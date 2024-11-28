<?php

namespace App\Events;

use App\Models\TicketAssignment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $assignment;

    public function __construct(TicketAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tickets');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->assignment->id,
            'ticket_id' => $this->assignment->ticket_id,
            'table_id' => $this->assignment->table_id,
            'user_id' => $this->assignment->user_id,
            'status' => $this->assignment->status,
            'ticket' => [
                'id' => $this->assignment->ticket->id,
                'ticket_number' => $this->assignment->ticket->ticket_number,
                'category' => $this->assignment->ticket->category,
            ],
        ];
    }
}
