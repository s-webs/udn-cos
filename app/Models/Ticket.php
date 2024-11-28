<?php

namespace App\Models;

use App\Events\TicketCreated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'category_id',
        'status',
        'assigned_table_id',
        'created_date',
        'ticket_number'
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public static function generateTicketNumber()
    {
        $today = Carbon::today();
        $lastTicket = self::whereDate('created_date', $today)
            ->orderBy('ticket_number', 'desc')
            ->first();

        return $lastTicket ? $lastTicket->ticket_number + 1 : 1;
    }

    public static function createTicket($categoryId)
    {
        $ticketNumber = self::generateTicketNumber($categoryId);

        $ticket = self::create([
            'category_id' => $categoryId,
            'status' => 'waiting',
            'created_date' => Carbon::today(),
            'ticket_number' => $ticketNumber,
        ]);

        event(new TicketCreated($ticket));

        return $ticket;
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketAssignment::class);
    }
}
