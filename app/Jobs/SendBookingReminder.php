<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Notifications\BookingReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking,
        public int $hoursUntilBooking = 24
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot', 'vehicle']);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Only send reminder if booking is still confirmed/pending
            if (!in_array($this->booking->status->value, ['confirmed', 'pending'])) {
                Log::info('SendBookingReminder skipped - booking not confirmed', [
                    'booking_id' => $this->booking->id,
                    'status' => $this->booking->status->value,
                ]);
                return;
            }

            // Send reminder notification to customer
            if ($this->booking->user) {
                $this->booking->user->notify(new BookingReminderNotification(
                    $this->booking,
                    $this->hoursUntilBooking
                ));
            }

            Log::info('SendBookingReminder completed', [
                'booking_id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'hours_until' => $this->hoursUntilBooking,
            ]);

        } catch (\Exception $e) {
            Log::error('SendBookingReminder failed', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }
}