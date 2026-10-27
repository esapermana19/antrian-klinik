<?php

use App\Http\Controllers\MedicationCategoryController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PracticeScheduleController;
use App\Http\Controllers\QueueController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('practice-schedule', PracticeScheduleController::class)->except(['create', 'edit', 'show']);
    Route::resource('medication-category', MedicationCategoryController::class)->except('create', 'edit', 'show');
    Route::resource('medication', MedicationController::class)->except('create', 'edit', 'show');
    Route::resource('patient', PatientController::class)->except('create', 'edit');
    Route::resource('queue', QueueController::class)->only('index', 'store');
    Route::post('queue/lock', [QueueController::class, 'lockSLot'])->name('queue.lock');
    Route::post('queue/unlock', [QueueController::class, 'unlockSLot'])->name('queue.unlock');
    Route::patch('/queues/{queue}/status', [QueueController::class, 'updateStatus'])->name('queue.status');
});

require __DIR__.'/settings.php';
