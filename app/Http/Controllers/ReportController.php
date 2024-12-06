<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'period' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $dates = $this->getStartAndEndDates($request->period, $request->start_date, $request->end_date);

        $categoryStats = $this->getCategoryStatistics($dates['start_date'], $dates['end_date']);
        $userStats = $this->getUserStatistics($dates['start_date'], $dates['end_date']);

        Log::info('Период: ' . $request->period);
        Log::info('Дата начала: ' . $dates['start_date']);
        Log::info('Дата окончания: ' . $dates['end_date']);

        // Возвращаем JSON-ответ
        return response()->json([
            'categoryStats' => $categoryStats,
            'userStats' => $userStats,
        ]);
    }

    private function getStartAndEndDates($period, $startDate = null, $endDate = null)
    {
        $now = Carbon::today();

        switch ($period) {
            case 'today':
                $start = $now->copy();
                $end = $now->copy();
                break;
            case 'week':
                $start = $now->copy()->startOfWeek(Carbon::MONDAY);
                $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
                break;
            case 'month':
                $start = $now->copy()->firstOfMonth();
                $end = $now->copy()->lastOfMonth();
                break;
            case 'half_year':
                $start = $now->copy()->subMonthsNoOverflow(5)->firstOfMonth();
                $end = $now->copy()->lastOfMonth();
                break;
            case 'year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'custom':
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                break;
            default:
                $start = $now->copy();
                $end = $now->copy();
                break;
        }

        // Преобразуем в строки формата 'Y-m-d'
        $start_date = $start->toDateString();
        $end_date = $end->toDateString();

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
    }


    private function getCategoryStatistics($startDate, $endDate)
    {
        return Ticket::select('category_id', DB::raw('count(*) as total'))
            ->where('status', 'completed')
            ->whereBetween('created_date', [$startDate, $endDate])
            ->groupBy('category_id')
            ->with('category')
            ->get();
    }


    private function getUserStatistics($startDate, $endDate)
    {
        return TicketAssignment::select('user_id', DB::raw('count(*) as total'))
            ->where('status', 'completed')
            ->whereHas('ticket', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_date', [$startDate, $endDate]);
            })
            ->groupBy('user_id')
            ->with('user')
            ->get();
    }

}
