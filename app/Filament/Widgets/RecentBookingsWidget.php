<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class RecentBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');

        return $table
            ->query(
                Booking::query()
                    ->with(['customer', 'service', 'branch'])
                    ->when($branchIds, fn(Builder $q) => $q->whereIn('branch_id', $branchIds))
                    ->latest('booking_date')
                    ->latest('slot_time')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer / Vehicle')
                    ->formatStateUsing(fn(Booking $record) => "{$record->customer?->name} / {$record->vehicle_type} {$record->vehicle_brand} ({$record->vehicle_color})")
                    ->searchable(['customer.name', 'vehicle_type', 'vehicle_brand', 'vehicle_color'])
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('vehicle_plate')
                    ->label('License Plate')
                    ->fontFamily('monospace')
                    ->fontFamily('JetBrains Mono')
                    ->formatStateUsing(fn($state) => strtoupper($state))
                    ->weight('medium')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->badge()
                    ->color(fn($record) => match ($record->service->category) {
                        'basic' => 'gray',
                        'premium' => 'primary',
                        'premium_plus' => 'warning',
                        'on_demand' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => BookingStatus::PENDING->value,
                        'info' => BookingStatus::CONFIRMED->value,
                        'warning' => BookingStatus::IN_QUEUE->value,
                        'primary' => BookingStatus::WASHING->value,
                        'warning' => BookingStatus::DRYING->value,
                        'success' => BookingStatus::COMPLETED->value,
                        'danger' => BookingStatus::CANCELLED->value,
                    ])
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('total')
                    ->label('Price')
                    ->money('IDR', locale: 'id')
                    ->weight('bold'),
            ])
            ->defaultSort('booking_date', 'desc')
            ->paginated(false)
            ->poll('5s');
    }
}