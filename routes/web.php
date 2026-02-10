<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('patients', PatientController::class)->only([
        'index',
        'show',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
    ]);

    Route::get('patients/{patient}/medical-records/create', [MedicalRecordController::class, 'create'])
        ->name('patients.medical-records.create');
    Route::post('patients/{patient}/medical-records', [MedicalRecordController::class, 'store'])
        ->name('patients.medical-records.store');
});

require __DIR__.'/auth.php';
