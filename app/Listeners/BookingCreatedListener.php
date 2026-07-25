<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Notifications\BookingStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class BookingCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;
        
        try {
            // Update slot count - decrement available slots
            if ($booking->slot) {
                $booking->slot->decrement('current_bookings');
            }
            
            // Send booking confirmation to customer
            if ($booking->user && $booking->user->fcm_token) {
                Notification::send($booking->user, new BookingStatusNotification(
                    $booking,
                    'booking_created',
                    'Booking Berhasil Dibuat',
                    'Booking Anda (' . $booking->booking_code . ') untuk layanan ' . $booking->service->name . ' telah berhasil dibuat. Mohon tunggu konfirmasi dari kasir.'
                ));
            }
            
            // Send database notification
            if ($booking->user) {
                $booking->user->notify(new BookingStatusNotification(
                    $booking,
                    'booking_created',
                    'Booking Berhasil Dibuat',
                    'Booking Anda (' . $booking->booking_code . ') untuk layanan ' . $booking->service->name . ' telah berhasil dibuat.'
                ));
            }
            
            // Notify branch staff (kasir/operator) about new booking
            $this->notifyBranchStaff($booking);
            
            // Log the event
            Log::info('BookingCreatedListener handled', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'branch_id' => $booking->branch_id,
            ]);
            
        } catch (\Exception $e) {
            Log::error('BookingCreatedListener error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->release(30);
        }
    }
    
    /**
     * Notify branch staff about new booking
     */
    protected function notifyBranchStaff(Booking $booking): void
    {
        $staff = \App\Models\User::role(['kasir', 'operator', 'admin'])
            ->where('branch_id', $booking->branch_id)
            ->whereNotNull('fcm_token')
            ->get();
        
        if ($staff->isNotEmpty()) {
            $slotInfo = '';
            if ($booking->slot) {
                $slotInfo = ' pada ' . $booking->slot->date?->format('d/m/Y') . ' ' . $booking->slot->start_time?->format('H:i') . '-' . $booking->slot->end_time?->format('H:i');
            }
            
            Notification::send($staff, new BookingStatusNotification(
                $booking,
                'booking_created',
                'Booking Baru: ' . $booking->booking_code,
                'Booking baru dari ' . $booking->user->name . ' untuk ' . $booking->service->name . $slotInfo
            ));
        }
    }
}