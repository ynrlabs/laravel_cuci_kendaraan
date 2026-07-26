<?php

namespace App\Filament\Resources\Employees\Widgets;

use App\Models\Employee;
use App\Models\Shift;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CommissionRatesWidget extends Widget
{
    protected string $view = 'filament.resources.employees.widgets.commission-rates-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public ?string $selectedBranchId = null;

    public array $branchOptions = [];

    public array $employees = [];

    public function mount(): void
    {
        $this->selectedBranchId = request()->query('branch_id') ?? Employee::first()?->branch_id;
        $this->loadBranchOptions();
        $this->loadCommissionData();
    }

    public function loadBranchOptions(): void
    {
        $this->branchOptions = \App\Models\Branch::pluck('name', 'id')->toArray();
    }

    public function loadCommissionData(): void
    {
        if (!$this->selectedBranchId) {
            return;
        }

        $this->employees = Employee::where('branch_id', $this->selectedBranchId)
            ->where('is_active', true)
            ->with(['user', 'shifts' => function ($query) {
                $query->where('date', '>=', now()->startOfWeek())
                      ->where('date', '<=', now()->endOfWeek())
                      ->where('status', 'completed');
            }])
            ->orderBy('position')
            ->orderBy('user_id')
            ->get()
            ->map(function ($employee) {
                $weeklySales = $employee->shifts->count();
                $commissionEarned = $employee->shifts->sum(function ($shift) use ($employee) {
                    if ($employee->salary_type === 'per_wash') {
                        return $employee->base_salary * ($employee->commission_rate / 100);
                    }
                    return 0;
                });

                return [
                    'id' => $employee->id,
                    'name' => $employee->user->name,
                    'avatar' => $employee->avatar,
                    'position' => $employee->position->value,
                    'base_commission' => $employee->commission_rate,
                    'salary_type' => $employee->salary_type->value,
                    'base_salary' => $employee->base_salary,
                    'weekly_sales' => $weeklySales,
                    'commission_earned' => $commissionEarned,
                ];
            })
            ->toArray();
    }

    public function onBranchChanged(): void
    {
        $this->loadCommissionData();
        $this->redirect(static::getUrl('index', ['branch_id' => $this->selectedBranchId]));
    }

    public function getPositionBadgeClass(string $position): string
    {
        return match ($position) {
            'cashier' => 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
            'washer' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
            'supervisor' => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
        };
    }

    public function getSalaryTypeBadgeClass(string $salaryType): string
    {
        return match ($salaryType) {
            'monthly' => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400',
            'per_wash' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
            'hourly' => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
        };
    }
}