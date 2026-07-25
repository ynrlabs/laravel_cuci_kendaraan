<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_code')
                    ->label('Booking Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('customer.user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->customer?->user?->email),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service.name')
                    ->label('Service')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicle_plate')
                    ->label('Vehicle')
                    ->description(fn ($record) => "{$record->vehicle_brand} ({$record->vehicle_color})")
                    ->searchable(['vehicle_plate', 'vehicle_brand', 'vehicle_color']),

                TextColumn::make('booking_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('slot_time')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => BookingStatus::CANCELLED->value,
                        'success' => BookingStatus::COMPLETED->value,
                        'warning' => BookingStatus::WASHING->value,
                        'info' => BookingStatus::CONFIRMED->value,
                        'gray' => BookingStatus::NO_SHOW->value,
                    ])
                    ->sortable(),

                BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'success' => PaymentStatus::PAID->value,
                        'danger' => PaymentStatus::UNPAID->value,
                        'warning' => PaymentStatus::REFUND_PENDING->value,
                        'gray' => PaymentStatus::REFUNDED->value,
                    ])
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BookingStatus::class),

                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name'),

                SelectFilter::make('service_id')
                    ->label('Service')
                    ->relationship('service', 'name'),

                Filter::make('booking_date')
                    ->form([
                        DatePicker::make('date_from')->label('Dari Tanggal'),
                        DatePicker::make('date_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query->when(
                            $data['date_from'],
                            fn ($query, $date) => $query->whereDate('booking_date', '>=', $date),
                        )->when(
                            $data['date_until'],
                            fn ($query, $date) => $query->whereDate('booking_date', '<=', $date),
                        );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('start_wash')
                        ->label('Mulai Cuci')
                        ->icon('heroicon-o-play')
                        ->color('info')
                        ->visible(fn ($record) => in_array($record->status->value, ['confirmed', 'in_queue', 'pending_payment']))
                        ->action(function ($record) {
                            $record->startWash();
                        }),

                    Action::make('complete_wash')
                        ->label('Selesai Cuci')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => in_array($record->status->value, ['washing', 'drying']))
                        ->action(function ($record) {
                            $record->completeWash();
                        }),

                    Action::make('reschedule')
                        ->label('Reschedule')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->visible(fn ($record) => !in_array($record->status->value, ['completed', 'cancelled', 'no_show']))
                        ->form([
                            DatePicker::make('booking_date')
                                ->label('Tanggal Baru')
                                ->required()
                                ->default(fn ($record) => $record->booking_date),
                            TimePicker::make('slot_time')
                                ->label('Jam Baru')
                                ->required()
                                ->default(fn ($record) => $record->slot_time),
                        ])
                        ->action(function ($record, array $data) {
                            $record->reschedule($data['booking_date'], $data['slot_time']);
                        }),

                    Action::make('assign_operator')
                        ->label('Tugaskan Operator')
                        ->icon('heroicon-o-user')
                        ->color('primary')
                        ->form([
                            Select::make('assigned_employee_id')
                                ->label('Pilih Karyawan')
                                ->options(fn ($record) => \App\Models\Employee::where('branch_id', $record->branch_id)
                                    ->where('is_active', true)
                                    ->with('user')
                                    ->get()
                                    ->pluck('user.name', 'id')
                                )
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update(['assigned_employee_id' => $data['assigned_employee_id']]);
                        }),

                    Action::make('cancel_booking')
                        ->label('Batalkan Booking')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => !in_array($record->status->value, ['completed', 'cancelled', 'no_show']))
                        ->form([
                            Textarea::make('reason')
                                ->label('Alasan Pembatalan')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->cancel($data['reason'], auth()->id());
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('booking_date', 'desc');
    }
}
