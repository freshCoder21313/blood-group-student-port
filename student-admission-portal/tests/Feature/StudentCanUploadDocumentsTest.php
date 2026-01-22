<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\ApplicationStep;
use App\Models\Document;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('documents page renders', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    $this->actingAs($user);
    
    $response = $this->get(route('application.wizard', $application));
        
    $response->assertOk()
        ->assertViewIs('application.wizard')
        ->assertViewHas('documents');
});

test('documents can be uploaded and next step validated', function () {
    Storage::fake('private');
    
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    ApplicationStep::create(['application_id' => $application->id, 'step_number' => 4, 'step_name' => 'documents', 'data' => []]);
    
    $this->actingAs($user);
    
    $file1 = UploadedFile::fake()->create('id.jpg', 100);
    $file2 = UploadedFile::fake()->create('trans.pdf', 100);
    
    // Upload ID
    $response = $this->post(route('application.wizard.save', ['application' => $application, 'step' => 4]), [
        'national_id' => $file1,
        'action' => 'save'
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('documents', ['application_id' => $application->id, 'type' => 'national_id']);
    
    // Upload Transcript and Next
    $response = $this->post(route('application.wizard.save', ['application' => $application, 'step' => 4]), [
        'transcript' => $file2,
        'action' => 'finish'
    ]);
    
    $response->assertRedirect(route('application.payment', $application)); 
    $this->assertDatabaseHas('documents', ['application_id' => $application->id, 'type' => 'transcript']);
    
    $this->assertTrue(ApplicationStep::where('application_id', $application->id)->where('step_number', 4)->first()->is_completed);
});

test('upload fails with invalid file type', function () {
    Storage::fake('private');
    
    $user = User::factory()->create();
    $student = Student::factory()->create(['user_id' => $user->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    $this->actingAs($user);
    
    $file = UploadedFile::fake()->create('bad.exe', 100);
    
    $response = $this->post(route('application.wizard.save', ['application' => $application, 'step' => 4]), [
        'national_id' => $file,
        'action' => 'save'
    ]);
    
    $response->assertSessionHasErrors('national_id');
});
