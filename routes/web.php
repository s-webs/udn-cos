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



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
