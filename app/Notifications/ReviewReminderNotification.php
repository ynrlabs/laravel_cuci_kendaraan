<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class ReviewReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking->load(['user', 'service', 'branch']);
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        if ($notifiable->fcm_token) {
            $channels[] = FcmChannel::class;
        }
        
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): FcmMessage
    {
        $notification = FcmNotification::create()
            ->title('Bagikan Pengalaman Anda!')
            ->body('Layanan ' . $this->booking->service->name . ' telah selesai. Berikan ulasan dan bintang untuk membantu kami berkembang!')
            ->image(asset('images/notifications/review.png'));
        
        $data = [
            'type' => 'review_reminder',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];
        
        return FcmMessage::create()
            ->token($notifiable->fcm_token)
            ->notification($notification)
            ->data($data)
            ->priority('high')
            ->androidConfig([
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'review_reminders',
                    'icon' => 'ic_notification',
                    'color' => '#8B5CF6',
                    'sound' => 'default',
                ],
            ])
            ->apnsConfig([
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                        'category' => 'review_reminder',
                    ],
                ],
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_reminder',
            'title' => 'Bagikan Pengalaman Anda!',
            'body' => 'Layanan ' . $this->booking->service->name . ' telah selesai. Berikan ulasan dan bintang untuk membantu kami berkembang!',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bagikan Pengalaman Anda di CuciKuy - ' . $this->booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Layanan ' . $this->booking->service->name . ' Anda di ' . $this->booking->branch->name . ' telah selesai.')
            ->line('Kami sangat menghargai umpan balik Anda untuk membantu kami meningkatkan layanan.')
            ->line(' ')
            ->line('Kode Booking: ' . $this->booking->booking_code)
            ->line('Tanggal: ' . $this->booking->completed_at?->format('d/m/Y'))
            ->line(' ')
            ->action('Beri Ulasan & Bintang', route('customer.booking.review', $this->booking->id))
            ->line('Ulasan Anda sangat berharga bagi kami dan pelanggan lain!')
            ->salutation('Salam, Tim CuciKuy');
    }
}