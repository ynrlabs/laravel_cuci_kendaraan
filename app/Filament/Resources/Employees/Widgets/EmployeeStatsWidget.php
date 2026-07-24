<?php

namespace App\Filament\Resources\Employees\Widgets;

use App\Models\Employee;
use App\Models\Shift;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EmployeeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalForce = Employee::where('is_active', true)->count();
        
        $today = now()->format('Y-m-d');
        $onShiftNow = Shift::where('date', $today)
            ->where('status', 'active')
            ->count();
        
        // Calculate today's commissions based on completed shifts
        $todaysShifts = Shift::where('date', $today)
            ->where('status', 'completed')
            ->with('employee')
            ->get();
        
        $todaysCommissions = $todaysShifts->sum(function ($shift) {
            $employee = $shift->employee;
            if (!$employee) return 0;
            
            // Commission calculation: base_salary * commission_rate for per_wash type
            if ($employee->salary_type === 'per_wash') {
                return $employee->base_salary * ($employee->commission_rate / 100);
            }
            return 0;
        });
        
        // Performance score calculation (simplified)
        $performanceScore = Employee::where('is_active', true)->avg('commission_rate') ?? 0;
        $performanceScore = round($performanceScore, 1);

        return [
            Stat::make('Total Force', $totalForce)
                ->description('Active employees')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 12, 8, 15, 10, 18, 24]),
            
            Stat::make('On Shift Now', $onShiftNow)
                ->description("/ {$totalForce} total")
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->chart([3, 5, 8, 12, 18, 15, 10]),
            
            Stat::make("Today's Commissions", "Rp " . number_format($todaysCommissions, 0, ',', '.'))
                ->description('Avg ' . Employee::where('is_active', true)->avg('commission_rate') . '%')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([50000, 120000, 80000, 200000, 150000, 300000, 420000]),
            
            Stat::make('Performance Score', $performanceScore . '/10')
                ->description('High Efficiency')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning')
                ->chart([3.2, 3.5, 3.8, 4.1, 4.3, 4.6, 4.8]),
        ];
    }
}