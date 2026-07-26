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
            // Row 1: Stats Overview (4 columns)
            DashboardStatsWidget::class,
            AvgRatingStatWidget::class,
            
            // Row 2: Pending Alerts (full width - important alerts)
            PendingBookingsAlertWidget::class,
            
            // Row 3: Live Occupancy & Live Status Feed (2 columns side by side)
            LiveOccupancyWidget::class,
            LiveStatusFeedWidget::class,
            
            // Row 4: Recent Bookings (half) + Revenue Chart (half)
            RecentBookingsWidget::class,
            RevenueLineChartWidget::class,
            
            // Row 5: Service Chart (half) + Hourly Chart (half)
            ServiceDoughnutChartWidget::class,
            HourlyBookingsBarChartWidget::class,
            
            // Row 6: Top Customers (full width table)
            TopCustomersWidget::class,
        ];
    }
}