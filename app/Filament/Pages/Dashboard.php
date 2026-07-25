<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\RecentBookingsWidget;
use App\Filament\Widgets\LiveOccupancyWidget;
use App\Filament\Widgets\LiveStatusFeedWidget;
use App\Filament\Widgets\RevenueLineChartWidget;
use App\Filament\Widgets\ServiceDoughnutChartWidget;
use App\Filament\Widgets\HourlyBookingsBarChartWidget;
use App\Filament\Widgets\TopCustomersWidget;
use App\Filament\Widgets\PendingBookingsAlertWidget;
use App\Filament\Widgets\AvgRatingStatWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;

class Dashboard extends BaseDashboard implements HasActions
{
    use InteractsWithActions;

    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scanQr')
                ->label('Scan QR')
                ->icon('heroicon-o-qr-code')
                ->color('warning')
                ->url(route('filament.admin.resources.bookings.index'))
                ->openUrlInNewTab(),

            Action::make('newWalkIn')
                ->label('New Walk-in')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->url(route('filament.admin.resources.bookings.create'))
                ->openUrlInNewTab(),
        ];
    }

    public function getWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
            AvgRatingStatWidget::class,
            PendingBookingsAlertWidget::class,
            [
                'columnSpan' => 'full',
                'widgets' => [
                    LiveOccupancyWidget::class,
                    LiveStatusFeedWidget::class,
                ],
            ],
            [
                'columnSpan' => 'full',
                'widgets' => [
                    RecentBookingsWidget::class,
                ],
            ],
            [
                'columnSpan' => 'full',
                'widgets' => [
                    RevenueLineChartWidget::class,
                    ServiceDoughnutChartWidget::class,
                    HourlyBookingsBarChartWidget::class,
                ],
            ],
            TopCustomersWidget::class,
        ];
    }
}