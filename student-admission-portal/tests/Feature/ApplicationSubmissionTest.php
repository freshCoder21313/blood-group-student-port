<?php

use App\Events\ApplicationSubmitted;
use App\Models\Application;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('application submission success flow', function () {
    Event::fake();
    Mail::fake();

    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $program = Program::factory()->create();
    
    $application = Application::factory()->create([
        'student_id' => $student->id,
        'program_id' => $program->id,
        'status' => 'draft',
        'current_step' => 4,
        'total_steps' => 4
    ]);
    
    // Complete steps
    $application->steps()->createMany([
        ['step_number' => 1, 'step_name' => 'personal_info', 'is_completed' => true],
        ['step_number' => 2, 'step_name' => 'parent_info', 'is_completed' => true],
        ['step_number' => 3, 'step_name' => 'program_selection', 'is_completed' => true],
        ['step_number' => 4, 'step_name' => 'documents', 'is_completed' => true],
    ]);
    
    // Payment
    Payment::factory()->create([
        'application_id' => $application->id,
        'status' => 'completed',
        'amount' => 1000
    ]);
    
    // Set required data for strict validation
    $student->update([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => '2000-01-01',
        'gender' => 'Male',
        'nationality' => 'Kenyan',
        'national_id' => '12345678',
    ]);
    
    $student->parentInfo()->create([
        'guardian_name' => 'Jane Doe',
        'guardian_phone' => '0700000000',
        'relationship' => 'Parent',
    ]);
    
    $response = $this->actingAs($user)
        ->post(route('application.submit', $application));
        
    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('status', 'application-submitted');
    
    // Verify DB
    expect($application->fresh()->status)->toBe('pending_approval');
    expect($application->fresh()->submitted_at)->not->toBeNull();
    
    // Verify Event
    Event::assertDispatched(ApplicationSubmitted::class);
});

test('application submission fails with validation errors if data missing', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'draft'
    ]);
    
    // Payment ok but data missing
    Payment::factory()->create([
        'application_id' => $application->id,
        'status' => 'completed'
    ]);
    
    // Mock steps otherwise "Please complete all steps" exception
    $application->steps()->createMany([
        ['step_number' => 1, 'step_name' => 'personal_info', 'is_completed' => true],
        ['step_number' => 2, 'step_name' => 'parent_info', 'is_completed' => true],
        ['step_number' => 3, 'step_name' => 'program_selection', 'is_completed' => true],
        ['step_number' => 4, 'step_name' => 'documents', 'is_completed' => true],
    ]);

    $response = $this->actingAs($user)
        ->post(route('application.submit', $application));
        
    $response->assertSessionHasErrors();
});

test('application submission fails if payment not completed', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'draft'
    ]);
    
    // Mock steps
    $application->steps()->createMany([
        ['step_number' => 1, 'step_name' => 'personal_info', 'is_completed' => true],
        ['step_number' => 2, 'step_name' => 'parent_info', 'is_completed' => true],
        ['step_number' => 3, 'step_name' => 'program_selection', 'is_completed' => true],
        ['step_number' => 4, 'step_name' => 'documents', 'is_completed' => true],
    ]);
    
    $response = $this->actingAs($user)
        ->post(route('application.submit', $application));
        
    // Exception caught and returned as error
    $response->assertSessionHasErrors(['error']);
});

test('user cannot edit application after submission', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create([
        'student_id' => $student->id,
        'status' => 'pending_approval' // Submitted
    ]);
    
    $response = $this->actingAs($user)
        ->post(route('application.personal.update', $application), [
            'first_name' => 'New Name',
            'last_name' => 'New Name',
            'date_of_birth' => '2000-01-01',
            'gender' => 'male',
            'nationality' => 'Kenya',
            'national_id' => '12345678',
            'address' => 'Box 1',
            'city' => 'Nairobi',
            'county' => 'Nairobi',
            'postal_code' => '00100',
        ]);
        
    $response->assertForbidden();
});
