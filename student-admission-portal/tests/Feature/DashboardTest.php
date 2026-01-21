<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard displays apply now when no application', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertStatus(200);
    // We expect 'application' variable to be passed, even if null
    $response->assertViewHas('application', null);
    $response->assertSee('Apply Now');
});

test('dashboard displays continue when draft application exists', function () {
    $user = User::factory()->create();
    $student = \App\Models\Student::factory()->create(['user_id' => $user->id]);
    $application = \App\Models\Application::factory()->create([
         'student_id' => $student->id,
         'status' => 'draft',
         'application_number' => 'APP-TEST-002'
    ]);
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertStatus(200);
    $response->assertViewHas('application', function ($viewApp) use ($application) {
        return $viewApp->id === $application->id;
    });
    $response->assertSee('Continue Application');
});
