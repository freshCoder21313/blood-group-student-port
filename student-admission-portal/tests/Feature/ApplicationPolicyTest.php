<?php

use App\Models\User;
use App\Models\Application;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view own application', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);

    expect($user->can('view', $application))->toBeTrue();
});

test('user cannot view others application', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $otherUser->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);

    expect($user->can('view', $application))->toBeFalse();
});

test('user can create application', function () {
    $user = User::factory()->create();
    expect($user->can('create', Application::class))->toBeTrue();
});

test('user cannot update submitted application', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'pending_approval'
    ]);

    expect($user->can('update', $application))->toBeFalse();
});
