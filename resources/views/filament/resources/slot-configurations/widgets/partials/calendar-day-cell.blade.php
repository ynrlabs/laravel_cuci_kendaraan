@props(['day'])

@php
    $capacityColor = function ($percentage) {
        if ($percentage === 0) return 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700';
        if ($percentage <= 50) return 'bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-700';
        if ($percentage <= 80) return 'bg-yellow-100 dark:bg-yellow-900/30 border-yellow-300 dark:border-yellow-700';
        return 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700';
    };
    
    $isBlocked = $day['is_blocked'] ?? false;
    $isToday = $day['is_today'] ?? false;
    $isCurrentMonth = $day['is_current_month'] ?? true;
@endphp

<div 
    data-date="{{ $day['date'] }}"
    class="relative min-h-[100px] p-1 rounded-lg border transition-all duration-200 cursor-pointer hover:shadow-md 
           {{ $capacityColor($day['capacity_percentage']) }}
           {{ $isBlocked ? 'opacity-70' : '' }}
           {{ $isToday ? 'ring-2 ring-blue-500' : '' }}
           {{ !$isCurrentMonth ? 'opacity-50' : '' }}"
    wire:click="$dispatch('dateSelected', { date: '{{ $day['date'] }}' })"
    title="{{ $day['date'] }} - {{ $day['total_booked'] }}/{{ $day['total_capacity'] }} Slot ({{ $day['capacity_percentage'] }}%){{ $isBlocked ? ' - DIBLOKIR: ' . ($day['block_reason'] ?? '') : '' }}"
>
    <!-- Date number -->
    <div class="text-sm font-medium {{ !$isCurrentMonth ? 'text-gray-400' : ($isToday ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white') }}">
        {{ $day['day'] }}
    </div>

    <!-- Blocked indicator -->
    @if($isBlocked)
        <div class="absolute inset-0 bg-gradient-to-br from-red-500/10 to-transparent rounded-lg pointer-events-none" 
             style="background-image: repeating-linear-gradient(45deg, #ef4444 0, #ef4444 2px, transparent 2px, transparent 4px);">
        </div>
        <div class="absolute top-1 right-1 z-10">
            <span class="px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded">BLOKIR</span>
        </div>
    @endif

    <!-- Capacity Bar -->
    @if($day['total_capacity'] > 0)
        <div class="mt-2 space-y-1">
            <div class="flex justify-between text-xs">
                <span class="text-gray-600 dark:text-gray-400">Kapasitas</span>
                <span class="font-medium {{ $day['capacity_percentage'] >= 100 ? 'text-red-600 dark:text-red-400' : '' }}">
                    {{ $day['total_booked'] }} / {{ $day['total_capacity'] }}
                </span>
            </div>
            
            <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300 {{ $day['capacity_percentage'] >= 100 ? 'bg-red-500' : ($day['capacity_percentage'] >= 80 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                     style="width: {{ min($day['capacity_percentage'], 100) }}%">
                </div>
            </div>
            
            <div class="text-xs text-gray-500 dark:text-gray-400 text-right">
                {{ $day['capacity_percentage'] }}% terisi
            </div>
        </div>
    @else
        <div class="mt-2 text-center text-xs text-gray-400 dark:text-gray-500">
            <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">Belum disetel</span>
        </div>
    @endif

    <!-- Today indicator -->
    @if($isToday && !$isBlocked)
        <div class="absolute bottom-1 left-1/2 -translate-x-1/2">
            <span class="px-1.5 py-0.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                Hari Ini
            </span>
        </div>
    @endif
</div>