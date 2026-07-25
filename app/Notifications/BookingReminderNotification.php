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

class BookingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
        public int $hoursUntilBooking = 24
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot', 'vehicle']);
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
            ->title('Pengingat Booking Besok')
            ->body('Booking ' . $this->booking->booking_code . ' untuk ' . $this->booking->service->name . ' dijadwalkan besok pukul ' . $this->booking->slot->start_time?->format('H:i'))
            ->image(asset('images/notifications/reminder.png'));
        
        $data = [
            'type' => 'booking_reminder',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'hours_until' => $this->hoursUntilBooking,
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
                    'channel_id' => 'booking_reminders',
                    'icon' => 'ic_notification',
                    'color' => '#F59E0B',
                    'sound' => 'default',
                ],
            ])
            ->apnsConfig([
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                        'category' => 'booking_reminder',
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
            'type' => 'booking_reminder',
            'title' => 'Pengingat Booking Besok',
            'body' => 'Booking ' . $this->booking->booking_code . ' dijadwalkan besok pukul ' . $this->booking->slot->start_time?->format('H:i'),
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'hours_until' => $this->hoursUntilBooking,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengingat: Booking Anda Besok - ' . $this->booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ini adalah pengingat bahwa Anda memiliki booking yang dijadwalkan besok:')
            ->line(' ')
            ->line('Detail Booking:')
            ->line('Kode Booking: ' . $this->booking->booking_code)
            ->line('Layanan: ' . $this->booking->service->name)
            ->line('Cabang: ' . $this->booking->branch->name)
            ->line('Tanggal: ' . $this->booking->slot->date?->format('d/m/Y'))
            ->line('Waktu: ' . $this->booking->slot->start_time?->format('H:i') . ' - ' . $this->booking->slot->end_time?->format('H:i'))
            ->when($this->booking->vehicle, function ($mail) {
                $mail->line('Kendaraan: ' . $this->booking->vehicle->plate_number . ' (' . $this->booking->vehicle->type . ')');
            })
            ->line(' ')
            ->line('Mohon datang tepat waktu. Jika ada perubahan, silakan hubungi cabang kami.')
            ->action('Lihat Detail Booking', route('customer.booking.show', $this->booking->id))
            ->salutation('Salam, Tim CuciKuy');
    }
}