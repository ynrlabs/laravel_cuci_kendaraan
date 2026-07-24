<?php

namespace App\Filament\Resources\Shifts\Schemas;

use App\Enums\ShiftStatus;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'user.name')
                    ->getOptionLabelFromRecordUsing(fn (Employee $record) => "{$record->user->name} ({$record->position->value}) - {$record->branch->name}")
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),

                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->default(now()),

                TimePicker::make('start_time')
                    ->label('Start Time')
                    ->required()
                    ->seconds(false)
                    ->default('08:00'),

                TimePicker::make('end_time')
                    ->label('End Time')
                    ->required()
                    ->seconds(false)
                    ->default('16:00'),

                Select::make('status')
                    ->label('Status')
                    ->options(ShiftStatus::class)
                    ->required()
                    ->native(false)
                    ->default(ShiftStatus::Scheduled),
            ])->columns(2);
    }
}