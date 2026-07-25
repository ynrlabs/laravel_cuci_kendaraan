<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Models\Booking;
use App\Models\Slot;
use App\Notifications\BookingStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class BookingCancelledListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;
        
        try {
            // Restore slot availability
            if ($booking->slot_id) {
                $slot = Slot::find($booking->slot_id);
                if ($slot) {
                    $slot->increment('current_bookings');
                    $slot->save();
                }
            }
            
            // Send notification to customer
            if ($booking->user && $booking->user->fcm_token) {
                Notification::send($booking->user, new BookingStatusNotification(
                    $booking,
                    'booking_cancelled',
                    'Booking Dibatalkan',
                    'Booking Anda (' . $booking->booking_code . ') telah dibatalkan. ' . 
                    ($event->cancelReason ?? $booking->cancel_reason ?? '')
                ));
            }
            
            // Database notification
            if ($booking->user) {
                $booking->user->notify(new BookingStatusNotification(
                    $booking,
                    'booking_cancelled',
                    'Booking Dibatalkan',
                    'Booking Anda (' . $booking->booking_code . ') telah dibatalkan. ' . 
                    ($event->cancelReason ?? $booking->cancel_reason ?? '')
                ));
            }
            
            // Send email notification
            if ($booking->user && $booking->user->email) {
                $booking->user->notify(new \App\Notifications\BookingStatusEmailNotification(
                    $booking, 
                    $booking->status->value, 
                    'cancelled'
                ));
            }
            
            // Notify branch staff
            $this->notifyBranchStaff($booking, $event->cancelReason);
            
            // Handle refund if required
            if ($event->refundRequired && $booking->payment) {
                $this->processRefund($booking);
            }
            
            Log::info('BookingCancelledListener handled', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'cancel_reason' => $event->cancelReason ?? $booking->cancel_reason,
                'refund_required' => $event->refundRequired,
            ]);
            
        } catch (\Exception $e) {
            Log::error('BookingCancelledListener error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->release(30);
        }
    }
    
    /**
     * Notify branch staff about cancellation
     */
    protected function notifyBranchStaff(Booking $booking, ?string $cancelReason): void
    {
        $staff = \App\Models\User::role(['kasir', 'operator', 'admin'])
            ->where('branch_id', $booking->branch_id)
            ->whereNotNull('fcm_token')
            ->get();
        
        if ($staff->isNotEmpty()) {
            Notification::send($staff, new BookingStatusNotification(
                $booking,
                'booking_cancelled',
                'Booking Dibatalkan: ' . $booking->booking_code,
                'Booking ' . $booking->booking_code . ' untuk ' . $booking->user->name . ' dibatalkan. ' . 
                ($cancelReason ?? $booking->cancel_reason ? 'Alasan: ' . ($cancelReason ?? $booking->cancel_reason) : '')
            ));
        }
    }
    
    /**
     * Process refund for cancelled booking
     */
    protected function processRefund(Booking $booking): void
    {
        $payment = $booking->payment;
        
        if (!$payment || $payment->status !== \App\Enums\PaymentStatus::Paid) {
            return;
        }
        
        // Dispatch refund job
        \App\Jobs\ProcessRefund::dispatch($booking, $payment);
    }
}