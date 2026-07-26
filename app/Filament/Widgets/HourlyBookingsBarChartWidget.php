<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\SlotConfiguration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class HourlyBookingsBarChartWidget extends ChartWidget
{
    protected ?string $heading = 'Bookings by Hour (Today)';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'half';

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

        // Get bookings for today grouped by slot_time
        $bookingsQuery = Booking::whereDate('booking_date', $today)
            ->whereIn('status', ['confirmed', 'in_queue', 'washing', 'drying', 'completed'])
            ->selectRaw('slot_time, COUNT(*) as count')
            ->groupBy('slot_time');

        if ($branchIds && $branchIds->isNotEmpty()) {
            $bookingsQuery->whereIn('branch_id', $branchIds);
        }

        $bookingsBySlot = $bookingsQuery->get()->keyBy('slot_time');

        $labels = [];
        $bookedData = [];
        $capacityData = [];

        foreach ($slots as $slot) {
            $timeLabel = Carbon::parse($slot->slot_time)->format('H:i');
            $labels[] = $timeLabel;
            $capacityData[] = $slot->max_capacity;
            $bookedData[] = $bookingsBySlot[$slot->slot_time]->count ?? 0;
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
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
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
                    'title' => [
                        'display' => true,
                        'text' => 'Time Slots',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Bookings',
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
        return '30s';
    }
}