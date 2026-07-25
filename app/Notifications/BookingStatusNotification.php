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

class BookingStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
        public string $type,
        public string $title,
        public string $body,
        public array $data = []
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot', 'vehicle', 'payment']);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Add FCM if user has FCM token
        if ($notifiable->fcm_token) {
            $channels[] = FcmChannel::class;
        }
        
        // Add mail for important notifications
        if (in_array($this->type, ['booking_confirmed', 'payment_received', 'booking_cancelled', 'booking_completed']) && $notifiable->email) {
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
            ->title($this->title)
            ->body($this->body)
            ->image($this->getImageUrl());
        
        $data = array_merge([
            'type' => $this->type,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'status' => $this->booking->status->value,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ], $this->data);
        
        return FcmMessage::create()
            ->token($notifiable->fcm_token)
            ->notification($notification)
            ->data($data)
            ->priority('high')
            ->androidConfig([
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'booking_updates',
                    'icon' => 'ic_notification',
                    'color' => '#2563EB',
                    'sound' => 'default',
                ],
            ])
            ->apnsConfig([
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                        'category' => 'booking_update',
                    ],
                ],
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'status' => $this->booking->status->value,
            'service_name' => $this->booking->service->name,
            'branch_name' => $this->booking->branch->name,
            'slot_date' => $this->booking->slot?->date?->format('Y-m-d'),
            'slot_time' => $this->booking->slot?->start_time?->format('H:i'),
            'data' => $this->data,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line($this->body)
            ->line('Detail Booking:')
            ->line('Kode Booking: ' . $this->booking->booking_code)
            ->line('Layanan: ' . $this->booking->service->name)
            ->line('Cabang: ' . $this->booking->branch->name);
        
        if ($this->booking->slot) {
            $mail->line('Tanggal: ' . $this->booking->slot->date?->format('d/m/Y'))
                 ->line('Waktu: ' . $this->booking->slot->start_time?->format('H:i') . ' - ' . $this->booking->slot->end_time?->format('H:i'));
        }
        
        if ($this->booking->vehicle) {
            $mail->line('Kendaraan: ' . $this->booking->vehicle->plate_number . ' (' . $this->booking->vehicle->type . ')');
        }
        
        $actionUrl = $this->getActionUrl();
        if ($actionUrl) {
            $mail->action('Lihat Detail', $actionUrl);
        }
        
        return $mail
            ->line('Terima kasih telah menggunakan CuciKuy!')
            ->salutation('Salam, Tim CuciKuy');
    }

    /**
     * Get image URL based on notification type
     */
    protected function getImageUrl(): ?string
    {
        return match($this->type) {
            'booking_created' => asset('images/notifications/booking-created.png'),
            'booking_confirmed' => asset('images/notifications/booking-confirmed.png'),
            'booking_completed' => asset('images/notifications/booking-completed.png'),
            'booking_cancelled' => asset('images/notifications/booking-cancelled.png'),
            'payment_received' => asset('images/notifications/payment-success.png'),
            default => null,
        };
    }

    /**
     * Get action URL for the notification
     */
    protected function getActionUrl(): ?string
    {
        return match($this->type) {
            'booking_created', 'booking_confirmed', 'status_updated' => route('customer.booking.show', $this->booking->id),
            'payment_received' => route('customer.payment.show', $this->booking->payment?->id ?? $this->booking->id),
            'booking_cancelled', 'booking_completed' => route('customer.booking.show', $this->booking->id),
            default => route('customer.dashboard'),
        };
    }
}