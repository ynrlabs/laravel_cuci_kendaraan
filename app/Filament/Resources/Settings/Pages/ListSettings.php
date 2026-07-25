<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Tambah Setting')
                ->url(SettingResource::getUrl('create'))
                ->icon('heroicon-o-plus')
                ->visible(fn () => auth()->user()->hasRole('super_admin')),
        ];
    }
}