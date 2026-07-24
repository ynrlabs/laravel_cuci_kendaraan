<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_code')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'id')
                    ->required(),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required(),
                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->required(),
                Select::make('assigned_employee_id')
                    ->relationship('assignedEmployee', 'id'),
                TextInput::make('vehicle_type')
                    ->required(),
                TextInput::make('vehicle_color'),
                TextInput::make('vehicle_plate'),
                TextInput::make('vehicle_photos'),
                DatePicker::make('booking_date')
                    ->required(),
                TimePicker::make('slot_time')
                    ->required(),
                Select::make('status')
                    ->options(BookingStatus::class)
                    ->required(),
                TextInput::make('payment_method')
                    ->required(),
                Select::make('payment_status')
                    ->options(PaymentStatus::class)
                    ->required(),
                TextInput::make('payment_reference'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Toggle::make('is_walk_in')
                    ->required(),
                TextInput::make('rating')
                    ->numeric(),
                Textarea::make('review')
                    ->columnSpanFull(),
                TextInput::make('review_photos'),
                DateTimePicker::make('reviewed_at'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('finished_at'),
                DateTimePicker::make('cancelled_at'),
                TextInput::make('cancel_reason'),
                TextInput::make('cancelled_by'),
            ]);
    }
}
