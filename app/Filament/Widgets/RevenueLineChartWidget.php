<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Branch;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class RevenueLineChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue (Last 7 Days)';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');

        $labels = [];
        $revenueData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D, d M');

            $query = Booking::whereDate('booking_date', $date)
                ->where('payment_status', 'paid');

            if ($branchIds && $branchIds->isNotEmpty()) {
                $query->whereIn('branch_id', $branchIds);
            }

            $revenueData[] = $query->sum('total');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Revenue (IDR)',
                    'data' => $revenueData,
                    'fill' => true,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgba(34, 197, 94, 1)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'padding' => 12,
                    'titleFont' => ['size' => 14],
                    'bodyFont' => ['size' => 13],
                    'callbacks' => [
                        'label' => 'function(context) {
                            return "Revenue: Rp " + context.parsed.y.toLocaleString("id-ID");
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "Rp " + value.toLocaleString("id-ID");
                        }',
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }

    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}