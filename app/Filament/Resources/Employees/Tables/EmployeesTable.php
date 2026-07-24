<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(url('/images/default-avatar.png')),
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                BadgeColumn::make('position')
                    ->label('Position')
                    ->colors([
                        'primary' => 'cashier',
                        'success' => 'washer',
                        'warning' => 'supervisor',
                    ])
                    ->icons([
                        'cashier' => 'heroicon-m-currency-dollar',
                        'washer' => 'heroicon-m-sparkles',
                        'supervisor' => 'heroicon-m-user-group',
                    ])
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone copied')
                    ->formatStateUsing(fn (string $state): string => '+62 ' . ltrim($state, '0')),
                TextColumn::make('hire_date')
                    ->label('Hire Date')
                    ->date('d M Y')
                    ->sortable(),
                BadgeColumn::make('salary_type')
                    ->label('Salary Type')
                    ->colors([
                        'primary' => 'monthly',
                        'success' => 'per_wash',
                        'warning' => 'hourly',
                    ])
                    ->sortable(),
                TextColumn::make('base_salary')
                    ->label('Base Salary')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commission_rate')
                    ->label('Commission (%)')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('skills')
                    ->label('Skills')
                    ->limit(30)
                    ->tooltip(fn (string $state): string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),
                TextColumn::make('created_at')
                    ->label('Created')
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
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
