<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Service;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ServiceDoughnutChartWidget extends ChartWidget
{
    protected ?string $heading = 'Bookings by Service';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');
        $today = Carbon::today();

        $query = Booking::whereDate('booking_date', $today)
            ->whereIn('status', ['confirmed', 'in_queue', 'washing', 'drying', 'completed'])
            ->selectRaw('service_id, COUNT(*) as count')
            ->groupBy('service_id');

        if ($branchIds && $branchIds->isNotEmpty()) {
            $query->whereIn('branch_id', $branchIds);
        }

        $bookingsByService = $query->get()->keyBy('service_id');

        $services = Service::where('is_active', true)->orderBy('sort_order')->get();

        $labels = [];
        $data = [];
        $colors = [
            'rgba(107, 114, 128, 0.8)',   // gray - basic
            'rgba(59, 130, 246, 0.8)',    // primary - premium
            'rgba(245, 158, 11, 0.8)',    // warning - premium_plus
            'rgba(6, 182, 212, 0.8)',     // info - on_demand
        ];

        foreach ($services as $index => $service) {
            $labels[] = $service->name;
            $data[] = $bookingsByService[$service->id]->count ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'borderColor' => array_map(fn($c) => str_replace('0.8', '1', $c), array_slice($colors, 0, count($labels))),
                    'borderWidth' => 2,
                    'hoverOffset' => 8,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'cutout' => '65%',
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 16,
                        'font' => ['size' => 12],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'padding' => 12,
                    'titleFont' => ['size' => 14],
                    'bodyFont' => ['size' => 13],
                ],
            ],
        ];
    }

    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}