<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Booking $booking,
        public string $oldStatus,
        public string $newStatus,
        public ?string $changedBy = null,
        public ?string $notes = null,
        public string $message = 'Status booking diperbarui'
    ) {
        $this->booking->load(['user', 'service', 'branch', 'slot', 'payment']);
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
        return 'booking.status_updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'booking' => [
                'id' => $this->booking->id,
                'booking_code' => $this->booking->booking_code,
                'status' => $this->booking->status->value,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
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
                'vehicle' => $this->booking->vehicle ? [
                    'id' => $this->booking->vehicle->id,
                    'plate_number' => $this->booking->vehicle->plate_number,
                    'type' => $this->booking->vehicle->type,
                ] : null,
                'payment' => $this->booking->payment ? [
                    'id' => $this->booking->payment->id,
                    'amount' => $this->booking->payment->amount,
                    'method' => $this->booking->payment->method,
                    'status' => $this->booking->payment->status->value,
                    'paid_at' => $this->booking->payment->paid_at?->toISOString(),
                ] : null,
                'notes' => $this->booking->notes,
                'changed_by' => $this->changedBy,
                'status_notes' => $this->notes,
                'updated_at' => $this->booking->updated_at?->toISOString(),
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