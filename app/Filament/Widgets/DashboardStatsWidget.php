<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Service;
use App\Models\SlotConfiguration;
use App\Enums\BookingStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        $branchId = $user->hasRole('super_admin') ? null : $user->branches->first()?->id;

        $branchQuery = Branch::query();
        if ($branchId) {
            $branchQuery->where('id', $branchId);
        }
        $branchIds = $branchQuery->pluck('id');

        // 1. Total Bookings Today
        $bookingsTodayQuery = Booking::whereDate('booking_date', Carbon::today());
        if ($branchIds->isNotEmpty()) {
            $bookingsTodayQuery->whereIn('branch_id', $branchIds);
        }
        $totalBookingsToday = $bookingsTodayQuery->count();

        // 2. Daily Revenue
        $revenueTodayQuery = Booking::whereDate('booking_date', Carbon::today())
            ->where('payment_status', 'paid');
        if ($branchIds->isNotEmpty()) {
            $revenueTodayQuery->whereIn('branch_id', $branchIds);
        }
        $dailyRevenue = $revenueTodayQuery->sum('total');

        // 3. Active Queue (Bookings in queue/washing/drying)
        $activeStatuses = [BookingStatus::IN_QUEUE->value, BookingStatus::WASHING->value, BookingStatus::DRYING->value];
        $activeBookingsQuery = Booking::whereDate('booking_date', Carbon::today())
            ->whereIn('status', $activeStatuses);
        if ($branchIds->isNotEmpty()) {
            $activeBookingsQuery->whereIn('branch_id', $branchIds);
        }
        $activeBookings = $activeBookingsQuery->count();

        // Total capacity for today
        $totalCapacityQuery = SlotConfiguration::where(function ($query) use ($branchIds) {
            $query->whereDate('date', Carbon::today())
                ->orWhere('day_of_week', Carbon::today()->dayOfWeekIso);
        });
        if ($branchIds->isNotEmpty()) {
            $totalCapacityQuery->whereIn('branch_id', $branchIds);
        }
        $totalCapacityQuery->where('is_blocked', false);
        $totalCapacity = $totalCapacityQuery->sum('max_capacity');

        $activeQueueText = "{$activeBookings} / {$totalCapacity}";
        $occupancyPercent = $totalCapacity > 0 ? round(($activeBookings / $totalCapacity) * 100) : 0;
        $queueColor = $occupancyPercent >= 80 ? 'danger' : ($occupancyPercent >= 50 ? 'warning' : 'success');

        // 4. Average Wash Time (completed bookings today)
        $completedBookingsQuery = Booking::whereDate('booking_date', Carbon::today())
            ->where('status', BookingStatus::COMPLETED->value)
            ->whereNotNull('duration_minutes');
        if ($branchIds->isNotEmpty()) {
            $completedBookingsQuery->whereIn('branch_id', $branchIds);
        }
        $avgWashTime = $completedBookingsQuery->avg('duration_minutes') ?? 0;
        $avgWashTimeText = round($avgWashTime) . ' min';

        return [
            Stat::make('Total Bookings Today', $totalBookingsToday)
                ->description('Bookings for ' . Carbon::today()->format('d M Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->chart([2, 5, 3, 8, 12, 15, $totalBookingsToday])
                ->url(route('filament.admin.resources.bookings.index', ['booking_date' => Carbon::today()->format('Y-m-d')])),

            Stat::make('Daily Revenue', 'Rp ' . number_format($dailyRevenue, 0, ',', '.'))
                ->description('Revenue for ' . Carbon::today()->format('d M Y'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([100000, 250000, 180000, 450000, 320000, 600000, $dailyRevenue])
                ->url(route('filament.admin.pages.financial-report')),

            Stat::make('Active Queue', $activeQueueText)
                ->description("{$occupancyPercent}% occupancy • In Queue / Washing / Drying")
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($queueColor)
                ->chart([2, 5, 8, 12, 8, 5, $activeBookings])
                ->url(route('filament.admin.pages.queue')),

            Stat::make('Avg. Wash Time', $avgWashTimeText)
                ->description('Average completion time today')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([25, 32, 28, 40, 35, 30, round($avgWashTime)])
                ->url(route('filament.admin.pages.financial-report')),
        ];
    }
}