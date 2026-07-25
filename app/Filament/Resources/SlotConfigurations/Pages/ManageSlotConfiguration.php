<?php

namespace App\Filament\Resources\SlotConfigurations\Pages;

use App\Filament\Resources\SlotConfigurations\SlotConfigurationResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\SlotConfiguration;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ManageSlotConfiguration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SlotConfigurationResource::class;

    protected string $view = 'filament.resources.slot-configurations.pages.manage-slot-configuration';

    public ?Model $record = null;

    public ?array $data = [];

    public ?int $selectedBranchId = null;

    public ?string $selectedDate = null;

    public ?int $dailyMaxCapacity = 10;

    public ?bool $isBlocked = false;

    public ?string $blockReason = null;

    public array $hourlySlots = [];

    public ?Collection $months = null;

    public ?string $currentMonth = null;

    public ?int $currentYear = null;

    public function mount(): void
    {
        $this->selectedBranchId = request()->query('branch_id') ?? Branch::first()?->id;
        $this->currentMonth = request()->query('month') ?? now()->format('m');
        $this->currentYear = request()->query('year') ?? now()->year;
        $this->selectedDate = request()->query('date') ?? now()->format('Y-m-d');
        
        $this->loadData();
        $this->form->fill($this->data);
    }

    public function loadData(): void
    {
        if (!$this->selectedBranchId) {
            return;
        }

        $branch = Branch::find($this->selectedBranchId);
        if (!$branch) {
            return;
        }

        // Generate months for navigation (6 months back, 6 months forward)
        $months = collect();
        $start = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonths(6);
        for ($i = 0; $i < 13; $i++) {
            $date = $start->copy()->addMonths($i);
            $months->push([
                'value' => $date->format('Y-m'),
                'label' => $date->translatedFormat('F Y'),
                'is_current' => $date->format('Y-m') === ($this->currentYear . '-' . $this->currentMonth),
            ]);
        }
        $this->months = $months;

        // Load slot configurations for the current month
        $this->loadMonthlySlots();
        $this->loadDailySlots();
    }

    public function loadMonthlySlots(): void
    {
        if (!$this->selectedBranchId) {
            return;
        }

        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Load specific date slots
        $specificSlots = SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('type', 'specific')
            ->get()
            ->groupBy('date');

        // Load recurring slots
        $recurringSlots = SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('type', 'recurring')
            ->get()
            ->groupBy('day_of_week');

        $this->monthlySlots = [
            'specific' => $specificSlots,
            'recurring' => $recurringSlots,
        ];
    }

    public function loadDailySlots(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);

        // Load existing slots for this date
        $slots = SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('date', $date)
            ->orderBy('slot_time')
            ->get()
            ->keyBy('slot_time');

        // Get branch daily max capacity
        $branch = Branch::find($this->selectedBranchId);
        $this->dailyMaxCapacity = $branch->daily_max_capacity ?? 10;

        // Load or generate hourly slots (08:00 - 17:00)
        $this->hourlySlots = [];
        $isBlocked = false;
        $blockReason = null;

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
                    'notes' => $slot->notes,
                    'capacity_percentage' => $slot->max_capacity > 0 
                        ? round(($slot->current_booked / $slot->max_capacity) * 100) 
                        : 0,
                ];
                
                if ($slot->is_blocked) {
                    $isBlocked = true;
                    $blockReason = $slot->block_reason;
                }
            } else {
                $this->hourlySlots[$time] = [
                    'id' => null,
                    'time' => $time,
                    'max_capacity' => 1,
                    'current_booked' => 0,
                    'is_blocked' => false,
                    'block_reason' => null,
                    'notes' => null,
                    'capacity_percentage' => 0,
                ];
            }
        }

        $this->isBlocked = $isBlocked;
        $this->blockReason = $blockReason;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Cabang & Bulan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('selectedBranchId')
                                    ->label('Cabang')
                                    ->options(Branch::pluck('name', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state) => $this->onBranchChanged($state)),
                                
                                Select::make('currentMonth')
                                    ->label('Bulan')
                                    ->options($this->months?->pluck('label', 'value') ?? [])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state) => $this->onMonthChanged($state)),
                                
                                Select::make('currentYear')
                                    ->label('Tahun')
                                    ->options(fn () => collect(range(now()->year - 1, now()->year + 2))
                                        ->mapWithKeys(fn ($year) => [$year => (string) $year]))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state) => $this->onYearChanged($state)),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Panel Kontrol Slot Harian')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                DatePicker::make('selectedDate')
                                    ->label('Tanggal Target')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->format('Y-m-d')
                                    ->live()
                                    ->afterStateUpdated(fn ($state) => $this->onDateChanged($state)),

                                Slider::make('dailyMaxCapacity')
                                    ->label('Kapasitas Maks Harian')
                                    ->min(1)
                                    ->max(50)
                                    ->step(1)
                                    ->live()
                                    ->afterStateUpdated(fn ($state) => $this->onDailyMaxCapacityChanged($state)),

                                Action::make('blockDate')
                                    ->label('Blokir Tanggal')
                                    ->icon('heroicon-o-x-circle')
                                    ->color('danger')
                                    ->form([
                                        Textarea::make('blockReason')
                                            ->label('Alasan Blokir')
                                            ->required()
                                            ->rows(2),
                                    ])
                                    ->action(fn (array $data) => $this->blockDate($data['blockReason']))
                                    ->visible(fn () => !$this->isBlocked),

                                Action::make('resetAll')
                                    ->label('Reset Semua')
                                    ->icon('heroicon-o-arrow-path')
                                    ->color('warning')
                                    ->requiresConfirmation()
                                    ->modalHeading('Reset Semua Slot')
                                    ->modalDescription('Apakah Anda yakin ingin menghapus semua konfigurasi slot untuk tanggal ini? Tindakan ini tidak dapat dibatalkan.')
                                    ->action(fn () => $this->resetAllSlots())
                                    ->visible(fn () => count($this->hourlySlots) > 0),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Bulk Blokir Tanggal (Libur Nasional, dll)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('bulkBlockDateFrom')
                                    ->label('Dari Tanggal')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->format('Y-m-d'),
                                
                                DatePicker::make('bulkBlockDateUntil')
                                    ->label('Sampai Tanggal')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->format('Y-m-d'),
                                
                                Textarea::make('bulkBlockReason')
                                    ->label('Alasan Blokir Massal')
                                    ->placeholder('Contoh: Libur Nasional, Maintenance, dll.')
                                    ->rows(2),
                            ]),
                        
                        Action::make('bulkBlockDates')
                            ->label('Blokir Rentang Tanggal')
                            ->icon('heroicon-o-calendar-days')
                            ->color('danger')
                            ->form([
                                DatePicker::make('date_from')->label('Dari Tanggal')->required()->native(false)->displayFormat('d M Y')->format('Y-m-d'),
                                DatePicker::make('date_until')->label('Sampai Tanggal')->required()->native(false)->displayFormat('d M Y')->format('Y-m-d'),
                                Textarea::make('reason')->label('Alasan')->required()->rows(2)->placeholder('Contoh: Libur Nasional, Maintenance, Cuti Bersama'),
                            ])
                            ->action(fn (array $data) => $this->bulkBlockDates($data['date_from'], $data['date_until'], $data['reason']))
                            ->requiresConfirmation()
                            ->modalHeading('Blokir Rentang Tanggal')
                            ->modalDescription('Ini akan memblokir semua slot pada rentang tanggal yang dipilih.'),
                    ])
                    ->columns(1),

                Section::make('Override Slot (Jadwal Khusus - Contoh: Jam Operasional Ramadhan)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('overrideDateFrom')
                                    ->label('Dari Tanggal')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->format('Y-m-d'),
                                
                                DatePicker::make('overrideDateUntil')
                                    ->label('Sampai Tanggal')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->format('Y-m-d'),
                                
                                Select::make('overrideDayOfWeek')
                                    ->label('Hari dalam Minggu (opsional - kosongkan untuk semua hari)')
                                    ->options([
                                        '' => 'Semua Hari',
                                        'monday' => 'Senin',
                                        'tuesday' => 'Selasa',
                                        'wednesday' => 'Rabu',
                                        'thursday' => 'Kamis',
                                        'friday' => 'Jumat',
                                        'saturday' => 'Sabtu',
                                        'sunday' => 'Minggu',
                                    ])
                                    ->placeholder('Semua Hari'),
                            ]),
                        
                        Grid::make(4)
                            ->schema([
                                TimePicker::make('overrideStartTime')
                                    ->label('Jam Mulai')
                                    ->seconds(false)
                                    ->default('08:00'),
                                
                                TimePicker::make('overrideEndTime')
                                    ->label('Jam Selesai')
                                    ->seconds(false)
                                    ->default('17:00'),
                                
                                TextInput::make('overrideMaxCapacity')
                                    ->label('Maks Slot per Jam')
                                    ->type('number')
                                    ->minValue(0)
                                    ->maxValue(20)
                                    ->default(1)
                                    ->step(1),
                                
                                TextInput::make('overrideReason')
                                    ->label('Keterangan')
                                    ->placeholder('Contoh: Jam Operasional Ramadhan'),
                            ]),
                        
                        Action::make('applyOverride')
                            ->label('Terapkan Override')
                            ->icon('heroicon-o-clock')
                            ->color('primary')
                            ->form([
                                DatePicker::make('date_from')->label('Dari Tanggal')->required()->native(false)->displayFormat('d M Y')->format('Y-m-d'),
                                DatePicker::make('date_until')->label('Sampai Tanggal')->required()->native(false)->displayFormat('d M Y')->format('Y-m-d'),
                                Select::make('day_of_week')->label('Hari (opsional)')->options([
                                    '' => 'Semua Hari',
                                    'monday' => 'Senin',
                                    'tuesday' => 'Selasa',
                                    'wednesday' => 'Rabu',
                                    'thursday' => 'Kamis',
                                    'friday' => 'Jumat',
                                    'saturday' => 'Sabtu',
                                    'sunday' => 'Minggu',
                                ])->placeholder('Semua Hari'),
                                TimePicker::make('start_time')->label('Jam Mulai')->required()->seconds(false)->default('08:00'),
                                TimePicker::make('end_time')->label('Jam Selesai')->required()->seconds(false)->default('17:00'),
                                TextInput::make('max_capacity')->label('Maks Slot per Jam')->type('number')->minValue(0)->maxValue(20)->default(1)->step(1)->required(),
                                TextInput::make('reason')->label('Keterangan')->placeholder('Contoh: Jam Operasional Ramadhan')->required(),
                            ])
                            ->action(fn (array $data) => $this->applySlotOverride($data))
                            ->requiresConfirmation()
                            ->modalHeading('Terapkan Override Slot')
                            ->modalDescription('Ini akan mengoverride slot yang sudah ada pada rentang tanggal/jam yang dipilih.'),
                    ])
                    ->columns(1),

                Section::make('Daftar Kapasitas Per Jam (08:00 - 17:00)')
                    ->schema([
                        Repeater::make('hourlySlots')
                            ->schema([
                                Hidden::make('time'),
                                Hidden::make('id'),
                                
                                Grid::make(5)
                                    ->schema([
                                        TextInput::make('time')
                                            ->label('Jam')
                                            ->disabled()
                                            ->columnSpan(1),
                                        
                                        TextInput::make('max_capacity')
                                            ->label('Maks Slot')
                                            ->type('number')
                                            ->minValue(0)
                                            ->maxValue(20)
                                            ->step(1)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $this->updateHourlySlot($get('time'), 'max_capacity', (int)$state);
                                                $this->recalculateDailyCapacity();
                                            })
                                            ->columnSpan(1),
                                        
                                        TextInput::make('current_booked')
                                            ->label('Terisi')
                                            ->type('number')
                                            ->disabled()
                                            ->columnSpan(1),
                                        
                                        Toggle::make('is_blocked')
                                            ->label('Blokir')
                                            ->inline(false)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $this->updateHourlySlot($get('time'), 'is_blocked', $state);
                                            })
                                            ->columnSpan(1),
                                        
                                        TextInput::make('block_reason')
                                            ->label('Alasan')
                                            ->placeholder('Alasan blokir...')
                                            ->visible(fn (Get $get) => $get('is_blocked'))
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->columns(1)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false)
                            ->items($this->hourlySlots),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function onBranchChanged($branchId): void
    {
        $this->selectedBranchId = $branchId;
        $this->loadData();
        $this->redirect(static::getUrl('manage', ['record' => 1]) . "?branch_id={$branchId}&month={$this->currentMonth}&year={$this->currentYear}");
    }

    public function onMonthChanged($month): void
    {
        $this->currentMonth = $month;
        $this->loadData();
        $this->redirect(static::getUrl('manage', ['record' => 1]) . "?branch_id={$this->selectedBranchId}&month={$month}&year={$this->currentYear}");
    }

    public function onYearChanged($year): void
    {
        $this->currentYear = $year;
        $this->loadData();
        $this->redirect(static::getUrl('manage', ['record' => 1]) . "?branch_id={$this->selectedBranchId}&month={$this->currentMonth}&year={$year}");
    }

    public function onDateChanged($date): void
    {
        $this->selectedDate = $date;
        $this->loadDailySlots();
        $this->redirect(static::getUrl('manage', ['record' => 1]) . "?branch_id={$this->selectedBranchId}&month={$this->currentMonth}&year={$this->currentYear}&date={$date}");
    }

    public function onDailyMaxCapacityChanged($capacity): void
    {
        $this->dailyMaxCapacity = $capacity;
        $this->recalculateDailyCapacity();
    }

    public function recalculateDailyCapacity(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        
        // Update or create daily max capacity record
        $dailySlot = SlotConfiguration::firstOrCreate([
            'branch_id' => $this->selectedBranchId,
            'date' => $date,
            'slot_time' => '00:00', // Daily max capacity marker
        ], [
            'day_of_week' => $date->dayOfWeek,
            'max_capacity' => $this->dailyMaxCapacity,
            'current_booked' => 0,
            'is_blocked' => false,
        ]);

        if (!$dailySlot->wasRecentlyCreated) {
            $dailySlot->update(['max_capacity' => $this->dailyMaxCapacity]);
        }

        // Also update branch daily_max_capacity
        Branch::where('id', $this->selectedBranchId)->update(['daily_max_capacity' => $this->dailyMaxCapacity]);

        Notification::make()
            ->title('Kapasitas harian diperbarui')
            ->body("Kapasitas maksimum harian diatur ke {$this->dailyMaxCapacity}")
            ->success()
            ->send();
    }

    public function updateHourlySlot(string $time, string $field, mixed $value): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        
        $slot = SlotConfiguration::firstOrCreate([
            'branch_id' => $this->selectedBranchId,
            'date' => $date,
            'slot_time' => $time,
        ], [
            'day_of_week' => $date->dayOfWeek,
            'max_capacity' => 1,
            'current_booked' => 0,
            'is_blocked' => false,
        ]);

        $slot->update([$field => $value]);

        // Reload daily slots
        $this->loadDailySlots();
    }

    public function blockDate(string $reason): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);

        // Block all hourly slots for this date
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
                'block_reason' => $reason,
            ]);
        }

        // Also create a daily blocked record
        SlotConfiguration::updateOrCreate([
            'branch_id' => $this->selectedBranchId,
            'date' => $date,
            'slot_time' => '00:00',
        ], [
            'day_of_week' => $date->dayOfWeek,
            'max_capacity' => 0,
            'current_booked' => 0,
            'is_blocked' => true,
            'block_reason' => $reason,
        ]);

        $this->loadDailySlots();
        $this->loadMonthlySlots();

        Notification::make()
            ->title('Tanggal diblokir')
            ->body("Tanggal {$date->format('d M Y')} telah diblokir: {$reason}")
            ->success()
            ->send();
    }

    public function resetAllSlots(): void
    {
        if (!$this->selectedBranchId || !$this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);

        SlotConfiguration::where('branch_id', $this->selectedBranchId)
            ->where('date', $date)
            ->delete();

        $this->loadDailySlots();
        $this->loadMonthlySlots();

        Notification::make()
            ->title('Slot direset')
            ->body("Semua konfigurasi slot untuk {$date->format('d M Y')} telah dihapus")
            ->success()
            ->send();
    }

    public function bulkBlockDates(string $dateFrom, string $dateUntil, string $reason): void
    {
        if (!$this->selectedBranchId || empty($reason)) {
            $this->notify('danger', 'Gagal', 'Cabang dan alasan harus diisi');
            return;
        }

        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateUntil);

        if ($startDate->gt($endDate)) {
            $this->notify('danger', 'Gagal', 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai');
            return;
        }

        $daysBlocked = 0;
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Block all hourly slots for this date
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
                    'block_reason' => $reason,
                    'type' => 'specific',
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
                'block_reason' => $reason,
                'type' => 'specific',
            ]);

            $daysBlocked++;
        }

        $this->loadDailySlots();
        $this->loadMonthlySlots();

        $this->notify('success', 'Berhasil', "Berhasil memblokir {$daysBlocked} hari: {$reason}");
    }

    public function applySlotOverride(array $data): void
    {
        if (!$this->selectedBranchId) {
            $this->notify('danger', 'Gagal', 'Pilih cabang terlebih dahulu');
            return;
        }

        $dateFrom = Carbon::parse($data['date_from']);
        $dateUntil = Carbon::parse($data['date_until']);
        $dayOfWeek = $data['day_of_week'] ?? null;
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];
        $maxCapacity = (int) $data['max_capacity'];
        $reason = $data['reason'];

        if ($dateFrom->gt($dateUntil)) {
            $this->notify('danger', 'Gagal', 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai');
            return;
        }

        $startHour = (int) substr($startTime, 0, 2);
        $endHour = (int) substr($endTime, 0, 2);

        if ($startHour >= $endHour) {
            $this->notify('danger', 'Gagal', 'Jam mulai harus sebelum jam selesai');
            return;
        }

        $slotsCreated = 0;
        $period = CarbonPeriod::create($dateFrom, $dateUntil);

        foreach ($period as $date) {
            // If day_of_week is specified, only apply to that day
            if ($dayOfWeek) {
                $dayMap = [
                    0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday',
                    4 => 'thursday', 5 => 'friday', 6 => 'saturday'
                ];
                if ($dayMap[$date->dayOfWeek] !== $dayOfWeek) {
                    continue;
                }
            }

            for ($hour = $startHour; $hour < $endHour; $hour++) {
                $time = sprintf('%02d:00', $hour);
                
                SlotConfiguration::updateOrCreate([
                    'branch_id' => $this->selectedBranchId,
                    'date' => $date,
                    'slot_time' => $time,
                ], [
                    'day_of_week' => $date->dayOfWeek,
                    'max_capacity' => $maxCapacity,
                    'current_booked' => 0,
                    'is_blocked' => false,
                    'block_reason' => null,
                    'notes' => $reason,
                    'type' => 'specific',
                ]);

                $slotsCreated++;
            }
        }

        $this->loadDailySlots();
        $this->loadMonthlySlots();

        $this->notify('success', 'Berhasil', "Override diterapkan ke {$slotsCreated} slot: {$reason}");
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToList')
                ->label('Kembali ke Daftar')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl('index')),
        ];
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
