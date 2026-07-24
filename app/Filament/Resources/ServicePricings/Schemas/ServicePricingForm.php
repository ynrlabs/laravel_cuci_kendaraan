<?php

namespace App\Filament\Resources\ServicePricings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServicePricingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->label('Service')
                    ->required(),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->required(),
                Select::make('vehicle_type')
                    ->options([
                        'sedan' => 'Sedan',
                        'suv' => 'SUV',
                        'mpv' => 'MPV',
                        'hatchback' => 'Hatchback',
                        'motor' => 'Motorcycle (Motor)',
                        'truck' => 'Truck / Large Vehicle',
                    ])
                    ->label('Vehicle Type')
                    ->required(),
                TextInput::make('price')
                    ->label('Price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp '),
                TextInput::make('duration_minutes')
                    ->label('Duration (Minutes)')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->required()
                    ->default(true),
            ]);
    }
}
