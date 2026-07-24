<?php

namespace App\Filament\Resources\SlotConfigurations;

use App\Filament\Resources\SlotConfigurations\Pages\ListSlotConfigurations;
use App\Filament\Resources\SlotConfigurations\Pages\ManageSlotConfiguration;
use App\Models\SlotConfiguration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SlotConfigurationResource extends Resource
{
    protected static ?string $model = SlotConfiguration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Pengaturan Slot';

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\SlotConfigurations\Schemas\SlotConfigurationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\SlotConfigurations\Tables\SlotConfigurationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSlotConfigurations::route('/'),
            'manage' => ManageSlotConfiguration::route('/{record}/manage'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\SlotConfigurations\Widgets\SlotConfigurationCalendarWidget::class,
        ];
    }
}