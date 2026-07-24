<div class="commission-rates-widget">
    <div class="space-y-4">
        <!-- Header with Branch Selector -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Commission Rates & Performance
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
                <span class="px-3 py-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                    This Week: {{ Carbon\Carbon::now()->startOfWeek()->format('d M') }} - {{ Carbon\Carbon::now()->endOfWeek()->format('d M Y') }}
                </span>
            </div>
        </div>

        <!-- Commission Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="p-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                            Employee
                        </th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Position
                        </th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Salary Type
                        </th>
                        <th class="p-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Base Commission
                        </th>
                        <th class="p-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Weekly Washes
                        </th>
                        <th class="p-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Commission Earned
                        </th>
                        <th class="p-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @if(empty($employees))
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No employees found for this branch.
                            </td>
                        </tr>
                    @else
                        @foreach($employees as $employee)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="p-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if($employee['avatar'])
                                            <img class="w-8 h-8 rounded-full object-cover" src="{{ asset('storage/' . $employee['avatar']) }}" alt="{{ $employee['name'] }}">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-xs font-bold">
                                                {{ Str::upper(Str::first($employee['name'])) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $employee['name'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $getPositionBadgeClass($employee['position']) }}">
                                        {{ ucfirst($employee['position']) }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $getSalaryTypeBadgeClass($employee['salary_type']) }}">
                                        {{ ucfirst(str_replace('_', ' ', $employee['salary_type'])) }}
                                    </span>
                                </td>
                                <td class="p-3 text-right font-mono text-sm text-gray-900 dark:text-white">
                                    {{ number_format($employee['base_commission'], 1) }}%
                                </td>
                                <td class="p-3 text-right font-mono text-sm text-gray-900 dark:text-white">
                                    {{ $employee['weekly_sales'] }}
                                </td>
                                <td class="p-3 text-right font-mono text-sm font-semibold text-success-600 dark:text-success-400">
                                    Rp {{ number_format($employee['commission_earned'], 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button class="p-1.5 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 rounded transition-colors" title="Edit Commission">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button class="p-1.5 text-gray-500 hover:text-danger-600 dark:text-gray-400 dark:hover:text-danger-400 rounded transition-colors" title="View Details">
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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                <span class="flex items-center gap-1">
                    <span class="font-mono font-semibold text-primary-600 dark:text-primary-400">{{ array_sum(array_column($employees, 'weekly_sales')) }}</span>
                    <span>Total Washes This Week</span>
                </span>
                <span class="flex items-center gap-1">
                    <span class="font-mono font-semibold text-success-600 dark:text-success-400">Rp {{ number_format(array_sum(array_column($employees, 'commission_earned')), 0, ',', '.') }}</span>
                    <span>Total Commissions</span>
                </span>
            </div>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 text-sm font-medium text-primary-600 dark:text-primary-400 border border-primary-600 dark:border-primary-400 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                    Export CSV
                </button>
            </div>
        </div>
        @endif
    </div>
</div>