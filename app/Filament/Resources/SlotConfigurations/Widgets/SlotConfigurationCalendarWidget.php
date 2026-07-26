<?php

namespace App\Filament\Resources\SlotConfigurations\Widgets;

use App\Models\Branch;
use App\Models\SlotConfiguration;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class SlotConfigurationCalendarWidget extends Widget
{
    protected string $view = 'filament.resources.slot-configurations.widgets.slot-configuration-calendar-widget';

    protected static ?int $sort = 1;

    public ?string $selectedBranchId = null;

    public ?string $currentMonth = null;

    public ?int $currentYear = null;

    public array $months = [];

    public array $branchOptions = [];

    public array $weeks = [];

    public ?string $selectedDate = null;

    public array $hourlySlots = [];

    public int $dailyMaxCapacity = 10;

    public bool $isBlocked = false;

    public ?string $blockReason = null;

    public bool $showBlockModal = false;

    public bool $showResetModal = false;

    public string $blockReasonInput = '';

    public function mount(): void
    {
        $this->selectedBranchId = request()->query('branch_id') ?? Branch::first()?->id;
        $this->currentMonth = request()->query('month') ?? now()->format('m');
        $this->currentYear = request()->query('year') ?? now()->year;
        $this->selectedDate = request()->query('date') ?? now()->format('Y-m-d');

        $this->loadBranchOptions();
        $this->loadMonths();
        $this->loadCalendarData();
        $this->loadDailySlots();
    }

    public function loadBranchOptions(): void
    {
        $this->branchOptions = Branch::pluck('name', 'id')->toArray();
    }

    public function loadMonths(): void
    {
        $months = [];
        $start = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonths(6);
        
        for ($i = 0; $i < 13; $i++) {
            $date = $start->copy()->addMonths($i);
            $months[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }
        
        $this->months = $months;
    }

    public function loadCalendarData(): void
    {
        if (!$this->selectedBranchId) {
            return;
        }

        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all SPECIFIC DATE slot configurations for this month
        $specificSlots = SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('slot_time', '!=', '00:00') // Exclude daily max capacity marker
            ->where('type', 'specific')
            ->get();

        // Get all RECURRING slot configurations for this branch
        $recurringSlots = SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('type', 'recurring')
            ->where('slot_time', '!=', '00:00')
            ->get();

        // Group specific slots by date
        $specificSlotsByDate = $specificSlots->groupBy('date');

        // Build calendar weeks with both specific and recurring slots
        $this->buildCalendarWeeks($startDate, $endDate, $specificSlotsByDate, $recurringSlots);
    }

    public function buildCalendarWeeks(Carbon $startDate, Carbon $endDate, Collection $specificSlotsByDate, Collection $recurringSlots): void
    {
        $weeks = [];
        $currentWeek = [];
        
        // Start from the first day of the month (or previous month to fill first week)
        $calendarStart = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endDate->copy()->endOfWeek(Carbon::SUNDAY);

        $date = $calendarStart->copy();
        $today = now()->format('Y-m-d');

        // Day of week mapping (Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday)
        $dayOfWeekMap = [
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
        ];

        while ($date <= $calendarEnd) {
            $dateStr = $date->format('Y-m-d');
            $isCurrentMonth = $date->month === $this->currentMonth && $date->year === $this->currentYear;
            $isToday = $dateStr === $today;

            // Get specific slots for this date
            $daySpecificSlots = $specificSlotsByDate->get($dateStr, collect());
            
            // Get recurring slots for this day of week
            $dayOfWeek = $dayOfWeekMap[$date->dayOfWeek];
            $dayRecurringSlots = $recurringSlots->where('day_of_week', $dayOfWeek);
            
            // Merge specific and recurring slots (specific takes precedence)
            $allDaySlots = $daySpecificSlots->merge($dayRecurringSlots);
            
            $totalCapacity = $allDaySlots->sum('max_capacity');
            $totalBooked = $allDaySlots->sum('current_booked');
            $isBlocked = $allDaySlots->contains('is_blocked', true);
            $blockReason = $allDaySlots->firstWhere('is_blocked', true)?->block_reason;

            $capacityPercentage = $totalCapacity > 0 ? round(($totalBooked / $totalCapacity) * 100) : 0;

            $currentWeek[] = [
                'date' => $dateStr,
                'day' => $date->day,
                'is_current_month' => $isCurrentMonth,
                'is_today' => $isToday,
                'total_capacity' => $totalCapacity,
                'total_booked' => $totalBooked,
                'capacity_percentage' => $capacityPercentage,
                'is_blocked' => $isBlocked,
                'block_reason' => $blockReason,
            ];

            // End of week (Sunday)
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }

            $date->addDay();
        }

        // Add last week if not empty
        if (!empty($currentWeek)) {
            // Pad the last week
            while (count($currentWeek) < 7) {
                $currentWeek[] = null;
            }
            $weeks[] = $currentWeek;
        }

        $this->weeks = $weeks;
    }

    public function loadDailySlots(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        $branch = Branch::find($this->selectedBranchId);

        if (!$branch) {
            return;
        }

        $this->dailyMaxCapacity = $branch->daily_max_capacity ?? 10;

        // Load existing slots for this date
        $slots = SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('date', $date)
            ->where('slot_time', '!=', '00:00')
            ->orderBy('slot_time')
            ->get()
            ->keyBy('slot_time');

        // Check if date is blocked (daily marker)
        $dailyMarker = SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('date', $date)
            ->where('slot_time', '00:00')
            ->first();

        $this->isBlocked = $dailyMarker?->is_blocked ?? false;
        $this->blockReason = $dailyMarker?->block_reason;

        // Generate hourly slots 08:00 - 17:00
        $this->hourlySlots = [];
        
        for ($hour = 8; $hour <= 17; $hour++) {
            $time = sprintf('%02d:00', $hour);
            $slot = $slots->get($time);
            
            if ($slot) {
                $this->hourlySlots[$time] = [
                    'id' => $slot->id,
                    'time' => $time,
                    'max_capacity' => $slot->max_capacity,
                    'current_booked' => $slot->current_booked,
                    'is_blocked' => $slot->is_blocked,
                    'block_reason' => $slot->block_reason,
                    'capacity_percentage' => $slot->max_capacity > 0 
                        ? round(($slot->current_booked / $slot->max_capacity) * 100) 
                        : 0,
                ];
            } else {
                $this->hourlySlots[$time] = [
                    'id' => null,
                    'time' => $time,
                    'max_capacity' => 1,
                    'current_booked' => 0,
                    'is_blocked' => false,
                    'block_reason' => null,
                    'capacity_percentage' => 0,
                ];
            }
        }
    }

    public function onBranchChanged(): void
    {
        $this->loadCalendarData();
        $this->loadDailySlots();
        $this->redirect(static::getUrl('manage', ['record' => 1]) . "?branch_id={$this->selectedBranchId}&month={$this->currentMonth}&year={$this->currentYear}");
    }

    public function onMonthChanged(): void
    {
        $this->loadMonths();
        $this->loadCalendarData();
        $this->redirect(static::getUrl('manage', ['record' => 1]) . "?branch_id={$this->selectedBranchId}&month={$this->currentMonth}&year={$this->currentYear}");
    }

    public function onYearChanged(): void
    {
        $this->loadMonths();
        $this->loadCalendarData();
        $this->redirect(static::getUrl('manage', ['record' => 1]) . "?branch_id={$this->selectedBranchId}&month={$this->currentMonth}&year={$this->currentYear}");
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->year;
        $this->onMonthChanged();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->year;
        $this->onMonthChanged();
    }

    public function goToToday(): void
    {
        $this->currentMonth = now()->format('m');
        $this->currentYear = now()->year;
        $this->selectedDate = now()->format('Y-m-d');
        $this->onMonthChanged();
    }

    public function dateSelected(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadDailySlots();
        $this->dispatch('date-selected', date: $date);
    }

    public function saveDailyMaxCapacity(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        
        // Update or create daily max capacity marker
        SlotConfiguration::updateOrCreate([
            'branch_id' => $this->selectedBranchId,
            'date' => $date,
            'slot_time' => '00:00',
        ], [
            'day_of_week' => $date->dayOfWeek,
            'max_capacity' => $this->dailyMaxCapacity,
            'current_booked' => 0,
            'is_blocked' => false,
        ]);

        // Also update branch
        Branch::where('id', $this->selectedBranchId)->update([
            'daily_max_capacity' => $this->dailyMaxCapacity,
        ]);

        $this->loadCalendarData();
        $this->loadDailySlots();

        $this->notify('success', 'Kapasitas harian diperbarui', "Kapasitas maksimum harian diatur ke {$this->dailyMaxCapacity}");
    }

    public function confirmBlockDate(): void
    {
        $this->blockReasonInput = '';
        $this->showBlockModal = true;
    }

    public function closeBlockModal(): void
    {
        $this->showBlockModal = false;
        $this->blockReasonInput = '';
    }

    public function executeBlockDate(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate || empty($this->blockReasonInput)) {
            $this->notify('danger', 'Gagal', 'Alasan blokir harus diisi');
            return;
        }

        $date = Carbon::parse($this->selectedDate);

        // Block all hourly slots
        for ($hour = 8; $hour <= 17; $hour++) {
            $time = sprintf('%02d:00', $hour);
            
            SlotConfiguration::updateOrCreate([
                'branch_id' => $this->selectedBranchId,
                'date' => $date,
                'slot_time' => $time,
            ], [
                'day_of_week' => $date->dayOfWeek,
                'max_capacity' => 0,
                'current_booked' => 0,
                'is_blocked' => true,
                'block_reason' => $this->blockReasonInput,
            ]);
        }

        // Also create/update daily marker
        SlotConfiguration::updateOrCreate([
            'branch_id' => $this->selectedBranchId,
            'date' => $date,
            'slot_time' => '00:00',
        ], [
            'day_of_week' => $date->dayOfWeek,
            'max_capacity' => 0,
            'current_booked' => 0,
            'is_blocked' => true,
            'block_reason' => $this->blockReasonInput,
        ]);

        $this->closeBlockModal();
        $this->loadCalendarData();
        $this->loadDailySlots();

        $this->notify('success', 'Tanggal diblokir', "Tanggal {$date->format('d M Y')} telah diblokir: {$this->blockReasonInput}");
    }

    public function unblockDate(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);

        // Unblock all hourly slots
        SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('date', $date)
            ->where('slot_time', '!=', '00:00')
            ->update([
                'is_blocked' => false,
                'block_reason' => null,
                'max_capacity' => 1,
            ]);

        // Unblock daily marker
        SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('date', $date)
            ->where('slot_time', '00:00')
            ->update([
                'is_blocked' => false,
                'block_reason' => null,
                'max_capacity' => $this->dailyMaxCapacity,
            ]);

        $this->loadCalendarData();
        $this->loadDailySlots();

        $this->notify('success', 'Blokir dibuka', "Tanggal {$date->format('d M Y')} telah dibuka kembali");
    }

    public function confirmResetAll(): void
    {
        $this->showResetModal = true;
    }

    public function closeResetModal(): void
    {
        $this->showResetModal = false;
    }

    public function executeResetAll(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);

        SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('date', $date)
            ->delete();

        $this->closeResetModal();
        $this->loadCalendarData();
        $this->loadDailySlots();

        $this->notify('success', 'Slot direset', "Semua konfigurasi slot untuk {$date->format('d M Y')} telah dihapus");
    }

    public function updatedHourlySlots(string $time, array $slot): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        
        $slotData = [
            'branch_id' => $this->selectedBranchId,
            'date' => $date,
            'slot_time' => $time,
            'day_of_week' => $date->dayOfWeek,
            'max_capacity' => $slot['max_capacity'] ?? 1,
            'current_booked' => $slot['current_booked'] ?? 0,
            'is_blocked' => $slot['is_blocked'] ?? false,
            'block_reason' => $slot['block_reason'] ?? null,
        ];

        if ($slot['id']) {
            SlotConfiguration::where('id', $slot['id'])->update($slotData);
        } else {
            $created = SlotConfiguration::create($slotData);
            $this->hourlySlots[$time]['id'] = $created->id;
        }

        $this->loadCalendarData();
    }

    public function applyToAllSlots(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $firstSlot = array_values($this->hourlySlots)[0] ?? null;
        if (!$firstSlot) {
            return;
        }

        $templateCapacity = $firstSlot['max_capacity'];
        $templateBlocked = $firstSlot['is_blocked'];
        $templateReason = $firstSlot['block_reason'];

        $date = Carbon::parse($this->selectedDate);

        for ($hour = 8; $hour <= 17; $hour++) {
            $time = sprintf('%02d:00', $hour);
            
            SlotConfiguration::updateOrCreate([
                'branch_id' => $this->selectedBranchId,
                'date' => $date,
                'slot_time' => $time,
            ], [
                'day_of_week' => $date->dayOfWeek,
                'max_capacity' => $templateCapacity,
                'current_booked' => $this->hourlySlots[$time]['current_booked'] ?? 0,
                'is_blocked' => $templateBlocked,
                'block_reason' => $templateBlocked ? $templateReason : null,
            ]);
        }

        $this->loadDailySlots();
        $this->loadCalendarData();

        $this->notify('success', 'Diterapkan ke semua', "Konfigurasi slot diterapkan ke semua jam (08:00-17:00)");
    }

    public function getCurrentMonthLabelProperty(): string
    {
        return Carbon::create($this->currentYear, $this->currentMonth, 1)->translatedFormat('F Y');
    }

    protected function notify(string $type, string $title, string $body): void
    {
        $this->dispatch('notify', [
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);
    }
}