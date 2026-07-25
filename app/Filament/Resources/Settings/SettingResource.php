<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\Settings\Pages\EditSettings;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Pengaturan Global';

    protected static ?int $navigationSort = 5;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->colors([
                        'primary' => 'general',
                        'success' => 'payment',
                        'warning' => 'notification',
                        'info' => 'operational',
                        'danger' => 'tax',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->limit(50)
                    ->wrap(),
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge(),
            ])
            ->defaultSort('group')
            ->searchable(['key', 'value']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSettings::route('/'),
            'edit' => EditSettings::route('/{record}/edit'),
        ];
    }
}