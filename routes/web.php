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


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('category', [\App\Http\Controllers\AdminController::class, 'category'])->name('category');
    Route::post('category', [\App\Http\Controllers\AdminController::class, 'addCategory'])->name('addCategory');
    Route::patch('category/{id}/update', [\App\Http\Controllers\AdminController::class, 'updateCategory'])->name('updateCategory');
    Route::delete('category/{id}/delete', [\App\Http\Controllers\AdminController::class, 'deleteCategory'])->name('deleteCategory');
    Route::get('tables', [\App\Http\Controllers\AdminController::class, 'tables'])->name('tables');
    Route::post('tables', [\App\Http\Controllers\AdminController::class, 'createTable'])->name('createTable');
    Route::delete('tables/{id}/delete', [\App\Http\Controllers\AdminController::class, 'deleteTable'])->name('deleteTable');
    Route::patch('table/{id}/update', [\App\Http\Controllers\AdminController::class, 'updateTable'])->name('updateTable');
    Route::post('table/{id}/assign', [\App\Http\Controllers\AdminController::class, 'assignTable'])->name('tables.assign');
    Route::post('table/unAssignTable', [\App\Http\Controllers\AdminController::class, 'unAssignTable'])->name('tables.unAssign');

    Route::post('/tickets/{id}/complete', [\App\Http\Controllers\TicketController::class, 'completeTicket'])->name('tickets.complete');
    Route::get('/tickets/current', [\App\Http\Controllers\TicketController::class, 'getCurrentTicket'])->name('tickets.current');
    Route::post('/tickets/assign', [\App\Http\Controllers\TicketController::class, 'assignNextTicket'])->name('tickets.assign');
    Route::post('/tickets/{id}/skip', [\App\Http\Controllers\TicketController::class, 'skipTicket'])->name('tickets.skip');
    Route::post('/tickets/{id}/reject', [\App\Http\Controllers\TicketController::class, 'rejectTicket'])->name('tickets.reject');
});
