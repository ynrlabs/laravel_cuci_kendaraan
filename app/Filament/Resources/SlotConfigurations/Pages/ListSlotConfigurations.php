<?php

namespace App\Filament\Resources\SlotConfigurations\Pages;

use App\Filament\Resources\SlotConfigurations\SlotConfigurationResource;
use Filament\Resources\Pages\ListRecords;

class ListSlotConfigurations extends ListRecords
{
    protected static string $resource = SlotConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('manage')
                ->label('Kelola Slot')
                ->icon('heroicon-o-calendar-days')
                ->url(fn () => static::getResource()::getUrl('manage', ['record' => 1]))
                ->visible(fn () => \App\Models\Branch::count() > 0),
        ];
    }
}