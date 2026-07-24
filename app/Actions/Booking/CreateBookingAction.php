<?php

namespace App\Actions\Booking;

use App\Models\Booking;

class CreateBookingAction
{
    /**
     * Execute the action to create a booking.
     *
     * @param array $data
     * @return Booking
     */
    public function execute(array $data): Booking
    {
        // TODO: Validate slot quota, calculate price, apply promo, save booking
        return Booking::create($data);
    }
}
