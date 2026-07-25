<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Payment $payment,
        public Booking $booking,
        public string $message = 'Pembayaran diterima'
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("booking.{$this->booking->id}"),
            new PrivateChannel("branch.{$this->booking->branch_id}.queue"),
            new PrivateChannel("branch.{$this->booking->branch_id}.stats"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'payment.received';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'payment' => [
                'id' => $this->payment->id,
                'booking_id' => $this->payment->booking_id,
                'amount' => $this->payment->amount,
                'method' => $this->payment->method,
                'status' => $this->payment->status->value,
                'transaction_id' => $this->payment->transaction_id,
                'paid_at' => $this->payment->paid_at?->toISOString(),
                'midtrans_response' => $this->payment->midtrans_response,
            ],
            'booking' => [
                'id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'status' => $this->booking->status->value,
                'user' => [
                    'id' => $this->booking->user->id,
                    'name' => $this->booking->user->name,
                    'phone' => $this->booking->user->phone,
                    'fcm_token' => $this->booking->user->fcm_token,
                ],
                'service' => [
                    'id' => $this->booking->service->id,
                    'name' => $this->booking->service->name,
                    'duration_minutes' => $this->booking->service->duration_minutes,
                ],
                'branch' => [
                    'id' => $this->booking->branch->id,
                    'name' => $this->booking->branch->name,
                ],
                'slot' => $this->booking->slot ? [
                    'id' => $this->booking->slot->id,
                    'start_time' => $this->booking->slot->start_time?->format('H:i'),
                    'end_time' => $this->booking->slot->end_time?->format('H:i'),
                    'date' => $this->booking->slot->date?->format('Y-m-d'),
                ] : null,
            ],
            'message' => $this->message,
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return true;
    }
}