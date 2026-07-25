<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Events\BookingCancelled;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCancelExpiredBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot', 'payment']);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if booking is still pending/unpaid
            if (!in_array($this->booking->status->value, ['pending', 'waiting_payment'])) {
                Log::info('AutoCancelExpiredBooking skipped - booking not pending', [
                    'booking_id' => $this->booking->id,
                    'status' => $this->booking->status->value,
                ]);
                return;
            }

            // Check if payment exists and is not paid
            if ($this->booking->payment && $this->booking->payment->status->value === 'paid') {
                Log::info('AutoCancelExpiredBooking skipped - payment already paid', [
                    'booking_id' => $this->booking->id,
                ]);
                return;
            }

            // Check if expired (beyond cutoff hours)
            $cutoffHours = config('app.booking_cutoff_hours', 2);
            $createdAt = $this->booking->created_at;
            
            if ($createdAt->diffInHours(now()) < $cutoffHours) {
                Log::info('AutoCancelExpiredBooking skipped - not yet expired', [
                    'booking_id' => $this->booking->id,
                    'hours_since_created' => $createdAt->diffInHours(now()),
                    'cutoff_hours' => $cutoffHours,
                ]);
                return;
            }

            // Cancel the booking
            $oldStatus = $this->booking->status->value;
            $this->booking->status = \App\Enums\BookingStatus::Cancelled;
            $this->booking->cancel_reason = 'Dibatalkan otomatis: Pembayaran tidak diterima dalam batas waktu ' . $cutoffHours . ' jam';
            $this->booking->cancelled_at = now();
            $this->booking->save();

            // Fire the cancelled event
            event(new BookingCancelled(
                $this->booking,
                $this->booking->cancel_reason,
                $this->booking->payment && $this->booking->payment->status->value === 'paid'
            ));

            Log::info('AutoCancelExpiredBooking completed', [
                'booking_id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'old_status' => $oldStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('AutoCancelExpiredBooking failed', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}