<x-filament-widgets::widget>
    <!-- Embedded Styles -->
    <style>
        .shift-schedule-widget-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        .dark .shift-schedule-widget-card {
            background-color: #18181b;
            border-color: #27272a;
            color: #f8fafc;
        }
        .shift-schedule-widget-card svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            display: inline-block;
            vertical-align: middle;
        }
        .shift-schedule-widget-card select {
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            color: #0f172a;
            cursor: pointer;
            appearance: auto;
        }
        .dark .shift-schedule-widget-card select {
            background-color: #27272a;
            border-color: #3f3f46;
            color: #f1f5f9;
        }
        .shift-schedule-widget-card table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .dark .shift-schedule-widget-card table {
            border-color: #27272a;
        }
        .shift-schedule-widget-card th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #cbd5e1;
        }
        .dark .shift-schedule-widget-card th {
            background-color: #27272a;
            color: #94a3b8;
            border-bottom-color: #3f3f46;
        }
        .shift-schedule-widget-card td {
            padding: 0.5rem;
            font-size: 0.875rem;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }
        .dark .shift-schedule-widget-card td {
            border-bottom-color: #27272a;
            color: #cbd5e1;
        }
        .shift-schedule-widget-card tr:hover {
            background-color: rgba(248, 250, 252, 0.8);
        }
        .dark .shift-schedule-widget-card tr:hover {
            background-color: rgba(39, 39, 42, 0.4);
        }
        
        .shift-schedule-widget-card .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .dark .shift-schedule-widget-card .badge-info { background-color: rgba(3, 105, 161, 0.2); color: #38bdf8; }
        
        .shift-schedule-widget-card .badge-warning { background-color: #fef3c7; color: #b45309; }
        .dark .shift-schedule-widget-card .badge-warning { background-color: rgba(180, 83, 9, 0.2); color: #fbbf24; }
        
        .shift-schedule-widget-card .badge-success { background-color: #dcfce7; color: #15803d; }
        .dark .shift-schedule-widget-card .badge-success { background-color: rgba(21, 128, 61, 0.2); color: #4ade80; }
        
        .shift-schedule-widget-card .badge-gray { background-color: #f1f5f9; color: #475569; }
        .dark .shift-schedule-widget-card .badge-gray { background-color: rgba(71, 85, 105, 0.2); color: #cbd5e1; }

        .shift-schedule-widget-card .badge-primary { background-color: #e0e7ff; color: #4338ca; }
        .dark .shift-schedule-widget-card .badge-primary { background-color: rgba(67, 56, 202, 0.2); color: #818cf8; }

        /* Sticky column for employee */
        .shift-schedule-widget-card .sticky-col {
            position: sticky;
            left: 0;
            z-index: 10;
            background-color: #ffffff !important;
            border-right: 1px solid #cbd5e1 !important;
            min-width: 160px;
        }
        .dark .shift-schedule-widget-card .sticky-col {
            background-color: #18181b !important;
            border-right-color: #3f3f46 !important;
        }

        /* Shift blocks */
        .shift-schedule-widget-card .shift-block {
            width: 100%;
            height: 2.25rem;
            border-radius: 0.5rem;
            border: 1px solid transparent;
            font-size: 0.6875rem;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .shift-schedule-widget-card .shift-block:hover {
            transform: scale(1.02);
        }
        .shift-schedule-widget-card .shift-active {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }
        .shift-schedule-widget-card .shift-completed {
            background-color: #16a34a;
            border-color: #16a34a;
            color: #ffffff;
        }
        .shift-schedule-widget-card .shift-scheduled {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            color: #475569;
        }
        .dark .shift-schedule-widget-card .shift-scheduled {
            background-color: #27272a;
            border-color: #3f3f46;
            color: #e2e8f0;
        }
        .shift-schedule-widget-card .shift-absent {
            background-color: #fee2e2;
            border-color: #fca5a5;
            color: #dc2626;
        }
        .dark .shift-schedule-widget-card .shift-absent {
            background-color: rgba(220, 38, 38, 0.1);
            border-color: rgba(220, 38, 38, 0.3);
            color: #f87171;
        }
        .shift-schedule-widget-card .shift-off {
            width: 100%;
            height: 2.25rem;
            border-radius: 0.5rem;
            border: 2px dashed #e2e8f0;
            background-color: rgba(248, 250, 252, 0.4);
            color: #94a3b8;
            font-size: 0.5625rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dark .shift-schedule-widget-card .shift-off {
            border-color: #27272a;
            background-color: rgba(39, 39, 42, 0.1);
            color: #71717a;
        }
        
        .shift-schedule-widget-card .today-highlight {
            outline: 2px solid #2563eb;
            outline-offset: 1px;
        }
        .shift-schedule-widget-card .today-header {
            background-color: rgba(37, 99, 235, 0.08) !important;
            color: #2563eb !important;
            font-weight: 800 !important;
            border-left: 1px solid rgba(37, 99, 235, 0.2);
            border-right: 1px solid rgba(37, 99, 235, 0.2);
        }
        .shift-schedule-widget-card .today-cell {
            background-color: rgba(37, 99, 235, 0.03) !important;
            border-left: 1px solid rgba(37, 99, 235, 0.1);
            border-right: 1px solid rgba(37, 99, 235, 0.1);
        }
    </style>

    <div class="shift-schedule-widget-card space-y-6">
        <!-- Header with Branch Selector and Week Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Jadwal Shift Mingguan
                </h3>
                
                <select 
                    wire:model="selectedBranchId"
                    wire:change="onBranchChanged"
                >
                    @foreach($branchOptions as $id => $name)
                        <option value="{{ $id }}" {{ $selectedBranchId == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button 
                    wire:click="previousWeek"
                    class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                    aria-label="Previous week"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <span class="px-2 text-xs font-bold text-gray-800 dark:text-gray-200 font-mono">
                    {{ Carbon\Carbon::parse($currentWeekStart)->translatedFormat('d M Y') }} - {{ Carbon\Carbon::parse($currentWeekStart)->copy()->endOfWeek(Carbon\Carbon::SUNDAY)->translatedFormat('d M Y') }}
                </span>

                <button 
                    wire:click="nextWeek"
                    class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                    aria-label="Next week"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <button 
                    wire:click="thisWeek"
                    class="ml-2 px-2.5 py-1 text-[11px] font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/30 hover:bg-primary-100 dark:hover:bg-primary-950/60 rounded-lg transition"
                >
                    Minggu Ini
                </button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="overflow-x-auto border border-gray-100 dark:border-gray-800/60 rounded-lg">
            <table class="w-full text-sm divide-y divide-gray-200 dark:divide-gray-800 min-w-[850px]">
                <thead class="bg-gray-50/50 dark:bg-gray-800/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[180px] sticky-col">
                            Karyawan
                        </th>
                        @foreach($weeks as $day)
                            <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider 
                                @if($day['is_today'])
                                    today-header
                                @endif">
                                <span class="block text-[10px]">{{ $day['day_name'] }}</span>
                                <span class="block text-sm font-mono mt-0.5">{{ $day['day_number'] }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-800">
                    @if(empty($employees))
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span>Tidak ada karyawan di cabang ini.</span>
                                </div>
                            </td>
                        </tr>
                    @else
                        @foreach($employees as $employee)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/10 transition-colors">
                                <td class="px-4 py-3 w-[180px] sticky-col">
                                    <div class="flex items-center gap-3">
                                        @if($employee['avatar'])
                                            <img class="w-7 h-7 rounded-full object-cover border border-gray-100 dark:border-gray-800" src="{{ asset('storage/' . $employee['avatar']) }}" alt="{{ $employee['user']['name'] }}">
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-primary-100/60 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-extrabold border border-primary-200/20">
                                                {{ Str::upper(Str::substr($employee['user']['name'], 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $employee['user']['name'] }}</p>
                                            @php
                                                $posClass = match ($employee['position']) {
                                                    'cashier' => 'badge-info',
                                                    'washer' => 'badge-warning',
                                                    'supervisor' => 'badge-success',
                                                    default => 'badge-gray',
                                                };
                                            @endphp
                                            <span class="badge {{ $posClass }} mt-0.5">
                                                {{ $employee['position'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                
                                @foreach($weeks as $day)
                                    <td class="p-1.5 align-middle text-center min-w-[90px]
                                        @if($day['is_today'])
                                            today-cell
                                        @endif">
                                        @php
                                            $shift = $shifts[$employee['id']][$day['date']] ?? null;
                                        @endphp
                                        
                                        @if($shift)
                                            @php
                                                $statusClass = match ($shift['status']) {
                                                    'active' => 'shift-active',
                                                    'completed' => 'shift-completed',
                                                    'scheduled' => 'shift-scheduled',
                                                    'absent' => 'shift-absent',
                                                    default => 'shift-scheduled',
                                                };
                                            @endphp
                                            <div class="shift-block {{ $statusClass }}
                                                @if($day['is_today'])
                                                    today-highlight
                                                @endif"
                                                wire:click="viewShift({{ $shift['id'] }})"
                                                title="{{ $shift['start_time'] }}-{{ $shift['end_time'] }} ({{ $shift['status'] }})">
                                                <span class="font-mono">{{ \Carbon\Carbon::parse($shift['start_time'])->format('H:i') }}</span>
                                                <span class="font-mono text-[9px] opacity-75 mt-0.5">{{ \Carbon\Carbon::parse($shift['end_time'])->format('H:i') }}</span>
                                            </div>
                                        @else
                                            <div class="shift-off">
                                                OFF
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-4 pt-4 border-t border-gray-150 dark:border-gray-800/80 text-[11px] text-gray-600 dark:text-gray-400">
            <span class="font-semibold text-gray-900 dark:text-white">Keterangan:</span>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-primary-500"></span>
                <span>Shift Aktif (Berjalan)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-success-500"></span>
                <span>Shift Selesai</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"></span>
                <span>Terjadwal</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 rounded bg-danger-100 dark:bg-danger-900/30 border border-danger-300 dark:border-danger-800"></span>
                <span>Absen / Tidak Hadir</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-flex px-1 rounded border-2 border-dashed border-primary-500 text-[9px] font-extrabold text-primary-500">HARI INI</span>
                <span>Hari Ini</span>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>