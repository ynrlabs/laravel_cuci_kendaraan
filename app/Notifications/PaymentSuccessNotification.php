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

class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
        public Payment $payment
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
            ->title('Pembayaran Berhasil')
            ->body('Pembayaran sebesar Rp ' . number_format($this->payment->amount, 0, ',', '.') . ' untuk booking ' . $this->booking->booking_code . ' telah berhasil.')
            ->image(asset('images/notifications/payment-success.png'));
        
        $data = [
            'type' => 'payment_success',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'method' => $this->payment->method,
            'status' => $this->booking->status->value,
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
                    'color' => '#16A34A',
                    'sound' => 'default',
                ],
            ])
            ->apnsConfig([
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                        'category' => 'payment_update',
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
            'type' => 'payment_success',
            'title' => 'Pembayaran Berhasil',
            'body' => 'Pembayaran sebesar Rp ' . number_format($this->payment->amount, 0, ',', '.') . ' untuk booking ' . $this->booking->booking_code . ' telah berhasil.',
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'method' => $this->payment->method,
            'status' => $this->booking->status->value,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Konfirmasi Pembayaran Berhasil - ' . $this->booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Pembayaran Anda telah berhasil diproses.')
            ->line('Detail Pembayaran:')
            ->line('Kode Booking: ' . $this->booking->booking_code)
            ->line('Layanan: ' . $this->booking->service->name)
            ->line('Cabang: ' . $this->booking->branch->name)
            ->line('Metode Pembayaran: ' . $this->payment->method)
            ->line('Jumlah: Rp ' . number_format($this->payment->amount, 0, ',', '.'))
            ->line('Waktu Pembayaran: ' . $this->payment->paid_at?->format('d/m/Y H:i'))
            ->when($this->booking->slot, function ($mail) {
                $mail->line('Tanggal Layanan: ' . $this->booking->slot->date?->format('d/m/Y'))
                     ->line('Waktu Layanan: ' . $this->booking->slot->start_time?->format('H:i') . ' - ' . $this->booking->slot->end_time?->format('H:i'));
            })
            ->when($this->booking->vehicle, function ($mail) {
                $mail->line('Kendaraan: ' . $this->booking->vehicle->plate_number . ' (' . $this->booking->vehicle->type . ')');
            })
            ->action('Lihat Struk', route('customer.payment.show', $this->payment->id))
            ->line('Struk pembayaran juga tersedia di aplikasi CuciKuy.')
            ->salutation('Salam, Tim CuciKuy');
    }
}