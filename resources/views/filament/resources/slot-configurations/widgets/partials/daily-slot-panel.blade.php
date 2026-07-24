@props(['selectedDate', 'hourlySlots', 'dailyMaxCapacity', 'isBlocked', 'blockReason', 'selectedBranchId'])

@php
    $date = \Carbon\Carbon::parse($selectedDate);
    $dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$date->dayOfWeek];
    $formattedDate = $date->format('d M Y');
@endphp

<div class="lg:w-96 flex-shrink-0">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 sticky top-4 h-fit">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $dayName }}, {{ $formattedDate }}
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Panel Kelola Slot Harian</p>
            </div>
            
            @if($isBlocked)
                <span class="px-2 py-1 text-xs font-bold text-white bg-red-600 rounded-full flex items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    DIBLOKIR
                </span>
            @endif
        </div>

        <!-- Date Picker -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Target</label>
            <input 
                type="date" 
                wire:model.debounce.300ms="selectedDate"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                value="{{ $selectedDate }}"
            >
        </div>

        <!-- Daily Max Capacity Slider -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kapasitas Maks Harian</label>
                <span class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $dailyMaxCapacity }} Slot</span>
            </div>
            
            <input 
                type="range" 
                wire:model.debounce.300ms="dailyMaxCapacity"
                min="1" 
                max="50" 
                step="1"
                class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary-500"
            >
            
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                <span>1</span>
                <span>50</span>
            </div>
            
            <button 
                wire:click="saveDailyMaxCapacity"
                class="w-full mt-2 px-3 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors"
            >
                Simpan Kapasitas Harian
            </button>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">

        <!-- Block Date / Reset All Actions -->
        <div class="space-y-2 mb-4">
            @if(!$isBlocked)
                <button 
                    wire:click="confirmBlockDate"
                    class="w-full px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Blokir Tanggal Ini
                </button>
            @else
                <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-700 dark:text-red-300 mb-1 font-medium">Alasan Blokir:</p>
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $blockReason }}</p>
                </div>
                
                <button 
                    wire:click="unblockDate"
                    class="w-full px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Buka Blokir
                </button>
            @endif

            <button 
                wire:click="confirmResetAll"
                class="w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-colors flex items-center justify-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset Semua Slot
            </button>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">

        <!-- Hourly Capacity List (08:00-17:00) -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h5 class="text-sm font-semibold text-gray-900 dark:text-white">Kapasitas Per Jam (08:00-17:00)</h5>
                <button 
                    wire:click="applyToAllSlots"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium"
                >
                    Terapkan ke Semua
                </button>
            </div>

            <div class="space-y-1 max-h-96 overflow-y-auto pr-1">
                @for($hour = 8; $hour <= 17; $hour++)
                    @php
                        $time = sprintf('%02d:00', $hour);
                        $slot = $hourlySlots[$time] ?? [
                            'id' => null,
                            'time' => $time,
                            'max_capacity' => 1,
                            'current_booked' => 0,
                            'is_blocked' => false,
                            'block_reason' => null,
                            'capacity_percentage' => 0,
                        ];
                    @endphp
                    
                    <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors hover:border-primary-300 dark:hover:border-primary-700">
                        <!-- Time -->
                        <div class="w-16 text-sm font-medium text-gray-700 dark:text-gray-300 flex-shrink-0">
                            {{ $time }}
                        </div>

                        <!-- Max Capacity Input -->
                        <input 
                            type="number" 
                            wire:model.debounce.300ms="hourlySlots.{{ $time }}.max_capacity"
                            min="0" 
                            max="20" 
                            step="1"
                            class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 focus:outline-none focus:ring-1 focus:ring-primary-500"
                            @if($slot['is_blocked']) disabled @endif
                        >

                        <!-- Current Booked (Display only) -->
                        <div class="w-16 text-sm text-center text-gray-500 dark:text-gray-400">
                            {{ $slot['current_booked'] }} terisi
                        </div>

                        <!-- Capacity Bar -->
                        <div class="flex-1 h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mr-2">
                            @php
                                $percentage = $slot['max_capacity'] > 0 ? min(round(($slot['current_booked'] / $slot['max_capacity']) * 100), 100) : 0;
                                $barColor = $percentage >= 100 ? 'bg-red-500' : ($percentage >= 80 ? 'bg-yellow-500' : 'bg-green-500');
                            @endphp
                            <div class="h-full {{ $barColor }} rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                        </div>

                        <!-- Percentage -->
                        <div class="w-16 text-xs text-right text-gray-500 dark:text-gray-400">
                            {{ $percentage }}%
                        </div>

                        <!-- Block Toggle -->
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:model.debounce.300ms="hourlySlots.{{ $time }}.is_blocked"
                                class="sr-only peer"
                            >
                            <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                        </label>

                        <!-- Block Reason Input (shown when blocked) -->
                        @if($slot['is_blocked'])
                            <input 
                                type="text" 
                                wire:model.debounce.300ms="hourlySlots.{{ $time }}.block_reason"
                                placeholder="Alasan..."
                                class="w-24 px-2 py-1 text-xs border border-red-300 dark:border-red-700 rounded bg-red-50 dark:bg-red-900/20 focus:outline-none focus:ring-1 focus:ring-red-500"
                            >
                        @endif
                    </div>
                @endfor
            </div>

            <!-- Summary -->
            @php
                $totalSlots = 0;
                $totalBooked = 0;
                $blockedCount = 0;
                
                foreach ($hourlySlots as $slot) {
                    if (!empty($slot['max_capacity'])) {
                        $totalSlots += $slot['max_capacity'];
                        $totalBooked += $slot['current_booked'];
                    }
                    if (!empty($slot['is_blocked'])) {
                        $blockedCount++;
                    }
                }
            @endphp

            <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalSlots }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Slot</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $totalBooked }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Terisi</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold {{ $blockedCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $blockedCount }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Diblokir</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block Date Confirmation Modal -->
@if($showBlockModal)
<div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeBlockModal"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Blokir Tanggal {{ $formattedDate }}</h3>
            
            <p class="text-gray-600 dark:text-gray-300 mb-4">
                Semua slot pada tanggal ini akan diblokir dan tidak dapat dibooking. Masukkan alasan blokir:
            </p>
            
            <textarea 
                wire:model="blockReason"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                placeholder="Contoh: Libur nasional, maintenance, dll."
            ></textarea>
            
            <div class="flex justify-end gap-3 mt-4">
                <button 
                    wire:click="closeBlockModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                    Batal
                </button>
                
                <button 
                    wire:click="executeBlockDate"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                >
                    Blokir Tanggal
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Reset All Confirmation Modal -->
@if($showResetModal)
<div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeResetModal"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reset Semua Slot</h3>
            
            <p class="text-gray-600 dark:text-gray-300 mb-4">
                Apakah Anda yakin ingin menghapus <strong>semua konfigurasi slot</strong> untuk tanggal {{ $formattedDate }}? 
                Tindakan ini tidak dapat dibatalkan.
            </p>
            
            <div class="flex justify-end gap-3">
                <button 
                    wire:click="closeResetModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                    Batal
                </button>
                
                <button 
                    wire:click="executeResetAll"
                    class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition-colors"
                >
                    Ya, Reset Semua
                </button>
            </div>
        </div>
    </div>
</div>
@endif