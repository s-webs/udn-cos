<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tickets')
        ];
    }
//
//    public function broadcastAs(): string
//    {
//        return 'TicketCreated';
//    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->ticket->id,
            'category_id' => $this->ticket->category_id,
            'status' => $this->ticket->status,
            'assigned_table_id' => $this->ticket->assigned_table_id,
            'created_date' => $this->ticket->created_date,
            'ticket_number' => $this->ticket->ticket_number,
            'category' => [
                'id' => $this->ticket->category->id,
                'name_ru' => $this->ticket->category->name_ru,
                'name_kz' => $this->ticket->category->name_kz,
                'name_en' => $this->ticket->category->name_en,
            ],
        ];
    }

}
