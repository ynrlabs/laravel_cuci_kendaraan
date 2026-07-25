<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PendingBookingsAlertWidget extends Widget
{
    protected static ?string $heading = 'Pending Confirmations';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.pending-bookings-alert';

    public function getCount(): int
    {
        return $this->getPendingBookingsQuery()->count();
    }

    public function getPendingBookings()
    {
        return $this->getPendingBookingsQuery()
            ->with(['customer', 'service', 'branch'])
            ->orderBy('slot_time')
            ->limit(10)
            ->get();
    }

    protected function getPendingBookingsQuery(): Builder
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');
        $today = Carbon::today();

        return Booking::query()
            ->whereDate('booking_date', $today)
            ->where(function ($query) {
                $query->where('status', BookingStatus::PENDING_PAYMENT->value)
                    ->orWhere('status', BookingStatus::CONFIRMED->value);
            })
            ->where('payment_status', PaymentStatus::UNPAID->value)
            ->when($branchIds && $branchIds->isNotEmpty(), fn(Builder $q) => $q->whereIn('branch_id', $branchIds));
    }

    public function getViewData(): array
    {
        return [
            'count' => $this->getCount(),
            'pendingBookings' => $this->getPendingBookings(),
        ];
    }

    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}