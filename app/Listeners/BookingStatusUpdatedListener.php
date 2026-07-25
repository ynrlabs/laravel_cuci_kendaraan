<?php

namespace App\Listeners;

use App\Events\BookingStatusUpdated;
use App\Models\Booking;
use App\Notifications\BookingStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class BookingStatusUpdatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BookingStatusUpdated $event): void
    {
        $booking = $event->booking;
        
        try {
            // Send push notification to customer
            if ($booking->user && $booking->user->fcm_token) {
                $title = $this->getNotificationTitle($event->newStatus);
                $message = $this->getNotificationMessage($booking, $event->newStatus, $event->notes);
                
                Notification::send($booking->user, new BookingStatusNotification(
                    $booking,
                    'status_updated',
                    $title,
                    $message
                ));
            }
            
            // Send database notification
            if ($booking->user) {
                $booking->user->notify(new BookingStatusNotification(
                    $booking,
                    'status_updated',
                    $this->getNotificationTitle($event->newStatus),
                    $this->getNotificationMessage($booking, $event->newStatus, $event->notes)
                ));
            }
            
            // Send email for important status changes
            $this->sendEmailNotification($booking, $event->oldStatus, $event->newStatus);
            
            // Notify branch staff for specific status changes
            $this->notifyBranchStaff($booking, $event->oldStatus, $event->newStatus);
            
            // Log status change
            Log::info('BookingStatusUpdatedListener handled', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'changed_by' => $event->changedBy,
            ]);
            
        } catch (\Exception $e) {
            Log::error('BookingStatusUpdatedListener error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->release(30);
        }
    }
    
    /**
     * Get notification title based on status
     */
    protected function getNotificationTitle(string $status): string
    {
        return match($status) {
            'confirmed' => 'Booking Dikonfirmasi',
            'in_progress' => 'Layanan Dimulai',
            'completed' => 'Layanan Selesai',
            'cancelled' => 'Booking Dibatalkan',
            'no_show' => 'Tidak Hadir',
            default => 'Status Booking Diperbarui',
        };
    }
    
    /**
     * Get notification message based on status
     */
    protected function getNotificationMessage(Booking $booking, string $status, ?string $notes): string
    {
        $baseMessage = 'Booking ' . $booking->booking_code . ' (' . $booking->service->name . ') ';
        
        $message = match($status) {
            'confirmed' => $baseMessage . 'telah dikonfirmasi. Silakan datang tepat waktu.',
            'in_progress' => $baseMessage . 'sedang diproses. Tim kami sedang menangani kendaraan Anda.',
            'completed' => $baseMessage . 'telah selesai. Terima kasih telah menggunakan layanan kami!',
            'cancelled' => $baseMessage . 'dibatalkan. ' . ($notes ?? 'Alasan: ' . $booking->cancel_reason),
            'no_show' => $baseMessage . 'dicatat sebagai tidak hadir (no-show).',
            default => $baseMessage . 'status diperbarui menjadi ' . $status,
        };
        
        if ($notes) {
            $message .= ' Catatan: ' . $notes;
        }
        
        return $message;
    }
    
    /**
     * Send email notification for important status changes
     */
    protected function sendEmailNotification(Booking $booking, string $oldStatus, string $newStatus): void
    {
        // Only send email for important status changes
        $importantStatuses = ['confirmed', 'completed', 'cancelled', 'no_show'];
        
        if (!in_array($newStatus, $importantStatuses) || !$booking->user || !$booking->user->email) {
            return;
        }
        
        // Email notifications will be sent via queued notifications
        $booking->user->notify(new \App\Notifications\BookingStatusEmailNotification($booking, $oldStatus, $newStatus));
    }
    
    /**
     * Notify branch staff about status updates
     */
    protected function notifyBranchStaff(Booking $booking, string $oldStatus, string $newStatus): void
    {
        // Only notify staff for specific status changes
        $notifyStatuses = ['confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];
        
        if (!in_array($newStatus, $notifyStatuses)) {
            return;
        }
        
        $staff = \App\Models\User::role(['kasir', 'operator', 'admin'])
            ->where('branch_id', $booking->branch_id)
            ->whereNotNull('fcm_token')
            ->get();
        
        if ($staff->isNotEmpty()) {
            $title = 'Status Diperbarui: ' . $booking->booking_code;
            $message = 'Booking ' . $booking->booking_code . ' untuk ' . $booking->user->name . ' berubah dari ' . $oldStatus . ' ke ' . $newStatus;
            
            Notification::send($staff, new BookingStatusNotification(
                $booking,
                'status_updated',
                $title,
                $message
            ));
        }
    }
}