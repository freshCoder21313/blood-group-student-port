<?php

use App\Models\User;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can initialize application via http endpoint', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('application.create'));

    $application = Application::whereHas('student', fn($q) => $q->where('user_id', $user->id))->first();
    $response->assertRedirect(route('application.personal', $application));

    $this->assertDatabaseHas('applications', [
        'student_id' => $user->student->id, // Student created via service
        'status' => 'draft',
        'current_step' => 1,
    ]);
});

test('initializing application creates student record if missing', function () {
    $user = User::factory()->create();
    // No student record initially

    $this->actingAs($user)
        ->post(route('application.create'));

    $this->assertDatabaseHas('students', [
        'user_id' => $user->id,
    ]);
});
