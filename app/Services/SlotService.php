<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Booking;
use App\Models\SlotConfiguration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlotService
{
    protected const CACHE_TTL = 60; // 60 seconds
    protected const CACHE_PREFIX = 'slot_availability:';
    protected const CUTOFF_HOURS_KEY = 'booking_cutoff_hours';

    /**
     * Get available slots for a branch on a specific date
     */
    public function getAvailableSlots(Branch $branch, string $date): array
    {
        $cacheKey = self::CACHE_PREFIX . $branch->id . ':' . $date;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($branch, $date) {
            return $this->calculateAvailableSlots($branch, $date);
        });
    }

    /**
     * Calculate available slots (core logic)
     */
    protected function calculateAvailableSlots(Branch $branch, string $date): array
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = strtolower($carbonDate->format('l')); // monday, tuesday, etc.

        // Get slot configurations for this branch and date
        // Priority: specific date > recurring (day_of_week)
        $specificSlots = SlotConfiguration::where('branch_id', $branch->id)
            ->where('date', $date)
            ->where('is_blocked', false)
            ->orderBy('slot_time')
            ->get();

        $recurringSlots = SlotConfiguration::where('branch_id', $branch->id)
            ->whereNull('date')
            ->where('day_of_week', $dayOfWeek)
            ->where('is_blocked', false)
            ->orderBy('slot_time')
            ->get();

        // Merge slots: specific slots override recurring slots for same time
        $slots = $recurringSlots->keyBy('slot_time');
        foreach ($specificSlots as $specificSlot) {
            $slots[$specificSlot->slot_time] = $specificSlot;
        }

        // Get booked counts for this date
        $bookedCounts = Booking::where('branch_id', $branch->id)
            ->where('booking_date', $date)
            ->whereIn('status', [
                \App\Enums\BookingStatus::PENDING_PAYMENT->value,
                \App\Enums\BookingStatus::CONFIRMED->value,
                \App\Enums\BookingStatus::IN_QUEUE->value,
                \App\Enums\BookingStatus::WASHING->value,
                \App\Enums\BookingStatus::DRYING->value,
                \App\Enums\BookingStatus::COMPLETED->value,
            ])
            ->select('slot_time', DB::raw('count(*) as count'))
            ->groupBy('slot_time')
            ->pluck('count', 'slot_time')
            ->toArray();

        $formattedSlots = [];
        $now = now();
        $slotDateTime = Carbon::parse($date);

        foreach ($slots as $slotTime => $slotConfig) {
            $slotTimeCarbon = Carbon::parse($slotTime);
            $slotDateTimeFull = Carbon::parse($date)->setTime(
                $slotTimeCarbon->hour,
                $slotTimeCarbon->minute,
                $slotTimeCarbon->second
            );

            $maxCapacity = $slotConfig->max_capacity;
            $bookedCount = $bookedCounts[$slotTime] ?? 0;
            $availableCount = max(0, $maxCapacity - $bookedCount);
            $fillPercentage = $maxCapacity > 0 ? ($bookedCount / $maxCapacity) * 100 : 0;

            // Determine color indicator
            $colorIndicator = $this->getColorIndicator($fillPercentage);

            // Check cut-off time
            $cutoffHours = (int) config(self::CUTOFF_HOURS_KEY, 2);
            $cutoffTime = $slotDateTimeFull->copy()->subHours($cutoffHours);
            $isPastCutoff = $now->gte($cutoffTime);

            // Check if slot is in the past
            $isPast = $slotDateTimeFull->isPast();

            $formattedSlots[] = [
                'id' => $slotConfig->id,
                'slot_time' => $slotTime,
                'slot_time_formatted' => $slotTimeCarbon->format('H:i'),
                'max_capacity' => $maxCapacity,
                'booked_count' => $bookedCount,
                'available_count' => $availableCount,
                'fill_percentage' => round($fillPercentage, 1),
                'color_indicator' => $colorIndicator,
                'is_available' => $availableCount > 0 && !$isPastCutoff && !$isPast && !$isPastCutoff,
                'is_past_cutoff' => $isPastCutoff,
                'is_past' => $isPast,
                'cutoff_time' => $cutoffTime->format('Y-m-d H:i:s'),
            ];
        }

        return $formattedSlots;
    }

    /**
     * Get color indicator based on fill percentage
     */
    protected function getColorIndicator(float $fillPercentage): string
    {
        if ($fillPercentage >= 100) {
            return 'full'; // Red - Penuh (100%)
        } elseif ($fillPercentage >= 50) {
            return 'almost_full'; // Yellow - Hampir Penuh (50-90%)
        } else {
            return 'available'; // Green - Tersedia (< 50%)
        }
    }

    /**
     * Check if a specific slot is available for booking
     */
    public function isSlotAvailable(Branch $branch, string $date, string $slotTime): bool
    {
        $slots = $this->getAvailableSlots($branch, $date);
        
        foreach ($slots as $slot) {
            if ($slot['slot_time'] === $slotTime) {
                return $slot['is_available'];
            }
        }

        return false;
    }

    /**
     * Get slot availability for a specific slot time
     */
    public function getSlotAvailability(Branch $branch, string $date, string $slotTime): ?array
    {
        $slots = $this->getAvailableSlots($branch, $date);
        
        foreach ($slots as $slot) {
            if ($slot['slot_time'] === $slotTime) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * Increment booked count for a slot (when booking is created)
     * Uses database-level locking to prevent race conditions
     */
    public function incrementBookedCount(Branch $branch, string $date, string $slotTime): bool
    {
        // Find the slot configuration
        $slotConfig = SlotConfiguration::where('branch_id', $branch->id)
            ->where(function ($query) use ($date, $slotTime) {
                $query->where('date', $date)
                      ->orWhere(function ($q) use ($date, $slotTime) {
                          $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
                          $q->whereNull('date')
                            ->where('day_of_week', $dayOfWeek);
                      });
            })
            ->where('slot_time', $slotTime)
            ->where('is_blocked', false)
            ->first();

        if (!$slotConfig) {
            return false;
        }

        // Use database transaction with row locking
        return DB::transaction(function () use ($slotConfig) {
            // Lock the row for update
            $lockedSlot = SlotConfiguration::where('id', $slotConfig->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedSlot) {
                return false;
            }

            // Check if we can still book
            if ($lockedSlot->current_booked >= $lockedSlot->max_capacity) {
                return false;
            }

            $lockedSlot->increment('current_booked');
            
            // Invalidate cache
            $this->invalidateCache($lockedSlot->branch_id, $lockedSlot->date ?? 
                Carbon::parse($lockedSlot->date ?? now())->format('Y-m-d'));
            
            return true;
        });
    }

    /**
     * Decrement booked count for a slot (when booking is cancelled)
     */
    public function decrementBookedCount(Branch $branch, string $date, string $slotTime): bool
    {
        $slotConfig = SlotConfiguration::where('branch_id', $branch->id)
            ->where(function ($query) use ($date, $slotTime) {
                $query->where('date', $date)
                      ->orWhere(function ($q) use ($date, $slotTime) {
                          $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
                          $q->whereNull('date')
                            ->where('day_of_week', $dayOfWeek);
                      });
            })
            ->where('slot_time', $slotTime)
            ->first();

        if (!$slotConfig) {
            return false;
        }

        return DB::transaction(function () use ($slotConfig) {
            $lockedSlot = SlotConfiguration::where('id', $slotConfig->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedSlot || $lockedSlot->current_booked <= 0) {
                return false;
            }

            $lockedSlot->decrement('current_booked');
            
            // Invalidate cache
            $this->invalidateCache($lockedSlot->branch_id, $lockedSlot->date ?? 
                Carbon::parse($lockedSlot->date ?? now())->format('Y-m-d'));
            
            return true;
        });
    }

    /**
     * Invalidate cache for a branch and date
     */
    public function invalidateCache(string $branchId, string $date): void
    {
        $cacheKey = self::CACHE_PREFIX . $branchId . ':' . $date;
        Cache::forget($cacheKey);
    }

    /**
     * Invalidate all caches for a branch
     */
    public function invalidateBranchCache(string $branchId): void
    {
        // Note: This requires cache tags or a different approach
        // For now, we'll just note that tags would be better
        // In production, consider using Redis SCAN or cache tags
    }

    /**
     * Get cut-off time for booking (in hours)
     */
    public function getCutoffHours(): int
    {
        return (int) config(self::CUTOFF_HOURS_KEY, 2);
    }

    /**
     * Check if booking can be made for a specific slot (considering cut-off)
     */
    public function canBookSlot(Branch $branch, string $date, string $slotTime): array
    {
        $slotDateTime = Carbon::parse($date)->setTimeFromTimeString($slotTime);
        $now = now();
        $cutoffHours = $this->getCutoffHours();
        $cutoffTime = $slotDateTime->copy()->subHours($cutoffHours);

        if ($now->gte($cutoffTime)) {
            return [
                'can_book' => false,
                'reason' => 'Waktu booking sudah lewat (minimal ' . $cutoffHours . ' jam sebelum slot).',
                'cutoff_time' => $cutoffTime->format('Y-m-d H:i:s'),
            ];
        }

        if ($slotDateTime->isPast()) {
            return [
                'can_book' => false,
                'reason' => 'Slot sudah lewat.',
            ];
        }

        if (!$this->isSlotAvailable($branch, $date, $slotTime)) {
            return [
                'can_book' => false,
                'reason' => 'Slot sudah penuh.',
            ];
        }

        return [
            'can_book' => true,
            'reason' => null,
        ];
    }

    /**
     * Get daily slot summary for a branch
     */
    public function getDailySummary(Branch $branch, string $date): array
    {
        $slots = $this->getAvailableSlots($branch, $date);
        
        $totalCapacity = array_sum(array_column($slots, 'max_capacity'));
        $totalBooked = array_sum(array_column($slots, 'booked_count'));
        $totalAvailable = array_sum(array_column($slots, 'available_count'));

        $availableSlots = array_filter($slots, fn($s) => $s['is_available']);
        $fullSlots = array_filter($slots, fn($s) => $s['color_indicator'] === 'full');
        $almostFullSlots = array_filter($slots, fn($s) => $s['color_indicator'] === 'almost_full');

        return [
            'date' => $date,
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'total_slots' => count($slots),
            'total_capacity' => $totalCapacity,
            'total_booked' => $totalBooked,
            'total_available' => $totalAvailable,
            'utilization_percentage' => $totalCapacity > 0 ? round(($totalBooked / $totalCapacity) * 100, 1) : 0,
            'available_slots_count' => count($availableSlots),
            'full_slots_count' => count($fullSlots),
            'almost_full_slots_count' => count($almostFullSlots),
            'slots' => $slots,
        ];
    }

    /**
     * Get slot statistics for a branch over a date range
     */
    public function getSlotStatistics(Branch $branch, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $statistics = [];
        
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $statistics[] = $this->getDailySummary($branch, $dateStr);
        }

        return $statistics;
    }
}