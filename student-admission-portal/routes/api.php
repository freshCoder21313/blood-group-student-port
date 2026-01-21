<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\StatusController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes for ASP.NET System
|--------------------------------------------------------------------------
*/

// ═══════════════════════════════════════════════════════════════
// ASP SYSTEM AUTHENTICATION (SANCTUM)
// ═══════════════════════════════════════════════════════════════

Route::prefix('v1')->middleware(['api'])->group(function () { 

    Route::middleware(['auth:sanctum', 'ability:asp:sync'])->prefix('asp')->group(function () {
        Route::get('/ping', [\App\Http\Controllers\Api\V1\AspSyncController::class, 'ping']);
    });

    Route::middleware(\App\Http\Middleware\ApiAuthentication::class)->group(function () {
        
        // ═══════════════════════════════════════════════════════════════
        // STUDENTS - Get list of students/applications
        // ═══════════════════════════════════════════════════════════════
        
        // GET /api/v1/students?status=pending_approval&page=1&per_page=50
        Route::get('/students', [StudentController::class, 'index'])
            ->name('api.students.index');
        
        // GET /api/v1/students/{id}
        Route::get('/students/{id}', [StudentController::class, 'show'])
            ->name('api.students.show');
        
        // GET /api/v1/students/{id}/documents
        Route::get('/students/{id}/documents', [DocumentController::class, 'index'])
            ->name('api.students.documents');
        
        // GET /api/v1/documents/{id}/download
        Route::get('/documents/{id}/download', [DocumentController::class, 'download'])
            ->name('api.documents.download');
        
        // ═══════════════════════════════════════════════════════════════
        // STATUS UPDATE - Update status from ASP
        // ═══════════════════════════════════════════════════════════════
        
        // POST /api/v1/update-status
        Route::post('/update-status', [StatusController::class, 'update'])
            ->name('api.status.update');
        
        // POST /api/v1/bulk-update-status
        Route::post('/bulk-update-status', [StatusController::class, 'bulkUpdate'])
            ->name('api.status.bulk-update');
        
        // ═══════════════════════════════════════════════════════════════
        // STUDENT DATA - Data for Student Portal
        // ═══════════════════════════════════════════════════════════════
        
        // GET /api/v1/students/{student_code}/grades
        Route::get('/students/{student_code}/grades', [StudentController::class, 'grades'])
            ->name('api.students.grades');
        
        // GET /api/v1/students/{student_code}/timetable
        Route::get('/students/{student_code}/timetable', [StudentController::class, 'timetable'])
            ->name('api.students.timetable');
        
        // GET /api/v1/students/{student_code}/fees
        Route::get('/students/{student_code}/fees', [StudentController::class, 'fees'])
            ->name('api.students.fees');
        
        // ═══════════════════════════════════════════════════════════════
        // WEBHOOKS - Receive notifications from ASP
        // ═══════════════════════════════════════════════════════════════
        
        Route::post('/webhooks/status-changed', [WebhookController::class, 'statusChanged'])
            ->name('api.webhooks.status');
        
        Route::post('/webhooks/grade-updated', [WebhookController::class, 'gradeUpdated'])
            ->name('api.webhooks.grade');
    });
});


// Include Legacy Auth Routes (formerly routes/auth.php)
require __DIR__ . '/api_auth.php';

// M-Pesa Callback (Public) - For Web Portal
Route::post('/payment/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])
    ->name('payment.callback')
    ->middleware(\App\Http\Middleware\VerifyMpesaIp::class);



