<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Kelola Slot - {{ $selectedBranch ? $selectedBranch->name : 'Pilih Cabang' }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Kalender view dengan indikator kapasitas (X/Y Slots bar) - Bulan: {{ $currentMonthLabel }}
                </p>
            </div>
            
            <a 
                href="{{ static::getResource()::getUrl('index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar
            </a>
        </div>

        <!-- Calendar Widget -->
        @livewire('filament.resources.slot-configurations.widgets.slot-configuration-calendar-widget', [
            'selectedBranchId' => $selectedBranchId,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'selectedDate' => $selectedDate,
        ], key('slot-calendar-' . $selectedBranchId . '-' . $currentMonth . '-' . $currentYear))
    </div>
</x-filament-panels::page>

@push('scripts')
<script>
    // Listen for date selection from calendar widget
    document.addEventListener('livewire:load', function() {
        Livewire.on('date-selected', (event) => {
            @this.set('selectedDate', event.detail.date);
        });
    });
</script>
@endpush