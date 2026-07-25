<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Models\Booking;
use App\Notifications\BookingStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class BookingConfirmedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking;
        
        try {
            // Send push notification to customer
            if ($booking->user && $booking->user->fcm_token) {
                Notification::send($booking->user, new BookingStatusNotification(
                    $booking,
                    'booking_confirmed',
                    'Booking Dikonfirmasi',
                    'Booking Anda (' . $booking->booking_code . ') telah dikonfirmasi. Silakan datang pada waktu yang telah dijadwalkan.'
                ));
            }
            
            // Send database notification to customer
            if ($booking->user) {
                $booking->user->notify(new BookingStatusNotification(
                    $booking,
                    'booking_confirmed',
                    'Booking Dikonfirmasi',
                    'Booking Anda (' . $booking->booking_code . ') telah dikonfirmasi. Silakan datang pada waktu yang telah dijadwalkan.'
                ));
            }
            
            // Notify branch staff
            $this->notifyBranchStaff($booking);
            
            // Log the event
            Log::info('BookingConfirmedListener handled', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
            ]);
            
        } catch (\Exception $e) {
            Log::error('BookingConfirmedListener error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->release(30);
        }
    }
    
    /**
     * Notify branch staff about confirmed booking
     */
    protected function notifyBranchStaff(Booking $booking): void
    {
        $staff = \App\Models\User::role(['kasir', 'operator', 'admin'])
            ->where('branch_id', $booking->branch_id)
            ->whereNotNull('fcm_token')
            ->get();
        
        if ($staff->isNotEmpty()) {
            Notification::send($staff, new BookingStatusNotification(
                $booking,
                'booking_confirmed',
                'Booking Dikonfirmasi: ' . $booking->booking_code,
                'Booking ' . $booking->booking_code . ' telah dikonfirmasi untuk ' . $booking->slot->start_time->format('H:i') . ' ' . $booking->slot->date->format('d/m/Y')
            ));
        }
    }
}