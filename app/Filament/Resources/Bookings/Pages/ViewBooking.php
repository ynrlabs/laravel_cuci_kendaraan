<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('start_wash')
                    ->label('Mulai Cuci')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn () => in_array($this->record->status->value, ['confirmed', 'in_queue', 'pending_payment']))
                    ->action(function () {
                        $this->record->startWash();
                        $this->refresh();
                    }),

                Action::make('complete_wash')
                    ->label('Selesai Cuci')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => in_array($this->record->status->value, ['washing', 'drying']))
                    ->action(function () {
                        $this->record->completeWash();
                        $this->refresh();
                    }),

                Action::make('reschedule')
                    ->label('Reschedule')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn () => !in_array($this->record->status->value, ['completed', 'cancelled', 'no_show']))
                    ->form([
                        DatePicker::make('booking_date')
                            ->label('Tanggal Baru')
                            ->required()
                            ->default(fn () => $this->record->booking_date),
                        TimePicker::make('slot_time')
                            ->label('Jam Baru')
                            ->required()
                            ->default(fn () => $this->record->slot_time),
                    ])
                    ->action(function (array $data) {
                        $this->record->reschedule($data['booking_date'], $data['slot_time']);
                        $this->refresh();
                    }),

                Action::make('assign_operator')
                    ->label('Tugaskan Operator')
                    ->icon('heroicon-o-user')
                    ->color('primary')
                    ->form([
                        Select::make('assigned_employee_id')
                            ->label('Pilih Karyawan')
                            ->options(fn () => \App\Models\Employee::where('branch_id', $this->record->branch_id)
                                ->where('is_active', true)
                                ->with('user')
                                ->get()
                                ->pluck('user.name', 'id')
                            )
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $this->record->update(['assigned_employee_id' => $data['assigned_employee_id']]);
                        $this->refresh();
                    }),

                Action::make('cancel_booking')
                    ->label('Batalkan Booking')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn () => !in_array($this->record->status->value, ['completed', 'cancelled', 'no_show']))
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Pembatalan')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $this->record->cancel($data['reason'], auth()->id());
                        $this->refresh();
                    }),
            ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Booking Details')
                    ->tabs([
                        Tab::make('Status & Timeline')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Timeline')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('booking_code')
                                                    ->label('Booking Code')
                                                    ->weight('bold')
                                                    ->size('lg'),
                                                TextEntry::make('status')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->color(fn ($state) => match ($state->value) {
                                                        'cancelled' => 'danger',
                                                        'completed' => 'success',
                                                        'washing' => 'warning',
                                                        'confirmed' => 'info',
                                                        default => 'gray',
                                                    }),
                                                IconEntry::make('is_walk_in')
                                                    ->label('Walk-in Booking')
                                                    ->boolean(),
                                            ]),
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('created_at')
                                                    ->label('Dibuat Pada')
                                                    ->dateTime('d M Y H:i'),
                                                TextEntry::make('started_at')
                                                    ->label('Mulai Cuci')
                                                    ->dateTime('d M Y H:i')
                                                    ->placeholder('-'),
                                                TextEntry::make('finished_at')
                                                    ->label('Selesai Cuci')
                                                    ->dateTime('d M Y H:i')
                                                    ->placeholder('-'),
                                                TextEntry::make('duration_minutes')
                                                    ->label('Durasi Cuci')
                                                    ->suffix(' menit')
                                                    ->placeholder('-'),
                                            ]),
                                    ]),

                                Section::make('Informasi Pembatalan')
                                    ->visible(fn () => $this->record->status === BookingStatus::CANCELLED)
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('cancelled_at')
                                                    ->label('Dibatalkan Pada')
                                                    ->dateTime('d M Y H:i'),
                                                TextEntry::make('cancelledBy.name')
                                                    ->label('Dibatalkan Oleh')
                                                    ->placeholder('System/Customer'),
                                                TextEntry::make('cancel_reason')
                                                    ->label('Alasan Pembatalan')
                                                    ->columnSpan(2),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Customer & Vehicle')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Customer Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('customer.user.name')
                                                    ->label('Full Name')
                                                    ->weight('bold'),
                                                TextEntry::make('customer.user.email')
                                                    ->label('Email')
                                                    ->icon('heroicon-m-envelope'),
                                                TextEntry::make('customer.user.phone')
                                                    ->label('Phone')
                                                    ->icon('heroicon-m-phone'),
                                                TextEntry::make('customer.loyalty_tier')
                                                    ->label('Loyalty Tier')
                                                    ->badge()
                                                    ->color(fn ($state) => match ($state->value) {
                                                        'platinum' => Color::Amber,
                                                        'gold' => Color::Yellow,
                                                        'silver' => Color::Gray,
                                                        default => Color::Amber,
                                                    }),
                                            ]),
                                    ]),

                                Section::make('Vehicle Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('vehicle_plate')
                                                    ->label('License Plate')
                                                    ->badge()
                                                    ->color('primary'),
                                                TextEntry::make('vehicle_brand')
                                                    ->label('Vehicle Brand')
                                                    ->placeholder('-'),
                                                TextEntry::make('vehicle_type')
                                                    ->label('Vehicle Type')
                                                    ->placeholder('-'),
                                                TextEntry::make('vehicle_color')
                                                    ->label('Vehicle Color')
                                                    ->placeholder('-'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Service & Payment')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make('Service Information')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('service.name')
                                                    ->label('Service Booked')
                                                    ->weight('bold')
                                                    ->color('success'),
                                                TextEntry::make('assignedEmployee.user.name')
                                                    ->label('Assigned Operator')
                                                    ->placeholder('Belum ditugaskan'),
                                                TextEntry::make('booking_date')
                                                    ->label('Scheduled Date')
                                                    ->date('d M Y'),
                                                TextEntry::make('slot_time')
                                                    ->label('Scheduled Time')
                                                    ->time('H:i'),
                                            ]),
                                    ]),

                                Section::make('Payment Information')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('payment_method')
                                                    ->label('Payment Method')
                                                    ->placeholder('-'),
                                                TextEntry::make('payment_status')
                                                    ->label('Payment Status')
                                                    ->badge()
                                                    ->color(fn ($state) => match ($state->value) {
                                                        'paid' => 'success',
                                                        'unpaid' => 'danger',
                                                        'refund_pending' => 'warning',
                                                        default => 'gray',
                                                    }),
                                                TextEntry::make('payment_reference')
                                                    ->label('Payment Reference')
                                                    ->placeholder('-'),
                                            ]),
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('subtotal')
                                                    ->label('Subtotal')
                                                    ->money('IDR', locale: 'id'),
                                                TextEntry::make('discount')
                                                    ->label('Discount')
                                                    ->money('IDR', locale: 'id'),
                                                TextEntry::make('tax')
                                                    ->label('Tax')
                                                    ->money('IDR', locale: 'id'),
                                                TextEntry::make('total')
                                                    ->label('Total Payment')
                                                    ->money('IDR', locale: 'id')
                                                    ->weight('bold')
                                                    ->color('success'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
