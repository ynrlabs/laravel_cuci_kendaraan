<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Izin')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true)
                    ->placeholder('contoh: buat_booking, edit_harga, hapus_transaksi')
                    ->helperText('Gunakan snake_case, contoh: buat_booking'),
                
                Select::make('group')
                    ->label('Kategori')
                    ->options([
                        'Booking' => 'Booking',
                        'Pelanggan' => 'Pelanggan',
                        'Layanan & Harga' => 'Layanan & Harga',
                        'Karyawan' => 'Karyawan',
                        'Transaksi & Pembayaran' => 'Transaksi & Pembayaran',
                        'Laporan' => 'Laporan',
                        'Pengaturan' => 'Pengaturan',
                        'Cabang' => 'Cabang',
                    ])
                    ->required()
                    ->searchable()
                    ->native(false),
                
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->placeholder('Deskripsi singkat tentang izin ini...')
                    ->columnSpanFull(),
                
                Toggle::make('is_dangerous')
                    ->label('Aksi Berbahaya')
                    ->default(false)
                    ->helperText('Centang jika izin ini berpotensi berbahaya (hapus data, ubah harga, dll)'),
                
                TextInput::make('guard_name')
                    ->label('Guard')
                    ->default('web')
                    ->required()
                    ->maxLength(50)
                    ->hidden(),
            ])
            ->columns(2);
    }
}