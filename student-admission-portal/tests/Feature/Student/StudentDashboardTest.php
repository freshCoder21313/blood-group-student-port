<?php

namespace Tests\Feature\Student;

use App\Models\Application;
use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the student dashboard for approved students', function () {
    $user = User::factory()->create();
    
    // Create a student record for the user (Application usually creates this)
    $student = Student::factory()->create(['user_id' => $user->id, 'student_code' => 'STD-123']);
    
    // Create an approved application for this student/user
    Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'approved',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewIs('student.dashboard');
    $response->assertSee('Welcome, ' . $student->first_name);
    $response->assertSee('Student ID:');
    $response->assertSee('STD-123');
});

it('shows the applicant dashboard for non-approved students', function () {
    $user = User::factory()->create();
    
    // Create student record
    $student = Student::factory()->create(['user_id' => $user->id]);
    
    // Create pending application
    Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'pending_approval',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewIs('dashboard'); // The default applicant dashboard
});

it('shows the applicant dashboard for users with no application', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewIs('dashboard');
});

it('shows the student dashboard for approved students even if they have a new draft application', function () {
    $user = User::factory()->create();
    
    // Create student record for the user with student code
    $student = Student::factory()->create(['user_id' => $user->id, 'student_code' => 'STD-123']);
    
    // Create an APPROVED application (from previous year/program)
    Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'approved',
        'created_at' => now()->subYear(), // Older
    ]);

    // Create a NEW DRAFT application (latest)
    Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'draft',
        'created_at' => now(), // Newer
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewIs('student.dashboard');
    $response->assertSee('Welcome, ' . $student->first_name);
    $response->assertSee('Student ID:');
    $response->assertSee('STD-123');
});
