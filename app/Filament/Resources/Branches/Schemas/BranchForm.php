<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Cabang')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->unique(table: 'branches', column: 'slug', ignoreRecord: true),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->required()
                            ->default(true),
                    ])->columns(3),

                Section::make('Alamat & Kontak')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label('Kota')
                            ->required(),
                        TextInput::make('province')
                            ->label('Provinsi')
                            ->required(),
                        TextInput::make('postal_code')
                            ->label('Kode Pos'),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email(),
                    ])->columns(3),

                Section::make('Koordinat GPS & Jam Operasional')
                    ->schema([
                        TextInput::make('latitude')
                            ->numeric(),
                        TextInput::make('longitude')
                            ->numeric(),
                        KeyValue::make('opening_hours')
                            ->label('Jam Operasional')
                            ->valueLabel('Jam (Buka - Tutup)')
                            ->keyLabel('Hari')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Media & Catatan')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Foto Cover')
                            ->image()
                            ->directory('branches'),
                        Textarea::make('operational_notes')
                            ->label('Catatan Operasional')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}