<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->to(route('login'));
});
Route::get('/terminal', [\App\Http\Controllers\GuestController::class, 'terminal'])->name('terminal');
Route::get('/monitoring', [\App\Http\Controllers\GuestController::class, 'monitoring'])->name('monitoring');
Route::get('/digital-queue', [\App\Http\Controllers\GuestController::class, 'mobileCategories'])->name('mobileCategories');

Route::get('/tickets/client/{id}', [\App\Http\Controllers\GuestController::class, 'fetchTicket'])->name('ticket.fetch');

Route::get('/ticket/{categoryId}/{locale}', [\App\Http\Controllers\TicketController::class, 'create'])->name('digitalTicket-create');
Route::get('/digital-ticket/{ticketId}/{locale}', [\App\Http\Controllers\TicketController::class, 'show'])->name('digitalTicket-show');
Route::get('/tickets/queue-count/{categoryId}', [\App\Http\Controllers\TicketController::class, 'queueCount'])->name('tickets.queueCount');

Route::get('/queue/status', [\App\Http\Controllers\QueueController::class, 'getQueueStatus'])->name('queue.status');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/category', [\App\Http\Controllers\AdminController::class, 'category'])->name('category');
    Route::post('/category', [\App\Http\Controllers\AdminController::class, 'addCategory'])->name('addCategory');
    Route::patch('/category/{id}/update', [\App\Http\Controllers\AdminController::class, 'updateCategory'])->name('updateCategory');
    Route::delete('/category/{id}/delete', [\App\Http\Controllers\AdminController::class, 'deleteCategory'])->name('deleteCategory');
    Route::get('/tables', [\App\Http\Controllers\AdminController::class, 'tables'])->name('tables');
    Route::post('/tables', [\App\Http\Controllers\AdminController::class, 'createTable'])->name('createTable');
    Route::delete('/tables/{id}/delete', [\App\Http\Controllers\AdminController::class, 'deleteTable'])->name('deleteTable');
    Route::patch('/table/{id}/update', [\App\Http\Controllers\AdminController::class, 'updateTable'])->name('updateTable');
    Route::post('/table/{id}/assign', [\App\Http\Controllers\AdminController::class, 'assignTable'])->name('tables.assign');
    Route::post('/table/unAssignTable', [\App\Http\Controllers\AdminController::class, 'unAssignTable'])->name('tables.unAssign');

    Route::post('/tickets/{id}/complete', [\App\Http\Controllers\TicketController::class, 'completeTicket'])->name('tickets.complete');
    Route::get('/tickets/current', [\App\Http\Controllers\TicketController::class, 'getCurrentTicket'])->name('tickets.current');
    Route::post('/tickets/assign', [\App\Http\Controllers\TicketController::class, 'assignNextTicket'])->name('tickets.assign');

    Route::get('/tickets', [\App\Http\Controllers\TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/skip/{id}', [\App\Http\Controllers\TicketController::class, 'skipTicket'])->name('tickets.skip');
    Route::post('/tickets/reject/{id}', [\App\Http\Controllers\TicketController::class, 'rejectTicket'])->name('tickets.reject');
    Route::post('/tickets/clear-queue', [\App\Http\Controllers\TicketController::class, 'clearQueue'])->name('tickets.clearQueue');

    Route::post('/queue/toggle', [\App\Http\Controllers\QueueController::class, 'toggleQueueStatus'])->name('queue.toggle');

    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('reports.generate');

    Route::get('/users', [\App\Http\Controllers\UsersController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\UsersController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [\App\Http\Controllers\UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\UsersController::class, 'destroy'])->name('users.destroy');
});


Route::get('/forgot-password', function () {
    abort(404);
});
Route::post('/forgot-password', function () {
    abort(404);
});
Route::get('/register', function () {
    abort(404);
});
Route::post('/register', function () {
    abort(404);
});
Route::get('/reset-password', function () {
    abort(404);
});
Route::post('/reset-password/{token}', function () {
    abort(404);
});

Route::get('/two-factor-challenge', function () {
    abort(404);
});

Route::post('/two-factor-challenge', function () {
    abort(404);
});

Route::delete('/user', function () {
    abort(404);
});
Route::get('user/confirm-password', function () {
    abort(404);
});
Route::post('user/confirm-password', function () {
    abort(404);
});

Route::get('/user/confirmed-password-status', function () {
    abort(404);
});
