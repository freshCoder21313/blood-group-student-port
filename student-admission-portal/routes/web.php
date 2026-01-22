<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\ApplicationWizardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::middleware(['auth', 'otp.verified'])->group(function () {
Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/applications', [DashboardController::class, 'store'])->name('application.create');

    // Application Forms (Original routes kept for backward compatibility if needed, but we prefer wizard)
    // Route::get('/application/{application}/personal', [ApplicationFormController::class, 'personal'])->name('application.personal');
    // ...

    // New Wizard Routes
    Route::get('/application/{application}/wizard', [ApplicationWizardController::class, 'show'])->name('application.wizard');
    Route::post('/application/{application}/wizard/{step}', [ApplicationWizardController::class, 'save'])->name('application.wizard.save');
    
    // Redirect old routes to wizard for seamless UX
    Route::get('/application/{application}/personal', function ($application) {
        return redirect()->route('application.wizard', ['application' => $application])->withFragment('#step-1');
    })->name('application.personal');
    
    Route::get('/application/{application}/parent', function ($application) {
        return redirect()->route('application.wizard', ['application' => $application])->withFragment('#step-2');
    })->name('application.parent');
    
    Route::get('/application/{application}/program', function ($application) {
        return redirect()->route('application.wizard', ['application' => $application])->withFragment('#step-3');
    })->name('application.program');

    Route::get('/application/{application}/documents', function ($application) {
        return redirect()->route('application.wizard', ['application' => $application])->withFragment('#step-4');
    })->name('application.documents');

    Route::get('/application/{application}/payment', [ApplicationFormController::class, 'payment'])->name('application.payment');
    Route::post('/application/{application}/submit', [ApplicationFormController::class, 'submit'])->name('application.submit');

    Route::get('/documents/{document}', [DocumentController::class, 'download'])->name('documents.show');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Payment Routes
    Route::post('/payment/{application}/initiate', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payment.initiate');
    Route::post('/payment/{application}/manual', [\App\Http\Controllers\PaymentController::class, 'storeManual'])->name('payment.manual.store');
    Route::get('/payment/{application}/status', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('payment.status');
    
    // Dev only route
    if (app()->environment('local', 'testing')) {
        Route::post('/payment/{application}/simulate-callback', [\App\Http\Controllers\PaymentController::class, 'simulateCallback'])->name('payment.simulate');
    }
    Route::get('/application/{application}/letter', [AdmissionLetterController::class, 'download'])->name('application.letter');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/proof', [\App\Http\Controllers\Admin\PaymentController::class, 'downloadProof'])->name('payments.proof');
    Route::post('/payments/{payment}/approve', [\App\Http\Controllers\Admin\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');

    // Application Management
    Route::get('/applications', [\App\Http\Controllers\Admin\ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [\App\Http\Controllers\Admin\ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/approve', [\App\Http\Controllers\Admin\ApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [\App\Http\Controllers\Admin\ApplicationController::class, 'reject'])->name('applications.reject');

    // Audit Log
    Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
    Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
    Route::get('/schedule', [StudentController::class, 'schedule'])->name('schedule');
    Route::get('/fees', [StudentController::class, 'fees'])->name('fees');
});

require __DIR__.'/auth.php';
