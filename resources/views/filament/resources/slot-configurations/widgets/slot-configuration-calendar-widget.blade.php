<div class="slot-configuration-calendar-widget">
    <div class="space-y-4">
        <!-- Header with Branch Selector and Month Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $currentMonthLabel }}
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
                    wire:click="previousMonth"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    aria-label="Bulan sebelumnya"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <select 
                    wire:model="currentMonth"
                    wire:change="onMonthChanged"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white"
                >
                    @foreach($months as $value => $label)
                        <option value="{{ $value }}" {{ $currentMonth == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <select 
                    wire:model="currentYear"
                    wire:change="onYearChanged"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white w-24"
                >
                    @for($y = $currentYear - 1; $y <= $currentYear + 2; $y++)
                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>

                <button 
                    wire:click="nextMonth"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    aria-label="Bulan berikutnya"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <a 
                    wire:click="goToToday"
                    class="px-3 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                >
                    Hari Ini
                </a>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                            <th class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $day }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeks as $weekIndex => $week)
                        <tr>
                            @foreach($week as $dayIndex => $day)
                                @if($day)
                                    <td class="p-1 relative">
                                        @include('filament.resources.slot-configurations.widgets.partials.calendar-day-cell', ['day' => $day])
                                    </td>
                                @else
                                    <td class="p-1"></td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-100 dark:bg-green-900/30 rounded border border-green-300 dark:border-green-700"></div>
                <span>Tersedia (0-50%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-100 dark:bg-yellow-900/30 rounded border border-yellow-300 dark:border-yellow-700"></div>
                <span>Hampir Penuh (51-80%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-100 dark:bg-red-900/30 rounded border border-red-300 dark:border-red-700"></div>
                <span>Penuh (81-100%)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-gray-100 dark:bg-gray-800 rounded border border-gray-300 dark:border-gray-600" 
                     style="background-image: repeating-linear-gradient(45deg, #ef4444 0, #ef4444 2px, transparent 2px, transparent 4px);"></div>
                <span>Diblokir</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-blue-100 dark:bg-blue-900/30 rounded border border-blue-300 dark:border-blue-700"></div>
                <span>Hari Ini</span>
            </div>
        </div>

        <!-- Right Panel - Daily Slot Management -->
        @if($selectedDate)
            @include('filament.resources.slot-configurations.widgets.partials.daily-slot-panel')
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Handle date selection from calendar
    document.addEventListener('click', function(e) {
        const dayCell = e.target.closest('[data-date]');
        if (dayCell) {
            const date = dayCell.getAttribute('data-date');
            @this.set('selectedDate', date);
            @this.loadDailySlots();
        }
    });
</script>
@endpush