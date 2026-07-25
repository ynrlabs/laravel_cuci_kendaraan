<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class TopCustomersWidget extends BaseWidget
{
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');

        return $table
            ->query(
                Customer::query()
                    ->withCount(['bookings as completed_bookings_count' => fn(Builder $q) => $q->where('status', 'completed')])
                    ->withSum(['bookings as total_spent' => fn(Builder $q) => $q->where('payment_status', 'paid')], 'total')
                    ->when($branchIds && $branchIds->isNotEmpty(), fn(Builder $q) => $q->whereHas('bookings', fn(Builder $q2) => $q2->whereIn('branch_id', $branchIds)))
                    ->having('completed_bookings_count', '>', 0)
                    ->orderByDesc('completed_bookings_count')
                    ->orderByDesc('total_spent')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->weight('medium')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->formatStateUsing(fn($state) => $state ? '+62 ' . substr($state, 1) : '-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('completed_bookings_count')
                    ->label('Visits')
                    ->alignCenter()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->money('IDR', locale: 'id')
                    ->weight('medium')
                    ->alignEnd()
                    ->color('success'),

                Tables\Columns\TextColumn::make('loyalty_tier')
                    ->label('Tier')
                    ->badge()
                    ->colors([
                        'gray' => 'bronze',
                        'gray' => 'silver',
                        'warning' => 'gold',
                        'primary' => 'platinum',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state ?? 'bronze')),
            ])
            ->defaultSort('completed_bookings_count', 'desc')
            ->paginated(false)
            ->emptyStateHeading('No customers yet')
            ->emptyStateDescription('Customers will appear here after completing bookings.');
    }
}