<?php

use App\Models\User;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->apiKey = 'test-api-key';
    $this->apiSecret = 'test-api-secret';

    Config::set('services.asp.api_key', $this->apiKey);
    Config::set('services.asp.api_secret', $this->apiSecret);
    Config::set('services.student_info.driver', 'database');
});

test('guest or invalid signature cannot push student academic records', function () {
    $response = $this->postJson('/api/v1/students/STU123/academic-records', [
        'grades' => [['code' => 'MATH101', 'name' => 'Math', 'grade' => 'A']],
    ]);

    $response->assertStatus(401);
});

test('asp system can push student academic records with valid hmac signature', function () {
    $studentCode = 'STU123';
    $payload = [
        'grades' => [
            ['code' => 'MATH101', 'name' => 'Mathematics', 'grade' => 'A'],
            ['code' => 'CS101', 'name' => 'Computer Science', 'grade' => 'A+']
        ],
        'schedule' => [
            ['day' => 'Monday', 'time' => '09:00 - 11:00', 'course' => 'Mathematics', 'venue' => 'Lab 1']
        ],
        'fees' => [
            'balance' => 500.0,
            'currency' => 'KES',
            'status' => 'Partial',
            'invoice_history' => []
        ]
    ];

    $timestamp = time();
    $body = json_encode($payload);
    $signature = hash_hmac('sha256', $body . $timestamp, $this->apiSecret);

    $response = $this->withHeaders([
        'X-API-Key' => $this->apiKey,
        'X-Timestamp' => (string) $timestamp,
        'X-Signature' => $signature,
    ])->postJson("/api/v1/students/{$studentCode}/academic-records", $payload);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Academic records updated successfully',
            'student_code' => $studentCode
        ]);

    $this->assertDatabaseHas('student_academic_records', [
        'student_code' => $studentCode,
    ]);

    $record = StudentAcademicRecord::where('student_code', $studentCode)->first();
    expect($record->grades)->toHaveCount(2);
    expect($record->schedule)->toHaveCount(1);
    expect($record->fees['balance'])->toEqual(500.0);
});

test('student dashboard pages pull from database cache when using database driver', function () {
    // 1. Create a user and student
    $user = User::factory()->create(['role' => 'student']);
    $student = Student::factory()->create([
        'user_id' => $user->id,
        'student_code' => 'STU999'
    ]);

    // 2. Pre-seed academic records cache
    StudentAcademicRecord::create([
        'student_code' => 'STU999',
        'grades' => [['code' => 'CS301', 'name' => 'Database Systems', 'grade' => 'A']],
        'schedule' => [['day' => 'Wednesday', 'time' => '14:00 - 16:00', 'course' => 'Database Systems', 'venue' => 'Hall C']],
        'fees' => [
            'balance' => 0.0,
            'currency' => 'KES',
            'status' => 'Paid',
            'invoice_history' => []
        ]
    ]);

    // 3. Act as student and hit grades page
    $response = $this->actingAs($user)
        ->get('/student/grades');
    $response->assertOk()
        ->assertViewHas('grades');

    // 4. Hit schedule page
    $response = $this->actingAs($user)
        ->get('/student/schedule');
    $response->assertOk()
        ->assertViewHas('schedule');

    // 5. Hit fees page
    $response = $this->actingAs($user)
        ->get('/student/fees');
    $response->assertOk()
        ->assertViewHas('fees');
});
