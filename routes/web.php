<?php

use App\Http\Controllers\PracticeScheduleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('practice-schedules', PracticeScheduleController::class)->except(['create', 'edit', 'show']);
});

require __DIR__.'/settings.php';
