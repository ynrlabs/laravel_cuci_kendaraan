<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Customer ID'),
            ExportColumn::make('user.name')
                ->label('Full Name'),
            ExportColumn::make('user.email')
                ->label('Email'),
            ExportColumn::make('user.phone')
                ->label('Phone'),
            ExportColumn::make('vehicle_plate')
                ->label('Vehicle Plate'),
            ExportColumn::make('vehicle_type')
                ->label('Vehicle Type'),
            ExportColumn::make('vehicle_color')
                ->label('Vehicle Color'),
            ExportColumn::make('loyalty_tier')
                ->label('Loyalty Tier'),
            ExportColumn::make('preferredBranch.name')
                ->label('Preferred Branch'),
            ExportColumn::make('total_visits')
                ->label('Total Visits'),
            ExportColumn::make('total_spent')
                ->label('Total Spend (IDR)')
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            ExportColumn::make('bookings_count')
                ->label('Total Bookings'),
            ExportColumn::make('created_at')
                ->label('Registered At')
                ->formatStateUsing(fn($state) => $state->format('d M Y H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your customer export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($export->failed_rows > 0) {
            $body .= ' ' . number_format($export->failed_rows) . ' ' . str('row')->plural($export->failed_rows) . ' failed to export.';
        }

        return $body;
    }
}