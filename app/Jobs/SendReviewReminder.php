<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Notifications\ReviewReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReviewReminder implements ShouldQueue
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
        public Booking $booking
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot', 'vehicle']);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Only send if booking is completed
            if ($this->booking->status->value !== 'completed') {
                Log::info('SendReviewReminder skipped - booking not completed', [
                    'booking_id' => $this->booking->id,
                    'status' => $this->booking->status->value,
                ]);
                return;
            }

            // Check if review already exists
            if ($this->booking->review) {
                Log::info('SendReviewReminder skipped - review already exists', [
                    'booking_id' => $this->booking->id,
                ]);
                return;
            }

            // Send review reminder notification
            if ($this->booking->user) {
                $this->booking->user->notify(new ReviewReminderNotification($this->booking));
            }

            Log::info('SendReviewReminder completed', [
                'booking_id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
            ]);

        } catch (\Exception $e) {
            Log::error('SendReviewReminder failed', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}