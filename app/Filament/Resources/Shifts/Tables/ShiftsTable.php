<?php

namespace App\Filament\Resources\Shifts\Tables;

use App\Enums\ShiftStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('employee.position')
                    ->label('Position')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cashier' => 'info',
                        'washer' => 'warning',
                        'supervisor' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('employee.branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Start')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('End')
                    ->time('H:i')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => ShiftStatus::Scheduled,
                        'info' => ShiftStatus::Active,
                        'success' => ShiftStatus::Completed,
                        'danger' => ShiftStatus::Absent,
                    ])
                    ->formatStateUsing(fn (ShiftStatus $state): string => $state->label()),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'user.name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ShiftStatus::class)
                    ->native(false),

                SelectFilter::make('date')
                    ->label('Date Range')
                    ->options([
                        'today' => 'Today',
                        'this_week' => 'This Week',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                    ])
                    ->query(function ($query, $data) {
                        return match ($data['value']) {
                            'today' => $query->whereDate('date', now()),
                            'this_week' => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
                            'this_month' => $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]),
                            'last_month' => $query->whereBetween('date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}