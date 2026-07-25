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

class BookingCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Booking $booking,
        public string $message = 'Booking baru dibuat'
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
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.created';
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
                'user' => [
                    'id' => $this->booking->user->id,
                    'name' => $this->booking->user->name,
                    'phone' => $this->booking->user->phone,
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
                'notes' => $this->booking->notes,
                'created_at' => $this->booking->created_at?->toISOString(),
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