<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReconcilePaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?Payment $payment = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MidtransService $midtransService): void
    {
        try {
            $paymentsToCheck = $this->payment 
                ? collect([$this->payment]) 
                : Payment::whereIn('status', ['pending', 'processing'])
                    ->where('created_at', '>=', now()->subDays(1))
                    ->where('method', 'midtrans')
                    ->limit(100)
                    ->get();

            foreach ($paymentsToCheck as $payment) {
                $this->checkAndUpdatePayment($payment, $midtransService);
            }

            Log::info('ReconcilePaymentStatus completed', [
                'checked_count' => $paymentsToCheck->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('ReconcilePaymentStatus failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Check and update a single payment
     */
    protected function checkAndUpdatePayment(Payment $payment, MidtransService $midtransService): void
    {
        try {
            // Skip if no transaction ID
            if (!$payment->transaction_id) {
                Log::warning('ReconcilePaymentStatus - No transaction ID', [
                    'payment_id' => $payment->id,
                ]);
                return;
            }

            // Get status from Midtrans
            $midtransStatus = $midtransService->getTransactionStatus($payment->transaction_id);
            
            if (!$midtransStatus) {
                Log::warning('ReconcilePaymentStatus - Failed to get Midtrans status', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                ]);
                return;
            }

            $newStatus = $this->mapMidtransStatus($midtransStatus['transaction_status'] ?? '');
            
            // If status changed
            if ($newStatus && $newStatus !== $payment->status->value) {
                $oldStatus = $payment->status->value;
                
                $payment->status = $newStatus;
                
                if ($newStatus === \App\Enums\PaymentStatus::Paid) {
                    $payment->paid_at = now();
                    $payment->midtrans_response = $midtransStatus;
                    $payment->save();
                    
                    // Fire PaymentReceived event
                    if ($payment->booking) {
                        event(new \App\Events\PaymentReceived($payment, $payment->booking));
                    }
                } elseif ($newStatus === \App\Enums\PaymentStatus::Failed || 
                          $newStatus === \App\Enums\PaymentStatus::Expired ||
                          $newStatus === \App\Enums\PaymentStatus::Cancelled) {
                    $payment->midtrans_response = $midtransStatus;
                    $payment->save();
                    
                    // Auto-cancel booking if payment failed/expired
                    if ($payment->booking && in_array($payment->booking->status->value, ['pending', 'waiting_payment'])) {
                        $payment->booking->status = \App\Enums\BookingStatus::Cancelled;
                        $payment->booking->cancel_reason = 'Pembayaran ' . $newStatus->value;
                        $payment->booking->cancelled_at = now();
                        $payment->booking->save();
                        
                        event(new \App\Events\BookingCancelled(
                            $payment->booking,
                            'Pembayaran ' . $newStatus->value,
                            false
                        ));
                    }
                } else {
                    $payment->midtrans_response = $midtransStatus;
                    $payment->save();
                }

                Log::info('ReconcilePaymentStatus - Payment status updated', [
                    'payment_id' => $payment->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus->value,
                    'transaction_id' => $payment->transaction_id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('ReconcilePaymentStatus - Error checking payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map Midtrans transaction status to our PaymentStatus enum
     */
    protected function mapMidtransStatus(string $midtransStatus): ?\App\Enums\PaymentStatus
    {
        return match($midtransStatus) {
            'capture', 'settlement' => \App\Enums\PaymentStatus::Paid,
            'pending' => \App\Enums\PaymentStatus::Pending,
            'deny' => \App\Enums\PaymentStatus::Failed,
            'cancel', 'expire' => \App\Enums\PaymentStatus::Expired,
            'refund', 'partial_refund' => \App\Enums\PaymentStatus::Refunded,
            default => null,
        };
    }
}