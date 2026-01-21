<?php

use App\Models\User;
use App\Models\Student;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('payment page renders correctly', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    $response = $this->actingAs($user)->get(route('application.payment', $application));
    $response->assertOk();
    $response->assertSee('Pay Now');
    $response->assertSee('Step 5: Payment');
});

test('manual payment toggle is visible', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    $response = $this->actingAs($user)->get(route('application.payment', $application));
    $response->assertSee('Problems paying? Use Paybill');
});
