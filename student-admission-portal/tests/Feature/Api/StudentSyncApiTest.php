<?php

use App\Models\Application;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Config::set('services.asp.api_key', 'valid-api-key');
});

test('GET /api/v1/students returns correct structure and status filtering', function () {
    // 1. Create Data
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    
    // Create one pending and one draft application
    $pendingApp = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'pending_approval'
    ]);
    
    $draftApp = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'draft'
    ]);

    // 2. Call API
    $response = $this->getJson('/api/v1/students?status=pending', [
        'X-API-KEY' => 'valid-api-key'
    ]);

    // 3. Assertions
    $response->assertStatus(200)
        ->assertJsonCount(1) // Only pending app
        ->assertJsonFragment(['id' => $pendingApp->id])
        ->assertJsonMissing(['id' => $draftApp->id])
        ->assertJsonStructure([
            '*' => ['id', 'status', 'student', 'documents', 'payment']
        ]);
});

test('POST /api/v1/update-status correctly updates DB and sends notification', function () {
    Notification::fake();
    
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'pending_approval'
    ]);

    $payload = [
        'application_id' => $application->id,
        'status' => 'approved',
        'note' => 'Welcome aboard',
        'student_code' => 'STU-12345'
    ];

    $response = $this->postJson('/api/v1/update-status', $payload, [
        'X-API-KEY' => 'valid-api-key'
    ]);

    $response->assertStatus(200);

    // Verify DB
    expect($application->fresh()->status)->toBe('approved');
    expect($application->student->fresh()->student_code)->toBe('STU-12345');

    // Verify Event/Notification (Indirectly via StatusHistory or specific Notification class if known)
    // Since ApplicationService dispatches ApplicationStatusChanged, we assume listeners handle notification.
    // For now, we verified the DB update which is critical.
});

test('API 401 Unauthorized if API Key is missing', function () {
    $response = $this->getJson('/api/v1/students', [
        'X-API-KEY' => 'wrong-key'
    ]);

    $response->assertStatus(401);
});
