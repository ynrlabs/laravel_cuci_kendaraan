<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingStatusNotification;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PaymentReceivedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $booking = $event->booking;
        
        try {
            // Send payment success notification to customer
            if ($booking->user && $booking->user->fcm_token) {
                Notification::send($booking->user, new PaymentSuccessNotification(
                    $booking,
                    $payment
                ));
            }
            
            // Database notification
            if ($booking->user) {
                $booking->user->notify(new PaymentSuccessNotification(
                    $booking,
                    $payment
                ));
            }
            
            // Trigger BookingConfirmed event if booking was pending payment
            if ($booking->status->value === 'waiting_payment' || $booking->status->value === 'pending') {
                event(new \App\Events\BookingConfirmed($booking, 'Pembayaran berhasil, booking dikonfirmasi otomatis'));
            }
            
            // Send email receipt
            if ($booking->user && $booking->user->email) {
                $booking->user->notify(new \App\Notifications\PaymentReceiptEmailNotification($booking, $payment));
            }
            
            Log::info('PaymentReceivedListener handled', [
                'payment_id' => $payment->id,
                'booking_id' => $booking->id,
                'amount' => $payment->amount,
                'method' => $payment->method,
            ]);
            
        } catch (\Exception $e) {
            Log::error('PaymentReceivedListener error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->release(30);
        }
    }
}