<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRefund implements ShouldQueue
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
        public Booking $booking,
        public Payment $payment
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MidtransService $midtransService): void
    {
        try {
            // Only process if payment was made via Midtrans and is paid
            if ($this->payment->method !== 'midtrans' || $this->payment->status !== \App\Enums\PaymentStatus::Paid) {
                Log::info('ProcessRefund skipped - not eligible for refund', [
                    'payment_id' => $this->payment->id,
                    'method' => $this->payment->method,
                    'status' => $this->payment->status->value,
                ]);
                return;
            }

            // Check if already refunded
            if ($this->payment->status === \App\Enums\PaymentStatus::Refunded) {
                Log::info('ProcessRefund skipped - already refunded', [
                    'payment_id' => $this->payment->id,
                ]);
                return;
            }

            // Request refund from Midtrans
            if ($this->payment->transaction_id) {
                $refundResult = $midtransService->refundTransaction(
                    $this->payment->transaction_id,
                    $this->payment->amount
                );

                if ($refundResult) {
                    // Update payment status
                    $this->payment->status = \App\Enums\PaymentStatus::Refunded;
                    $this->payment->midtrans_response = array_merge(
                        $this->payment->midtrans_response ?? [],
                        ['refund' => $refundResult]
                    );
                    $this->payment->refunded_at = now();
                    $this->payment->save();

                    // Update booking
                    $this->booking->refunded_at = now();
                    $this->booking->refund_amount = $this->payment->amount;
                    $this->booking->save();

                    // Notify customer
                    if ($this->booking->user) {
                        $this->booking->user->notify(new \App\Notifications\RefundNotification(
                            $this->booking,
                            $this->payment
                        ));
                    }

                    Log::info('ProcessRefund completed', [
                        'payment_id' => $this->payment->id,
                        'booking_id' => $this->booking->id,
                        'amount' => $this->payment->amount,
                        'refund_transaction_id' => $refundResult['refund_id'] ?? null,
                    ]);
                } else {
                    Log::error('ProcessRefund failed - Midtrans refund returned false', [
                        'payment_id' => $this->payment->id,
                        'transaction_id' => $this->payment->transaction_id,
                    ]);
                    
                    // Schedule retry or notify admin
                    $this->release(300); // Retry in 5 minutes
                }
            } else {
                Log::error('ProcessRefund failed - no transaction ID', [
                    'payment_id' => $this->payment->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('ProcessRefund error', [
                'payment_id' => $this->payment->id,
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}