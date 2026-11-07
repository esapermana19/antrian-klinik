<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisplayQueueController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicationCategoryController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\MyQueueController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PracticeScheduleController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\QueueManagementController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('patient', PatientController::class)->except('create', 'edit');

    Route::resource('queue', QueueController::class)->only('index', 'store');
    Route::post('queue/lock', [QueueController::class, 'lockSLot'])->name('queue.lock');
    Route::post('queue/unlock', [QueueController::class, 'unlockSLot'])->name('queue.unlock');
    Route::patch('/queues/{queue}/status', [QueueController::class, 'updateStatus'])->name('queue.status');

    Route::get('/my-queue', [MyQueueController::class, 'index'])->name('my-queue.index');
    Route::get('/display-queue', [DisplayQueueController::class, 'index'])->name('display.index');

    Route::middleware(['role:administrator'])->group(function () {
        Route::resource('practice-schedule', PracticeScheduleController::class)->except(['create', 'edit', 'show']);
        Route::resource('medication-category', MedicationCategoryController::class)->except('create', 'edit', 'show');
        Route::resource('medication', MedicationController::class)->except('create', 'edit', 'show');
        Route::get('/queue-management', [QueueManagementController::class, 'index'])->name('queue-management.index');
        Route::patch('/queue-management/{queue}/status', [QueueManagementController::class, 'updateStatus'])->name('queue-management.status');

        Route::get('/patients/{patient}/medical-records', [PatientController::class, 'medicalRecords'])->name('patients.medical-records');

        Route::get('/medical-records/{queue}/create', [MedicalRecordController::class, 'create'])->name('medical-records.create');
        Route::post('/medical-records/{queue}', [MedicalRecordController::class, 'store'])->name('medical-records.store');
    });
});

require __DIR__.'/settings.php';
