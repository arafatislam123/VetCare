<?php

namespace App\Events;

use App\Models\Veterinarian;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DoctorAvailabilityEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $veterinarian;
    public $availableSlots;

    /**
     * Create a new event instance.
     *
     * @param Veterinarian $veterinarian
     * @param array $availableSlots
     */
    public function __construct(Veterinarian $veterinarian, array $availableSlots = [])
    {
        $this->veterinarian = $veterinarian;
        $this->availableSlots = $availableSlots;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('doctor-availability.' . $this->veterinarian->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'availability.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'veterinarian_id' => $this->veterinarian->id,
            'available_slots' => $this->availableSlots,
            'updated_at' => now()->toISOString(),
        ];
    }
}
