<?php

namespace App\Filament\Resources\Employees\Widgets;

use App\Enums\EmployeePosition;
use App\Models\Employee;
use App\Models\Shift;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ShiftScheduleWidget extends Widget
{
    protected string $view = 'filament.resources.employees.widgets.shift-schedule-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public ?int $selectedBranchId = null;

    public ?string $currentWeekStart = null;

    public array $branchOptions = [];

    public array $weeks = [];

    public array $employees = [];

    public array $shifts = [];

    public function mount(): void
    {
        $this->selectedBranchId = request()->query('branch_id') ?? Employee::first()?->branch_id;
        $this->currentWeekStart = request()->query('week_start') ?? now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        
        $this->loadBranchOptions();
        $this->loadWeekData();
    }

    public function loadBranchOptions(): void
    {
        $this->branchOptions = \App\Models\Branch::pluck('name', 'id')->toArray();
    }

    public function loadWeekData(): void
    {
        if (!$this->selectedBranchId || !$this->currentWeekStart) {
            return;
        }

        $weekStart = Carbon::parse($this->currentWeekStart);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $today = now()->format('Y-m-d');

        // Get employees for this branch
        $this->employees = Employee::where('branch_id', $this->selectedBranchId)
            ->where('is_active', true)
            ->with(['user', 'shifts' => function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('date', [$weekStart, $weekEnd]);
            }])
            ->orderBy('position')
            ->orderBy('user_id')
            ->get()
            ->toArray();

        // Get all shifts for this week and branch
        $this->shifts = Shift::whereHas('employee', function ($query) {
            $query->where('branch_id', $this->selectedBranchId);
        })
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get()
            ->groupBy('employee_id')
            ->map(function ($shifts) {
                return $shifts->keyBy('date');
            })
            ->toArray();

        // Build week days
        $this->weeks = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $this->weeks[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->translatedFormat('D'),
                'day_number' => $date->day,
                'is_today' => $date->format('Y-m-d') === $today,
                'is_weekend' => $date->isWeekend(),
            ];
        }
    }

    public function previousWeek(): void
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)->subWeek()->format('Y-m-d');
        $this->loadWeekData();
        $this->redirect(static::getUrl('index', ['branch_id' => $this->selectedBranchId, 'week_start' => $this->currentWeekStart]));
    }

    public function nextWeek(): void
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)->addWeek()->format('Y-m-d');
        $this->loadWeekData();
        $this->redirect(static::getUrl('index', ['branch_id' => $this->selectedBranchId, 'week_start' => $this->currentWeekStart]));
    }

    public function thisWeek(): void
    {
        $this->currentWeekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $this->loadWeekData();
        $this->redirect(static::getUrl('index', ['branch_id' => $this->selectedBranchId, 'week_start' => $this->currentWeekStart]));
    }

    public function onBranchChanged(): void
    {
        $this->loadWeekData();
        $this->redirect(static::getUrl('index', ['branch_id' => $this->selectedBranchId, 'week_start' => $this->currentWeekStart]));
    }

    public function getShiftForEmployeeAndDate(int $employeeId, string $date): ?array
    {
        return $this->shifts[$employeeId][$date] ?? null;
    }

    public function getShiftBadgeClass(string $status): string
    {
        return match ($status) {
            'scheduled' => 'bg-gray-100 text-gray-700 border border-gray-200',
            'active' => 'bg-primary-100 text-primary-700 border border-primary-200',
            'completed' => 'bg-success-100 text-success-700 border border-success-200',
            'absent' => 'bg-danger-100 text-danger-700 border border-danger-200',
            default => 'bg-gray-100 text-gray-700 border border-gray-200',
        };
    }

    public function getPositionBadgeClass(string $position): string
    {
        return match ($position) {
            'cashier' => 'bg-info-100 text-info-700',
            'washer' => 'bg-warning-100 text-warning-700',
            'supervisor' => 'bg-success-100 text-success-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}