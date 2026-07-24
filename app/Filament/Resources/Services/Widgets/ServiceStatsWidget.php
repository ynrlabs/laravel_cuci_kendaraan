<?php

namespace App\Filament\Resources\Services\Widgets;

use App\Models\Service;
use App\Models\AddOn;
use App\Models\ServicePricing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeServicesCount = Service::where('is_active', true)->count();
        $activeAddonsCount = AddOn::where('is_active', true)->count();
        $priceRulesCount = ServicePricing::count();
        $avgPrice = ServicePricing::where('is_active', true)->avg('price') ?? 0;

        return [
            Stat::make('Active Services', $activeServicesCount)
                ->description('Total active washing services')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success'),
            Stat::make('Avg. Price', 'Rp ' . number_format($avgPrice, 0, ',', '.'))
                ->description('Average service pricing combination')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('Active Add-ons', $activeAddonsCount)
                ->description('Optional add-on services')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),
            Stat::make('Price Rules', $priceRulesCount)
                ->description('Total combinations mapped')
                ->descriptionIcon('heroicon-m-adjustments-horizontal')
                ->color('warning'),
        ];
    }
}
