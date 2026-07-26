<x-filament-widgets::widget>
    <!-- Embedded Styles -->
    <style>
        .commission-rates-widget-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        .dark .commission-rates-widget-card {
            background-color: #18181b;
            border-color: #27272a;
            color: #f8fafc;
        }
        .commission-rates-widget-card svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            display: inline-block;
            vertical-align: middle;
        }
        .commission-rates-widget-card select {
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
        .dark .commission-rates-widget-card select {
            background-color: #27272a;
            border-color: #3f3f46;
            color: #f1f5f9;
        }
        .commission-rates-widget-card table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .dark .commission-rates-widget-card table {
            border-color: #27272a;
        }
        .commission-rates-widget-card th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #cbd5e1;
        }
        .dark .commission-rates-widget-card th {
            background-color: #27272a;
            color: #94a3b8;
            border-bottom-color: #3f3f46;
        }
        .commission-rates-widget-card td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }
        .dark .commission-rates-widget-card td {
            border-bottom-color: #27272a;
            color: #cbd5e1;
        }
        .commission-rates-widget-card tr:hover {
            background-color: rgba(248, 250, 252, 0.8);
        }
        .dark .commission-rates-widget-card tr:hover {
            background-color: rgba(39, 39, 42, 0.4);
        }
        
        .commission-rates-widget-card .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .dark .commission-rates-widget-card .badge-info { background-color: rgba(3, 105, 161, 0.2); color: #38bdf8; }
        
        .commission-rates-widget-card .badge-warning { background-color: #fef3c7; color: #b45309; }
        .dark .commission-rates-widget-card .badge-warning { background-color: rgba(180, 83, 9, 0.2); color: #fbbf24; }
        
        .commission-rates-widget-card .badge-success { background-color: #dcfce7; color: #15803d; }
        .dark .commission-rates-widget-card .badge-success { background-color: rgba(21, 128, 61, 0.2); color: #4ade80; }
        
        .commission-rates-widget-card .badge-gray { background-color: #f1f5f9; color: #475569; }
        .dark .commission-rates-widget-card .badge-gray { background-color: rgba(71, 85, 105, 0.2); color: #cbd5e1; }

        .commission-rates-widget-card .badge-primary { background-color: #e0e7ff; color: #4338ca; }
        .dark .commission-rates-widget-card .badge-primary { background-color: rgba(67, 56, 202, 0.2); color: #818cf8; }
    </style>

    <div class="commission-rates-widget-card space-y-6">
        <!-- Header with Branch Selector -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Komisi & Performa Karyawan
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

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Minggu Ini: {{ Carbon\Carbon::now()->startOfWeek()->format('d M') }} - {{ Carbon\Carbon::now()->endOfWeek()->format('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Commission Table -->
        <div class="overflow-x-auto border border-gray-100 dark:border-gray-800/60 rounded-lg">
            <table class="w-full text-sm divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50/50 dark:bg-gray-800/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Karyawan
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Jabatan
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Tipe Gaji
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Persentase Komisi
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Cucian Minggu Ini
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Komisi Diperoleh
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-800">
                    @if(empty($employees))
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
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
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($employee['avatar'])
                                            <img class="w-7 h-7 rounded-full object-cover border border-gray-100 dark:border-gray-800" src="{{ asset('storage/' . $employee['avatar']) }}" alt="{{ $employee['name'] }}">
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-primary-100/60 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-extrabold border border-primary-200/20">
                                                {{ Str::upper(Str::substr($employee['name'], 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $employee['name'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $posClass = match ($employee['position']) {
                                            'cashier' => 'badge-info',
                                            'washer' => 'badge-warning',
                                            'supervisor' => 'badge-success',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $posClass }}">
                                        {{ ucfirst($employee['position']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $salClass = match ($employee['salary_type']) {
                                            'monthly' => 'badge-primary',
                                            'per_wash' => 'badge-warning',
                                            'hourly' => 'badge-success',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $salClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $employee['salary_type'])) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($employee['base_commission'], 1) }}%
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-xs font-semibold text-gray-950 dark:text-white">
                                    {{ $employee['weekly_sales'] }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-sm font-bold text-success-600 dark:text-success-400">
                                    Rp {{ number_format($employee['commission_earned'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button class="p-1 text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Edit Komisi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Detail Performa">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Summary Footer -->
        @if(!empty($employees))
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4 border-t border-gray-150 dark:border-gray-800/80">
            <div class="flex flex-wrap gap-4 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/30 text-gray-600 dark:text-gray-400">
                    Total Cucian: 
                    <span class="font-mono font-bold text-gray-900 dark:text-white">{{ array_sum(array_column($employees, 'weekly_sales')) }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-success-50 dark:bg-success-950/20 border border-success-100/60 dark:border-success-900/10 text-success-700 dark:text-success-400">
                    Total Komisi Karyawan: 
                    <span class="font-mono font-bold">Rp {{ number_format(array_sum(array_column($employees, 'commission_earned')), 0, ',', '.') }}</span>
                </span>
            </div>
            <div>
                <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Ekspor Laporan (CSV)
                </button>
            </div>
        </div>
        @endif
    </div>
</x-filament-widgets::widget>