<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Models\Permission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(fn (string $state): string => $state)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                BadgeColumn::make('personnel_count')
                    ->label('Personel')
                    ->color('primary')
                    ->alignCenter()
                    ->formatStateUsing(fn (int $state): string => "{$state} orang"),
                
                BadgeColumn::make('is_default')
                    ->label('Default')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak')
                    ->sortable(),
                
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif')
                    ->sortable(),
                
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
            ->defaultSort('name')
            ->striped();
    }
}