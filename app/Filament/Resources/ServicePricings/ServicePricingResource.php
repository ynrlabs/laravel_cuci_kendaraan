<?php

namespace App\Filament\Resources\ServicePricings;

use App\Filament\Resources\ServicePricings\Pages\CreateServicePricing;
use App\Filament\Resources\ServicePricings\Pages\EditServicePricing;
use App\Filament\Resources\ServicePricings\Pages\ListServicePricings;
use App\Filament\Resources\ServicePricings\Schemas\ServicePricingForm;
use App\Filament\Resources\ServicePricings\Tables\ServicePricingsTable;
use App\Models\ServicePricing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServicePricingResource extends Resource
{
    protected static ?string $model = ServicePricing::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Layanan & Harga';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ServicePricingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicePricingsTable::configure($table);
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
            'index' => ListServicePricings::route('/'),
            'create' => CreateServicePricing::route('/create'),
            'edit' => EditServicePricing::route('/{record}/edit'),
        ];
    }
}
