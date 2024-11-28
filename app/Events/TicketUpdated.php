<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tickets');
    }

    public function broadcastWith(): array
    {
        $this->ticket->load('assignments.table');
        $currentAssignment = $this->ticket->assignments->where('status', 'processing')->sortByDesc('created_at')->first();

        return [
            'id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'status' => $this->ticket->status,
            'assignments' => $this->ticket->assignments()->with('table')->get(),
            'category' => $this->ticket->category,
            'processing_started_at' => $currentAssignment ? $currentAssignment->created_at->toISOString() : null,
        ];
    }
}
