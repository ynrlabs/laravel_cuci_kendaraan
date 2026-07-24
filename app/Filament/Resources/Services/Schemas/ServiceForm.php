<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ServiceCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->unique(table: 'services', column: 'slug', ignoreRecord: true),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('category')
                    ->options([
                        'basic' => 'Basic (WASH)',
                        'premium' => 'Premium',
                        'premium_plus' => 'Premium Plus',
                        'on_demand' => 'On Demand',
                    ])
                    ->required(),
                TextInput::make('base_duration_minutes')
                    ->required()
                    ->numeric()
                    ->label('Base Duration (Minutes)'),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('image')
                    ->image()
                    ->directory('services'),
            ]);
    }
}
