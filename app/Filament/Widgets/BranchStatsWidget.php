<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Booking;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BranchStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $branches = Branch::withCount(['employees', 'bookings'])->get();

        return $branches->map(function (Branch $branch) {
            $todayBookings = Booking::where('branch_id', $branch->id)
                ->whereDate('booking_date', Carbon::today())
                ->count();

            $activeEmployees = Employee::where('branch_id', $branch->id)
                ->where('is_active', true)
                ->count();

            return Stat::make($branch->name, $branch->employees_count . ' Karyawan')
                ->description("{$branch->bookings_count} total booking • {$todayBookings} hari ini • {$activeEmployees} karyawan aktif")
                ->descriptionIcon('heroicon-m-building-office')
                ->color($branch->is_active ? 'success' : 'danger')
                ->chart([$branch->bookings_count, $todayBookings, $activeEmployees])
                ->url(route('filament.admin.resources.branches.edit', $branch));
        })->toArray();
    }
}