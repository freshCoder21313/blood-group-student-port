<?php

use App\Models\User;
use App\Models\Application;
use App\Models\Student;
use App\Models\ApiLog;
use Laravel\Sanctum\Sanctum;

test('unauthenticated user cannot access pending applications', function () {
    $response = $this->getJson('/api/v1/sync/pending');
    $response->assertStatus(401);
});

test('authenticated user with wrong ability cannot access', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['wrong:ability']);
    
    $response = $this->getJson('/api/v1/sync/pending');
    $response->assertStatus(403);
});

test('authenticated user with correct ability can fetch pending applications', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['asp:sync']);
    
    // Create submitted applications
    $app1 = Application::factory()->create(['status' => 'pending_approval']);
    $app2 = Application::factory()->create(['status' => 'draft']); // Should not be returned
    $app3 = Application::factory()->create(['status' => 'pending_approval']);
    
    $response = $this->getJson('/api/v1/sync/pending');
    
    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
    $response->assertJsonPath('data.0.status', 'submitted');
});

test('response includes decrypted pii', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['asp:sync']);
    
    $student = Student::factory()->create([
        'national_id' => '12345678',
        'passport_number' => 'A1234567'
    ]);
    
    Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'pending_approval'
    ]);
    
    $response = $this->getJson('/api/v1/sync/pending');
    
    $response->assertStatus(200);
    $response->assertJsonPath('data.0.student.national_id', '12345678');
    $response->assertJsonPath('data.0.student.passport_number', 'A1234567');
});

test('access is logged to api_logs', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['asp:sync']);
    
    $this->getJson('/api/v1/sync/pending');
    
    $this->assertDatabaseHas('api_logs', [
        'method' => 'GET',
        'endpoint' => 'api/v1/sync/pending',
        'status_code' => 200,
    ]);
});
