<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceiptEmailNotification extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Struk Pembayaran - ' . $this->booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Pembayaran Anda telah berhasil diproses. Berikut adalah struk pembayaran Anda:')
            ->line(' ')
            ->line('=== STRUK PEMBAYARAN ===')
            ->line('Nomor Transaksi: ' . $this->payment->transaction_id)
            ->line('Tanggal: ' . $this->payment->paid_at?->format('d/m/Y H:i'))
            ->line('Metode: ' . ucfirst($this->payment->method))
            ->line('Status: ' . ucfirst($this->payment->status->value))
            ->line(' ')
            ->line('=== DETAIL BOOKING ===')
            ->line('Kode Booking: ' . $this->booking->booking_code)
            ->line('Layanan: ' . $this->booking->service->name)
            ->line('Cabang: ' . $this->booking->branch->name)
            ->when($this->booking->slot, function ($mail) {
                $mail->line('Tanggal Layanan: ' . $this->booking->slot->date?->format('d/m/Y'))
                     ->line('Waktu: ' . $this->booking->slot->start_time?->format('H:i') . ' - ' . $this->booking->slot->end_time?->format('H:i'));
            })
            ->when($this->booking->vehicle, function ($mail) {
                $mail->line('Kendaraan: ' . $this->booking->vehicle->plate_number . ' (' . $this->booking->vehicle->type . ')');
            })
            ->line(' ')
            ->line('=== RINCIAN BIAYA ===')
            ->line('Harga Layanan: Rp ' . number_format($this->booking->service->price, 0, ',', '.'))
            ->when($this->booking->promo_id, function ($mail) {
                $mail->line('Diskon Promo: -Rp ' . number_format($this->booking->discount_amount ?? 0, 0, ',', '.'));
            })
            ->line('Pajak (' . config('app.tax_rate', 11) . '%): Rp ' . number_format($this->booking->tax_amount ?? 0, 0, ',', '.'))
            ->line('Total Bayar: Rp ' . number_format($this->payment->amount, 0, ',', '.'))
            ->line(' ')
            ->action('Lihat Struk di Aplikasi', route('customer.payment.show', $this->payment->id))
            ->line('Struk ini juga tersedia di aplikasi CuciKuy di menu Riwayat Pembayaran.')
            ->salutation('Salam, Tim CuciKuy');
    }
}