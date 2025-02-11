<?php

namespace App\Http\Controllers;

use App\Events\QueueCleared;
use App\Events\TicketAssigned;
use App\Events\TicketUpdated;
use App\Models\Category;
use App\Models\Table;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $categoryIds = $request->query('category_ids', []);

        $tickets = Ticket::with('category')
            ->where('status', 'waiting')
            ->whereIn('category_id', $categoryIds)
            ->orderBy('ticket_number')
            ->get();

        return response()->json(['tickets' => $tickets]);
    }


    public function create($categoryId, $locale)
    {
        $category = Category::query()->findOrFail($categoryId);
        $ticket = Ticket::createTicket($category->id);

        return redirect()->to(route('digitalTicket-show', [$ticket->id, $locale]));
    }

    public function show($ticketId, $locale)
    {
        $ticket = Ticket::with('assignments.table')->findOrFail($ticketId);
        $category = Category::query()->findOrFail($ticket->category_id);
        return Inertia::render('Guest/Ticket', compact('ticket', 'locale', 'category'));
    }

    public function updateTicket(Request $request, $id)
    {
        $ticket = Ticket::query()->findOrFail($id);

        $ticket->update([
            'status' => $request->status,
            'assigned_table_id' => $request->assigned_table_id ?? $ticket->assigned_table_id,
        ]);

        // Отправка события
        event(new TicketUpdated($ticket));

        return response()->json(['message' => 'Ticket updated and broadcasted.', 'ticket' => $ticket]);
    }

    public function getCurrentTicket(Request $request)
    {
        $user = Auth::user();
        $table = $user->table->first();
        $tableId = $table->id;

        if (!$tableId) {
            return response()->json(['message' => 'Стол не указан.'], 400);
        }

        $assignment = TicketAssignment::query()->where('table_id', $tableId)
            ->where('user_id', $user->id)
            ->where('status', 'processing')
            ->with('ticket.category')
            ->first();

        if ($assignment) {
            return response()->json(['currentAssignment' => $assignment]);
        } else {
            return response()->json(['message' => 'Нет текущего талона.'], 404);
        }
    }

    public function completeTicket($id)
    {
        $user = Auth::user();

        // Находим назначение
        $assignment = TicketAssignment::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'processing')
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Талон не найден или уже завершен.'], 404);
        }

        // Обновляем статус назначения и талона
        $assignment->update(['status' => 'completed']);
        $assignment->ticket->update(['status' => 'completed']);

        // Транслируем событие обновления талона
        event(new TicketUpdated($assignment->ticket));

        return response()->json(['message' => 'Талон завершен.']);
    }

    public function assignNextTicket(Request $request)
    {
        $user = Auth::user();
        $tableId = $request->input('table_id');

        $table = Table::with('categories')->find($tableId);

        if (!$table) {
            return response()->json(['message' => 'Стол не найден.'], 404);
        }

        $categoryIds = $table->categories->pluck('id');

        // Завершаем текущее назначение, если есть
        $existingAssignment = TicketAssignment::query()
            ->where('table_id', $tableId)
            ->where('user_id', $user->id)
            ->where('status', 'processing')
            ->with('ticket')
            ->first();

        if ($existingAssignment) {
            $existingAssignment->update(['status' => 'completed']);
            $existingAssignment->ticket->update(['status' => 'completed']);

            // Транслируем событие обновления талона
            event(new TicketUpdated($existingAssignment->ticket));
        }

        // Назначаем следующий талон
        $ticket = Ticket::whereIn('category_id', $categoryIds)
            ->where('status', 'waiting')
            ->orderBy('created_at')
            ->first();

        if ($ticket) {
            $assignment = TicketAssignment::query()->create([
                'ticket_id' => $ticket->id,
                'table_id' => $table->id,
                'user_id' => $user->id,
                'status' => 'processing',
            ]);

            // Обновляем статус талона
            $ticket->update(['status' => 'processing']);

            // Транслируем событие обновления талона
            event(new TicketUpdated($ticket));

            // Загружаем связанные данные
            $assignment->load('ticket.category', 'table', 'user');

            return response()->json(['currentAssignment' => $assignment]);
        } else {
            return response()->json(['message' => 'Нет доступных талонов.'], 404);
        }
    }

    public function skipTicket($id)
    {
        $ticketAssignment = TicketAssignment::query()->findOrFail($id);
        $ticketAssignment->update(['status' => 'skipped']);
        $ticketAssignment->ticket->update(['status' => 'waiting']);
        $ticket = $ticketAssignment->ticket;
        $ticketAssignment->delete();

        event(new TicketUpdated($ticket));

        return response()->json(['message' => 'Талон пропущен.']);
    }


    public function rejectTicket($id)
    {
        $ticketAssignment = TicketAssignment::query()->findOrFail($id);
        $ticketAssignment->update(['status' => 'rejected']);
        $ticketAssignment->ticket->update(['status' => 'rejected']);

        event(new TicketUpdated($ticketAssignment->ticket));

        return response()->json(['message' => 'Талон отклонен.']);
    }

    public function clearQueue(Request $request)
    {
        $categoryIds = $request->input('category_ids', []);

        $tickets = Ticket::whereIn('category_id', $categoryIds)
            ->where('status', 'waiting')
            ->get();

        foreach ($tickets as $ticket) {
            $ticket->update(['status' => 'cancelled']);
            event(new TicketUpdated($ticket));
            $ticket->delete();
        }

        // Транслируем событие очистки очереди
        event(new QueueCleared($categoryIds));

        return response()->json(['message' => 'Очередь очищена.']);
    }

    public function queueCount($categoryId)
    {
        // Считаем количество тикетов со статусом waiting в этой категории
        $count = Ticket::query()->where('category_id', $categoryId)
            ->where('status', 'waiting')
            ->count();

        return response()->json(['count' => $count]);
    }


}
