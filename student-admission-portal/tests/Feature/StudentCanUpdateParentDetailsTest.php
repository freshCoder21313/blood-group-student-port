<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Student;
use App\Models\User;
use App\Services\Application\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StudentCanUpdateParentDetailsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function student_can_update_parent_details()
    {
        $user = User::factory()->create();
        $service = app(ApplicationService::class);
        $application = $service->createDraft($user->id);
        
        // Advance to step 2 manually or logic allows update regardless?
        // Service updates step if stepNumber >= current_step.
        // So we can update step 2 directly.
        $student = $application->student;

        $data = [
            'guardian_name' => 'Guardian Test',
            'relationship' => 'Father',
            'guardian_phone' => '0712345678',
            'guardian_email' => 'parent@test.com',
            'action' => 'save'
        ];

        $response = $this->actingAs($user)
            ->from(route('application.wizard', $application))
            ->post(route('application.wizard.save', ['application' => $application, 'step' => 2]), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('application.wizard', $application)); // Stays on page with status (or redirect back)

        $this->assertDatabaseHas('parent_info', [
            'student_id' => $student->id,
            'guardian_name' => 'Guardian Test',
            'relationship' => 'Father',
        ]);
    }
}
