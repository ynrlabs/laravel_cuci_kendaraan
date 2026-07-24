<?php

namespace App\Actions\Payment;

use App\Models\Booking;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Enums\BookingStatus;

class ProcessPaymentAction
{
    /**
     * Execute the action to process a payment and update the booking.
     *
     * @param Payment $payment
     * @param array $gatewayData
     * @return Payment
     */
    public function execute(Payment $payment, array $gatewayData): Payment
    {
        // TODO: Validate signature, handle success/failure transitions
        $payment->update([
            'status' => $gatewayData['status'], // e.g. 'success'
            'paid_at' => now(),
            'raw_response' => $gatewayData,
        ]);

        if ($gatewayData['status'] === 'success') {
            $payment->booking->update([
                'payment_status' => PaymentStatus::PAID,
                'status' => BookingStatus::CONFIRMED,
            ]);
        }

        return $payment;
    }
}
