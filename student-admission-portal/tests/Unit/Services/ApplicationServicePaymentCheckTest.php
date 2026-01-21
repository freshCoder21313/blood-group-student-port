<?php

use App\Models\Application;
use App\Models\Payment;
use App\Services\Application\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Student;

uses(TestCase::class, RefreshDatabase::class);

test('submit throws exception if payment not completed', function () {
    $service = new ApplicationService();
    
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    // Add required data for strict validation
    $program = \App\Models\Program::factory()->create();
    $application->update(['program_id' => $program->id]);
    
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
    
    // Create completed steps
    foreach (range(1, 4) as $step) {
        $application->steps()->create([
            'step_number' => $step,
            'step_name' => 'step_' . $step,
            'is_completed' => true
        ]);
    }
    
    // Case 1: No payment
    expect(fn() => $service->submit($application))->toThrow(\Exception::class, "Please submit payment proof");
    
    // Case 2: Pending payment
    $payment = Payment::factory()->create(['application_id' => $application->id, 'status' => 'pending']);
    expect(fn() => $service->submit($application->refresh()))->toThrow(\Exception::class, "Please submit payment proof");
    
    // Case 3: Completed payment (M-Pesa)
    $payment->update(['status' => 'completed']);
    $service->submit($application->refresh());
    expect($application->refresh()->status)->toBe('pending_approval');
});
