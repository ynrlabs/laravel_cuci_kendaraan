<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Branch;
use App\Enums\BookingStatus;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class LiveStatusFeedWidget extends BaseWidget
{
    protected static ?string $heading = 'Live Bay Status Feed';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');
        $today = Carbon::today();

        return $table
            ->query(
                Booking::query()
                    ->with(['customer', 'service', 'assignedEmployee.user'])
                    ->whereDate('booking_date', $today)
                    ->whereIn('status', [
                        BookingStatus::IN_QUEUE,
                        BookingStatus::WASHING,
                        BookingStatus::DRYING,
                        BookingStatus::COMPLETED,
                    ])
                    ->when($branchIds, fn(Builder $q) => $q->whereIn('branch_id', $branchIds))
                    ->orderByRaw("FIELD(status, 'in_queue', 'washing', 'drying', 'completed')")
                    ->latest('slot_time')
            )
            ->columns([
                Tables\Columns\TextColumn::make('slot_time')
                    ->label('Slot')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->format('H:i'))
                    ->fontFamily('JetBrains Mono')
                    ->weight('bold')
                    ->width('80px'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->weight('medium')
                    ->limit(20),

                Tables\Columns\TextColumn::make('vehicle_plate')
                    ->label('Plate')
                    ->formatStateUsing(fn($state) => strtoupper($state))
                    ->fontFamily('JetBrains Mono')
                    ->width('100px'),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->badge()
                    ->color(fn($record) => match ($record->service->category) {
                        'basic' => 'gray',
                        'premium' => 'primary',
                        'premium_plus' => 'warning',
                        'on_demand' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => BookingStatus::IN_QUEUE->value,
                        'primary' => BookingStatus::WASHING->value,
                        'info' => BookingStatus::DRYING->value,
                        'success' => BookingStatus::COMPLETED->value,
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'in_queue' => '🕐 In Queue',
                        'washing' => '🧽 Washing',
                        'drying' => '💨 Drying',
                        'completed' => '✅ Done',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),

                Tables\Columns\TextColumn::make('assignedEmployee.user.name')
                    ->label('Assigned To')
                    ->placeholder('—')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn($state, $record) => $record->status === BookingStatus::COMPLETED && $state
                        ? "{$state} min"
                        : ($record->started_at ? now()->diffInMinutes($record->started_at) . ' min ⏱️' : '—'))
                    ->alignCenter(),
            ])
            ->defaultSort('slot_time')
            ->paginated(false)
            ->poll('5s')
            ->emptyStateHeading('No active bookings')
            ->emptyStateDescription('All bays are free or no bookings for today.');
    }
}