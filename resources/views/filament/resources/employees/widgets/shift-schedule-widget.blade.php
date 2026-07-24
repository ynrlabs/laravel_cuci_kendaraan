<div class="shift-schedule-widget">
    <div class="space-y-4">
        <!-- Header with Branch Selector and Week Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Weekly Shift Schedule
                </h3>
                
                <select 
                    wire:model="selectedBranchId"
                    wire:change="onBranchChanged"
                    class="w-48 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white"
                >
                    @foreach($branchOptions as $id => $name)
                        <option value="{{ $id }}" {{ $selectedBranchId == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    wire:click="previousWeek"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    aria-label="Previous week"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <span class="px-4 py-1 font-sm font-bold flex items-center text-gray-900 dark:text-white">
                    {{ Carbon\Carbon::parse($currentWeekStart)->translatedFormat('d M Y') }} - {{ Carbon\Carbon::parse($currentWeekStart)->copy()->endOfWeek(Carbon\Carbon::SUNDAY)->translatedFormat('d M Y') }}
                </span>

                <button 
                    wire:click="nextWeek"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    aria-label="Next week"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <a 
                    wire:click="thisWeek"
                    class="px-3 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                >
                    This Week
                </a>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="p-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[180px] sticky left-0 z-10 bg-gray-50 dark:bg-gray-800/50">
                            EMPLOYEE
                        </th>
                        @foreach($weeks as $day)
                            <th class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider 
                                @if($day['is_today'])
                                    bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300
                                @elseif($day['is_weekend'])
                                    bg-gray-50 dark:bg-gray-800/50
                                @endif">
                                {{ $day['day_name'] }} {{ $day['day_number'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @if(empty($employees))
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No employees found for this branch.
                            </td>
                        </tr>
                    @else
                        @foreach($employees as $employee)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="p-2 w-[180px] sticky left-0 z-10 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if($employee['avatar'])
                                            <img class="w-8 h-8 rounded-full object-cover" src="{{ asset('storage/' . $employee['avatar']) }}" alt="{{ $employee['user']['name'] }}">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                                {{ Str::upper(Str::first($employee['user']['name'])) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $employee['user']['name'] }}</p>
                                            <span class="px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full
                                                @if($employee['position'] === 'cashier')
                                                    bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400
                                                @elseif($employee['position'] === 'washer')
                                                    bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400
                                                @elseif($employee['position'] === 'supervisor')
                                                    bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400
                                                @else
                                                    bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400
                                                @endif">
                                                {{ $employee['position'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                
                                @foreach($weeks as $day)
                                    <td class="p-1 relative">
                                        @php
                                            $shift = $shifts[$employee['id']][$day['date']] ?? null;
                                        @endphp
                                        
                                        @if($shift)
                                            <div class="w-full h-8 mx-0.5 rounded border text-[10px] font-bold flex items-center justify-center cursor-pointer
                                                @if($shift['status'] === 'active')
                                                    bg-primary-500 border-primary-500 text-white
                                                @elseif($shift['status'] === 'completed')
                                                    bg-success-500 border-success-500 text-white
                                                @elseif($shift['status'] === 'scheduled')
                                                    bg-gray-200 border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-300
                                                @elseif($shift['status'] === 'absent')
                                                    bg-danger-100 border-danger-300 dark:bg-danger-900/30 dark:border-danger-800 text-danger-700 dark:text-danger-400
                                                @endif
                                                @if($day['is_today'])
                                                    ring-2 ring-primary-500
                                                @endif"
                                                wire:click="viewShift({{ $shift['id'] }})"
                                                title="{{ $shift['start_time'] }}-{{ $shift['end_time'] }} ({{ $shift['status'] }})">
                                                {{ \Carbon\Carbon::parse($shift['start_time'])->format('H:i') }}-{{ \Carbon\Carbon::parse($shift['end_time'])->format('H:i') }}
                                            </div>
                                        @else
                                            <div class="w-full h-8 mx-0.5 rounded border-2 border-dashed border-gray-200 dark:border-gray-700
                                                @if($day['is_today'])
                                                    bg-primary-50 dark:bg-primary-900/10
                                                @else
                                                    bg-gray-50 dark:bg-gray-800/50
                                                @endif
                                                text-[10px] text-gray-400 dark:text-gray-600 flex items-center justify-center">
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
        <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                <span class="w-6 h-6 bg-primary-500 rounded border border-primary-500 flex items-center justify-center text-[10px] font-bold text-white">08-16</span>
                <span>Morning Shift</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                <span class="w-6 h-6 bg-amber-500 rounded border border-amber-500 flex items-center justify-center text-[10px] font-bold text-white">16-00</span>
                <span>Night Shift</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                <span class="w-6 h-6 bg-gray-200 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center text-[10px] font-bold text-gray-700 dark:text-gray-300">08-16</span>
                <span>Scheduled</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                <span class="w-6 h-6 bg-danger-100 dark:bg-danger-900/30 rounded border border-danger-300 dark:border-danger-800 flex items-center justify-center text-[10px] font-bold text-danger-700 dark:text-danger-400">OFF</span>
                <span>Absent/Off</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                <span class="w-6 h-6 rounded border-2 border-dashed border-primary-500 flex items-center justify-center text-[10px] font-bold text-primary-600 dark:text-primary-400">TODAY</span>
                <span>Highlighted</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle shift click
    document.addEventListener('click', function(e) {
        const shiftCell = e.target.closest('[wire\\:click*="viewShift"]');
        if (shiftCell) {
            e.preventDefault();
        }
    });
</script>
@endpush