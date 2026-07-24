<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Enums\BookingStatus;

class CancelBookingAction
{
    /**
     * Execute the action to cancel a booking.
     *
     * @param Booking $booking
     * @param string $reason
     * @param string|null $userId
     * @return Booking
     */
    public function execute(Booking $booking, string $reason, ?string $userId = null): Booking
    {
        // TODO: Release slot configuration booked count, transition status, and log
        $booking->update([
            'status' => BookingStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
            'cancelled_by' => $userId,
        ]);

        return $booking;
    }
}
