<?php

declare(strict_types=1);

use App\Events\ApplicationSubmitted;
use App\Models\Application;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\Application\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('submit dispatches ApplicationSubmitted event', function () {
    Event::fake();
    
    $user = User::factory()->create();
    $service = new ApplicationService();
    $application = $service->createDraft($user->id);
    
    // Mock steps completion
    $application->steps()->update(['is_completed' => true]);
    
    // Mock payment
    Payment::factory()->create([
        'application_id' => $application->id,
        'status' => 'completed',
        'amount' => 1000
    ]);

    // Setup required data for validation
    $program = \App\Models\Program::factory()->create();
    $application->update(['program_id' => $program->id]);

    $application->student->update([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => '2000-01-01',
        'gender' => 'Male',
        'nationality' => 'Kenyan',
        'national_id' => '12345678',
    ]);
    
    $application->student->parentInfo()->create([
        'guardian_name' => 'Jane Doe',
        'guardian_phone' => '0700000000',
        'relationship' => 'Parent',
    ]);
    
    $service->submit($application);
    
    Event::assertDispatched(ApplicationSubmitted::class);
});
