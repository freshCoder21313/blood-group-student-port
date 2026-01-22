<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\AcademicBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_wizard_steps()
    {
        // Setup initial data
        $user = User::factory()->create(['status' => 'new']);
        $this->actingAs($user);

        // 1.2.1 Click "Apply Now" (Create Application)
        $response = $this->post(route('application.create'));
        $response->assertStatus(302); // Redirect to wizard

        $application = $user->student->application;
        $this->assertNotNull($application);

        // 1.2.2 Step 1: Personal Info
        $personalData = [
            'first_name' => 'Minh',
            'last_name' => 'Nguyen',
            'date_of_birth' => '2005-01-01',
            'gender' => 'male',
            'nationality' => 'Vietnamese',
            'national_id' => '123456789',
            'address' => '123 Le Loi',
            'city' => 'Ho Chi Minh',
            'country' => 'Vietnam',
        ];

        $response = $this->post(route('application.wizard.save', ['application' => $application->id, 'step' => 1]), $personalData);
        $response->assertRedirect();

        // 1.2.3 Step 2: Parent Info
        $parentData = [
            'guardian_name' => 'Parent Name',
            'guardian_phone' => '0987654321',
            'guardian_email' => 'parent@example.com',
            'relationship' => 'Father',
        ];

        $response = $this->post(route('application.wizard.save', ['application' => $application->id, 'step' => 2]), $parentData);
        $response->assertRedirect();

        // 1.2.4 Step 3: Program Selection
        $program = Program::factory()->create();
        $academicBlock = AcademicBlock::factory()->create();

        $programData = [
            'program_id' => $program->id,
            'academic_block_id' => $academicBlock->id,
        ];

        $response = $this->post(route('application.wizard.save', ['application' => $application->id, 'step' => 3]), $programData);
        $response->assertRedirect();

        // 1.2.5 Step 4: Document Upload
        // Note: Actual file upload testing might need more setup with Storage::fake()
        // For basic flow, we check if we can reach the step
        $response = $this->get(route('application.wizard', ['application' => $application->id]));
        $response->assertStatus(200);
    }
}
