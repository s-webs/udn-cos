<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function terminal()
    {
        $categories = Category::all();
        return Inertia::render('Guest/Terminal', compact('categories'));
    }

    public function monitoring()
    {
        $tickets = Ticket::with(['assignments' => function($query) {
            $query->where('status', 'processing')->with('table');
        }])
            ->where('status', 'processing')
            ->get();

        // Добавляем поле processing_started_at для каждого талона
        $tickets->each(function($ticket) {
            $currentAssignment = $ticket->assignments->first();
            $ticket->processing_started_at = $currentAssignment ? $currentAssignment->created_at->toISOString() : null;
        });

        return Inertia::render('Guest/Monitoring', compact('tickets'));
    }

    public function mobileCategories()
    {
        $categories = Category::all();
        return Inertia::render('Guest/MobileCategories', compact('categories'));
    }

    public function fetchTicket($ticketId)
    {
        $ticket = Ticket::with('assignments.table', 'category')->findOrFail($ticketId);
        return response()->json(['ticket' => $ticket]);
    }

}
