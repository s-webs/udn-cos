<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $is_open;

    public function __construct($isOpen)
    {
        $this->is_open = $isOpen;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('queue-status');
    }

    public function broadcastWith(): array
    {
        return [
            'is_open' => $this->is_open,
        ];
    }
}
