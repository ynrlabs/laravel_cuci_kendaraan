<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class RefundNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
        public Payment $payment
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot']);
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
            ->title('Refund Diproses')
            ->body('Refund sebesar Rp ' . number_format($this->payment->amount, 0, ',', '.') . ' untuk booking ' . $this->booking->booking_code . ' telah diproses.')
            ->image(asset('images/notifications/refund.png'));
        
        $data = [
            'type' => 'refund_processed',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
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
                    'channel_id' => 'payment_updates',
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
                        'category' => 'refund_update',
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
            'type' => 'refund_processed',
            'title' => 'Refund Diproses',
            'body' => 'Refund sebesar Rp ' . number_format($this->payment->amount, 0, ',', '.') . ' untuk booking ' . $this->booking->booking_code . ' telah diproses.',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Konfirmasi Refund - ' . $this->booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Refund untuk booking Anda telah berhasil diproses.')
            ->line(' ')
            ->line('Detail Refund:')
            ->line('Kode Booking: ' . $this->booking->booking_code)
            ->line('Layanan: ' . $this->booking->service->name)
            ->line('Cabang: ' . $this->booking->branch->name)
            ->line('Jumlah Refund: Rp ' . number_format($this->payment->amount, 0, ',', '.'))
            ->line('Metode Pembayaran: ' . ucfirst($this->payment->method))
            ->line('Waktu Refund: ' . now()->format('d/m/Y H:i'))
            ->line(' ')
            ->line('Dana akan kembali ke rekening/ewallet Anda dalam 1-7 hari kerja tergantung kebijakan bank/pembayaran.')
            ->action('Lihat Detail', route('customer.booking.show', $this->booking->id))
            ->salutation('Salam, Tim CuciKuy');
    }
}