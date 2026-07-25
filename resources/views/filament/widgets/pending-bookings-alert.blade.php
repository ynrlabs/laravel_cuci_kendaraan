<div class="fi-widget-content">
    @if($count > 0)
        <div class="fi-alert fi-alert-warning mb-4">
            <div class="fi-alert-icon">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="fi-alert-content">
                <p class="fi-alert-message font-medium">{{ $count }} booking{{ $count > 1 ? 's' : '' }} waiting for confirmation</p>
            </div>
        </div>

        <div class="space-y-2">
            @foreach($pendingBookings as $booking)
                <div class="fi-flex fi-flex-col fi-gap-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="fi-flex fi-items-center fi-justify-between">
                        <div class="fi-flex fi-items-center fi-gap-3">
                            <span class="fi-text-sm fi-font-medium fi-text-gray-900 dark:fi-text-gray-100">{{ $booking->slot_time->format('H:i') }}</span>
                            <span class="fi-px-2 fi-py-0.5 fi-text-xs fi-font-medium fi-bg-yellow-100 fi-text-yellow-800 dark:fi-bg-yellow-900 dark:fi-text-yellow-200 fi-rounded-full">
                                {{ $booking->service->name }}
                            </span>
                        </div>
                        <span class="fi-text-sm fi-text-gray-500 dark:fi-text-gray-400">
                            {{ $booking->branch?->name }}
                        </span>
                    </div>
                    <div class="fi-flex fi-items-center fi-justify-between fi-text-sm">
                        <span class="fi-font-medium fi-text-gray-900 dark:fi-text-gray-100">
                            {{ $booking->customer->name }}
                        </span>
                        <span class="fi-font-mono fi-text-gray-500 dark:fi-text-gray-400">
                            {{ strtoupper($booking->vehicle_plate) }}
                        </span>
                    </div>
                    <div class="fi-flex fi-items-center fi-justify-between fi-text-xs fi-text-gray-500 dark:fi-text-gray-400">
                        <span>{{ $booking->payment_method }}</span>
                        <span class="fi-font-mono">{{ $booking->total->format('Rp #,##0') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="fi-text-center fi-py-8">
            <svg class="mx-auto h-12 w-12 fi-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 fi-text-sm fi-text-gray-500 dark:fi-text-gray-400">No pending bookings today</p>
            <p class="fi-text-xs fi-text-gray-400 dark:fi-text-gray-500">All bookings are confirmed or paid.</p>
        </div>
    @endif
</div>