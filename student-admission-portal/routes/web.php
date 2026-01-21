<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\DocumentController;
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

    Route::get('/application/{application}/documents', [ApplicationFormController::class, 'documents'])->name('application.documents');
    Route::post('/application/{application}/documents', [ApplicationFormController::class, 'updateDocuments'])->name('application.documents.update');

    Route::get('/application/{application}/payment', [ApplicationFormController::class, 'payment'])->name('application.payment');
    Route::post('/application/{application}/submit', [ApplicationFormController::class, 'submit'])->name('application.submit');

    Route::get('/documents/{document}', [DocumentController::class, 'download'])->name('documents.show');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Payment Routes
    Route::post('/payment/{application}/initiate', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payment.initiate');
    Route::post('/payment/{application}/manual', [\App\Http\Controllers\PaymentController::class, 'storeManual'])->name('payment.manual.store');
    Route::get('/payment/{application}/status', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('payment.status');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
