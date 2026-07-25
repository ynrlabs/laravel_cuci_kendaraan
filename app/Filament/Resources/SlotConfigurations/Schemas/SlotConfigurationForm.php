<?php

namespace App\Filament\Resources\SlotConfigurations\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use App\Models\Branch;

class SlotConfigurationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Slot')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('branch_id')
                                    ->label('Cabang')
                                    ->options(Branch::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('type')
                                    ->label('Tipe Slot')
                                    ->options([
                                        'specific' => 'Tanggal Spesifik',
                                        'recurring' => 'Berulang (Hari dalam Minggu)',
                                    ])
                                    ->required()
                                    ->default('specific')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state === 'specific') {
                                            $set('date', now()->format('Y-m-d'));
                                            $set('day_of_week', null);
                                        } else {
                                            $set('date', null);
                                            $set('day_of_week', 'monday');
                                        }
                                    }),

                                DatePicker::make('date')
                                    ->label('Tanggal')
                                    ->required(fn (callable $get) => $get('type') === 'specific')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->format('Y-m-d')
                                    ->visible(fn (callable $get) => $get('type') === 'specific'),

                                Select::make('day_of_week')
                                    ->label('Hari dalam Minggu')
                                    ->options([
                                        'monday' => 'Senin',
                                        'tuesday' => 'Selasa',
                                        'wednesday' => 'Rabu',
                                        'thursday' => 'Kamis',
                                        'friday' => 'Jumat',
                                        'saturday' => 'Sabtu',
                                        'sunday' => 'Minggu',
                                    ])
                                    ->required(fn (callable $get) => $get('type') === 'recurring')
                                    ->visible(fn (callable $get) => $get('type') === 'recurring'),

                                TimePicker::make('slot_time')
                                    ->label('Waktu Slot')
                                    ->required()
                                    ->seconds(false)
                                    ->step(3600), // 1 hour steps

                                TextInput::make('max_capacity')
                                    ->label('Kapasitas Maksimum')
                                    ->type('number')
                                    ->minValue(0)
                                    ->maxValue(50)
                                    ->step(1)
                                    ->required()
                                    ->default(1),

                                Toggle::make('is_blocked')
                                    ->label('Diblokir')
                                    ->default(false)
                                    ->live(),

                                Textarea::make('block_reason')
                                    ->label('Alasan Blokir')
                                    ->rows(2)
                                    ->visible(fn (callable $get) => $get('is_blocked'))
                                    ->placeholder('Masukkan alasan blokir...'),

                                Textarea::make('notes')
                                    ->label('Catatan')
                                    ->rows(2)
                                    ->placeholder('Catatan tambahan...'),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }
}