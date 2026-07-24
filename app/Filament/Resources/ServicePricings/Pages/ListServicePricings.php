<?php

namespace App\Filament\Resources\ServicePricings\Pages;

use App\Filament\Resources\ServicePricings\ServicePricingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServicePricings extends ListRecords
{
    protected static string $resource = ServicePricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ServicePricings\Widgets\PricingHealthWidget::class,
        ];
    }
}
