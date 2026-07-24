<?php

namespace App\Filament\Resources\SlotConfigurations\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use App\Models\Branch;

class SlotConfigurationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('type_label')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'specific' => 'primary',
                        'recurring' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'specific' => 'Tanggal Spesifik',
                        'recurring' => 'Berulang',
                        default => $state,
                    }),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->type === 'specific'),

                TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monday' => 'Senin',
                        'tuesday' => 'Selasa',
                        'wednesday' => 'Rabu',
                        'thursday' => 'Kamis',
                        'friday' => 'Jumat',
                        'saturday' => 'Sabtu',
                        'sunday' => 'Minggu',
                        default => $state,
                    })
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->type === 'recurring'),

                TextColumn::make('slot_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('max_capacity')
                    ->label('Maks Slot')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('current_booked')
                    ->label('Terisi')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('capacity_percentage')
                    ->label('Kapasitas')
                    ->formatStateUsing(fn ($record): string => 
                        $record->max_capacity > 0 
                            ? round(($record->current_booked / $record->max_capacity) * 100) . '%'
                            : '0%'
                    )
                    ->color(fn ($record): string => 
                        $record->max_capacity > 0 
                            ? (($record->current_booked / $record->max_capacity) >= 1 ? 'danger' : 
                                (($record->current_booked / $record->max_capacity) >= 0.8 ? 'warning' : 'success'))
                            : 'gray'
                    )
                    ->alignCenter(),

                IconColumn::make('is_blocked')
                    ->label('Blokir')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->sortable(),

                TextColumn::make('block_reason')
                    ->label('Alasan Blokir')
                    ->limit(30)
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->is_blocked),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->options(Branch::pluck('name', 'id'))
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Tipe Slot')
                    ->options([
                        'specific' => 'Tanggal Spesifik',
                        'recurring' => 'Berulang',
                    ]),

                Filter::make('is_blocked')
                    ->label('Hanya Diblokir')
                    ->query(fn ($query) => $query->where('is_blocked', true)),

                Filter::make('date')
                    ->label('Tanggal')
                    ->form([
                        DatePicker::make('date_from')->label('Dari Tanggal'),
                        DatePicker::make('date_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query->when(
                            $data['date_from'],
                            fn ($query, $date) => $query->whereDate('date', '>=', $date),
                        )->when(
                            $data['date_until'],
                            fn ($query, $date) => $query->whereDate('date', '<=', $date),
                        );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                // Bulk actions if needed
            ])
            ->defaultSort('date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}