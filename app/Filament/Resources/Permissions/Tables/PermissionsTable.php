<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Izin')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                
                BadgeColumn::make('group')
                    ->label('Kategori')
                    ->colors([
                        'primary' => 'Booking',
                        'success' => 'Pelanggan',
                        'warning' => 'Layanan & Harga',
                        'info' => 'Karyawan',
                        'danger' => 'Transaksi & Pembayaran',
                        'gray' => 'Laporan',
                        'purple' => 'Pengaturan',
                        'indigo' => 'Cabang',
                    ])
                    ->sortable(),
                
                BadgeColumn::make('is_dangerous')
                    ->label('Berbahaya')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ya ⚠️' : 'Tidak')
                    ->sortable(),
                
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group')
            ->striped();
    }
}