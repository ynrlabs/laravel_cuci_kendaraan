<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\LoyaltyTier;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.avatar')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(fn($record) => $record->user?->avatar
                        ? Storage::url($record->user->avatar)
                        : "https://ui-avatars.com/api/?name=" . urlencode($record->user?->name ?? 'Customer') . "&color=FFFFFF&background=3B82F6&size=80"),

                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(['user.name', 'user.email', 'user.phone'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => $record->user?->email)
                    ->icon('heroicon-m-user'),

                BadgeColumn::make('loyalty_tier')
                    ->label('Loyalty Tier')
                    ->colors([
                        'amber' => LoyaltyTier::PLATINUM->value,
                        'yellow' => LoyaltyTier::GOLD->value,
                        'gray' => LoyaltyTier::SILVER->value,
                        'amber' => LoyaltyTier::BRONZE->value,
                    ])
                    ->icons([
                        'heroicon-m-star' => LoyaltyTier::PLATINUM->value,
                        'heroicon-m-star' => LoyaltyTier::GOLD->value,
                        'heroicon-m-star' => LoyaltyTier::SILVER->value,
                        'heroicon-m-user' => LoyaltyTier::BRONZE->value,
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                TextColumn::make('vehicle_plate')
                    ->label('License Plate')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->placeholder('N/A')
                    ->icon('heroicon-m-truck'),

                TextColumn::make('total_visits')
                    ->label('Visits')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('primary'),

                TextColumn::make('total_spent')
                    ->label('Total Spend')
                    ->money('IDR')
                    ->sortable()
                    ->icon('heroicon-m-currency-dollar')
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('preferredBranch.name')
                    ->label('Preferred Branch')
                    ->icon('heroicon-m-building-office')
                    ->placeholder('Not set')
                    ->toggleable(),

                TextColumn::make('bookings_count')
                    ->label('Bookings')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->toggleable(),

                TextColumn::make('user.is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Active' : 'Blocked')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->icons([
                        'heroicon-m-check-circle' => true,
                        'heroicon-m-x-circle' => false,
                    ])
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('loyalty_tier')
                    ->label('Loyalty Tier')
                    ->options(LoyaltyTier::class)
                    ->multiple(),

                SelectFilter::make('preferred_branch_id')
                    ->label('Preferred Branch')
                    ->relationship('preferredBranch', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('is_active')
                    ->label('Status')
                    ->query(fn(Builder $query, array $data) => $query->whereHas('user', fn($q) => $q->where('is_active', true)))
                    ->toggle(),

                Filter::make('blocked')
                    ->label('Blocked')
                    ->query(fn(Builder $query, array $data) => $query->whereHas('user', fn($q) => $q->where('is_active', false)))
                    ->toggle(),

                Filter::make('has_vehicle')
                    ->label('Has Vehicle')
                    ->query(fn(Builder $query, array $data) => $query->whereNotNull('vehicle_plate'))
                    ->toggle(),

                Filter::make('recent_visitors')
                    ->label('Visited Last 30 Days')
                    ->query(fn(Builder $query, array $data) => $query->whereHas('bookings', fn($q) => $q->where('booking_date', '>=', now()->subDays(30))))
                    ->toggle(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->label('View'),
                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->label('Edit'),
                    Action::make('block_unblock')
                        ->label(fn($record) => $record->user?->is_active ? 'Block' : 'Unblock')
                        ->icon(fn($record) => $record->user?->is_active ? 'heroicon-o-user-minus' : 'heroicon-o-user-plus')
                        ->color(fn($record) => $record->user?->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => $record->user?->is_active ? 'Block Customer' : 'Unblock Customer')
                        ->modalDescription(fn($record) => $record->user?->is_active
                            ? 'Are you sure you want to block this customer? They will not be able to make new bookings.'
                            : 'Are you sure you want to unblock this customer? They will be able to make bookings again.')
                        ->action(function ($record) {
                            $record->user->update(['is_active' => !$record->user->is_active]);
                        }),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Action::make('bulk_block')
                        ->label('Block Selected')
                        ->icon('heroicon-o-user-minus')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->user) {
                                    $record->user->update(['is_active' => false]);
                                }
                            }
                        }),
                    Action::make('bulk_unblock')
                        ->label('Unblock Selected')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->user) {
                                    $record->user->update(['is_active' => true]);
                                }
                            }
                        }),
                    ExportBulkAction::make()
                        ->exports([
                            \App\Filament\Exports\CustomerExporter::class,
                        ]),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}