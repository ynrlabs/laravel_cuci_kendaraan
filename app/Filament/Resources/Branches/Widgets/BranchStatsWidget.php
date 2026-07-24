<?php

namespace App\Filament\Resources\Branches\Widgets;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BranchStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBranches = Branch::count();
        $activeBranches = Branch::where('is_active', true)->count();
        $totalEmployees = Employee::count();
        $totalBookings = Booking::count();

        return [
            Stat::make('Total Cabang', "{$totalBranches} Cabang")
                ->description("{$activeBranches} berstatus aktif")
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),
            Stat::make('Total Pegawai', "{$totalEmployees} Orang")
                ->description('Tersebar di seluruh cabang')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Total Transaksi Booking', "{$totalBookings} Transaksi")
                ->description('Total pesanan cuci masuk')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
        ];
    }
}
