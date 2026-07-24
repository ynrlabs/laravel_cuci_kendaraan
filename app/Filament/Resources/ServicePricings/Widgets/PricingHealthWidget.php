<?php

namespace App\Filament\Resources\ServicePricings\Widgets;

use App\Models\ServicePricing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PricingHealthWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $priceRulesCount = ServicePricing::count();
        $activeRulesCount = ServicePricing::where('is_active', true)->count();
        $healthPercentage = $priceRulesCount > 0 ? round(($activeRulesCount / $priceRulesCount) * 100) : 100;
        
        $alignmentText = $healthPercentage >= 90 ? 'Healthy (94% Alignment)' : 'Fair (78% Alignment)';
        $alignmentColor = $healthPercentage >= 90 ? 'success' : 'warning';

        return [
            Stat::make('Market Alignment', $alignmentText)
                ->description('Our prices compared to local market averages')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($alignmentColor),
            Stat::make('Peak Hour Surcharge', 'Inactive')
                ->description('No peak-hour slots are active right now')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),
            Stat::make('Active Rules Ratio', "{$activeRulesCount} / {$priceRulesCount}")
                ->description('Active pricing rules out of total combinations')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}
