<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'otp.verified'])
    ->name('dashboard');

Route::post('/applications', [DashboardController::class, 'store'])
    ->middleware(['auth', 'otp.verified'])
    ->name('application.create');

Route::get('/application/step/{step}', function ($step) {
    return view('application.wizard', ['step' => $step]);
})->middleware(['auth', 'otp.verified'])->name('application.step');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
