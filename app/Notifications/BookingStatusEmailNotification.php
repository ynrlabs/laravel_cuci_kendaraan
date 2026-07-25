<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Booking $booking,
        public string $oldStatus,
        public string $newStatus
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot', 'vehicle', 'payment']);
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
        $subject = $this->getSubject();
        $greeting = 'Halo ' . $notifiable->name . ',';
        $body = $this->getEmailBody();
        
        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($body)
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
        
        if ($this->newStatus === 'cancelled' && $this->booking->cancel_reason) {
            $mail->line('Alasan Pembatalan: ' . $this->booking->cancel_reason);
        }
        
        $actionUrl = $this->getActionUrl();
        if ($actionUrl) {
            $mail->action('Lihat Detail Booking', $actionUrl);
        }
        
        return $mail
            ->line('Terima kasih telah menggunakan CuciKuy!')
            ->salutation('Salam, Tim CuciKuy');
    }

    /**
     * Get email subject based on status
     */
    protected function getSubject(): string
    {
        return match($this->newStatus) {
            'confirmed' => 'Booking Dikonfirmasi - ' . $this->booking->booking_code,
            'in_progress' => 'Layanan Dimulai - ' . $this->booking->booking_code,
            'completed' => 'Layanan Selesai - ' . $this->booking->booking_code,
            'cancelled' => 'Booking Dibatalkan - ' . $this->booking->booking_code,
            'no_show' => 'Tidak Hadir - ' . $this->booking->booking_code,
            'waiting_payment' => 'Menunggu Pembayaran - ' . $this->booking->booking_code,
            default => 'Status Booking Diperbarui - ' . $this->booking->booking_code,
        };
    }

    /**
     * Get email body based on status
     */
    protected function getEmailBody(): string
    {
        return match($this->newStatus) {
            'confirmed' => 'Booking Anda (' . $this->booking->booking_code . ') telah dikonfirmasi oleh kasir. Silakan datang tepat waktu pada jadwal yang telah ditentukan.',
            'in_progress' => 'Layanan untuk booking ' . $this->booking->booking_code . ' sedang diproses. Tim kami sedang menangani kendaraan Anda.',
            'completed' => 'Layanan untuk booking ' . $this->booking->booking_code . ' telah selesai. Terima kasih telah menggunakan layanan CuciKuy! Silakan berikan ulasan Anda.',
            'cancelled' => 'Booking Anda (' . $this->booking->booking_code . ') telah dibatalkan. ' . ($this->booking->cancel_reason ? 'Alasan: ' . $this->booking->cancel_reason : ''),
            'no_show' => 'Anda tidak hadir untuk booking ' . $this->booking->booking_code . '. Booking ini ditandai sebagai no-show.',
            'waiting_payment' => 'Booking Anda (' . $this->booking->booking_code . ') menunggu pembayaran. Silakan selesaikan pembayaran sebelum batas waktu.',
            default => 'Status booking ' . $this->booking->booking_code . ' berubah dari ' . $this->oldStatus . ' menjadi ' . $this->newStatus . '.',
        };
    }

    /**
     * Get action URL based on status
     */
    protected function getActionUrl(): ?string
    {
        return route('customer.booking.show', $this->booking->id);
    }
}