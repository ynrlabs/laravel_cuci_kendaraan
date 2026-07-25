<?php

namespace App\Filament\Widgets;

use App\Models\SlotConfiguration;
use App\Models\Booking;
use App\Models\Branch;
use App\Enums\BookingStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class LiveOccupancyWidget extends ChartWidget
{
    protected ?string $heading = 'Live Occupancy (Today)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');
        $today = Carbon::today();

        // Get all slots for today
        $slotsQuery = SlotConfiguration::where(function ($query) use ($today) {
            $query->whereDate('date', $today)
                ->orWhere('day_of_week', $today->dayOfWeekIso);
        })->where('is_blocked', false);

        if ($branchIds && $branchIds->isNotEmpty()) {
            $slotsQuery->whereIn('branch_id', $branchIds);
        }

        $slots = $slotsQuery->orderBy('slot_time')->get();

        // Get active bookings per slot
        $activeBookings = Booking::whereDate('booking_date', $today)
            ->whereIn('status', [BookingStatus::IN_QUEUE, BookingStatus::WASHING, BookingStatus::DRYING]);

        if ($branchIds && $branchIds->isNotEmpty()) {
            $activeBookings->whereIn('branch_id', $branchIds);
        }

        $bookingsBySlot = $activeBookings->get()->groupBy('slot_time');

        $labels = [];
        $capacityData = [];
        $bookedData = [];
        $availableData = [];

        foreach ($slots as $slot) {
            $timeLabel = Carbon::parse($slot->slot_time)->format('H:i');
            $labels[] = $timeLabel;
            $capacityData[] = $slot->max_capacity;
            $booked = $bookingsBySlot->get($slot->slot_time)->count() ?? 0;
            $bookedData[] = $booked;
            $availableData[] = max(0, $slot->max_capacity - $booked);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Capacity',
                    'data' => $capacityData,
                    'backgroundColor' => 'rgba(148, 163, 184, 0.3)',
                    'borderColor' => 'rgba(148, 163, 184, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Booked',
                    'data' => $bookedData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Available',
                    'data' => $availableData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": " + context.parsed.y;
                        }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Time Slots',
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Bays',
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }

    public function getPollingInterval(): ?string
    {
        return '10s';
    }
}