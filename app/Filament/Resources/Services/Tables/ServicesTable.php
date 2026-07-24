<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $value = $state instanceof \App\Enums\ServiceCategory ? $state->value : $state;
                        return match ($value) {
                            'basic' => 'WASH',
                            'premium' => 'PREMIUM',
                            'premium_plus', 'on_demand' => 'SPECIALIST',
                            default => strtoupper((string) $value),
                        };
                    })
                    ->color(function ($state): string {
                        $value = $state instanceof \App\Enums\ServiceCategory ? $state->value : $state;
                        return match ($value) {
                            'basic' => 'success',
                            'premium' => 'warning',
                            'premium_plus', 'on_demand' => 'yellow',
                            default => 'gray',
                        };
                    })
                    ->searchable(),
                TextColumn::make('base_duration_minutes')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
            ]);
    }
}
