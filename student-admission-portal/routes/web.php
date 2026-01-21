<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApplicationFormController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::middleware(['auth', 'otp.verified'])->group(function () {
Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/applications', [DashboardController::class, 'store'])->name('application.create');

    // Application Forms
    Route::get('/application/{application}/personal', [ApplicationFormController::class, 'personal'])->name('application.personal');
    Route::post('/application/{application}/personal', [ApplicationFormController::class, 'updatePersonal'])->name('application.personal.update');
    
    Route::get('/application/{application}/parent', [ApplicationFormController::class, 'parent'])->name('application.parent');
    Route::post('/application/{application}/parent', [ApplicationFormController::class, 'updateParent'])->name('application.parent.update');

    Route::get('/application/{application}/program', [ApplicationFormController::class, 'program'])->name('application.program');
    Route::post('/application/{application}/program', [ApplicationFormController::class, 'updateProgram'])->name('application.program.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
