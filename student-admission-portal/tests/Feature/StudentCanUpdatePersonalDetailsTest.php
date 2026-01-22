<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Student;
use App\Models\User;
use App\Services\Application\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StudentCanUpdatePersonalDetailsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function student_can_view_personal_details_form()
    {
        $user = User::factory()->create();
        $service = app(ApplicationService::class);
        $application = $service->createDraft($user->id);

        $response = $this->actingAs($user)
            ->get(route('application.wizard', $application));

        $response->assertStatus(200);
        $response->assertViewIs('application.wizard');
    }

    #[Test]
    public function student_can_update_personal_details_draft()
    {
        $user = User::factory()->create();
        $service = app(ApplicationService::class);
        $application = $service->createDraft($user->id);
        $student = $application->student;

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'national_id' => '12345678',
            'gender' => 'male',
            'action' => 'save'
        ];

        $response = $this->actingAs($user)
            ->post(route('application.wizard.save', ['application' => $application, 'step' => 1]), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $student->refresh();
        $this->assertEquals('John', $student->first_name);
        $this->assertEquals('Doe', $student->last_name);
        // National ID is encrypted, so accessing property should decrypt it if cast is working
        $this->assertEquals('12345678', $student->national_id);
    }
    
    #[Test]
    public function it_updates_step_progress_on_save_and_next()
    {
        $user = User::factory()->create();
        $service = app(ApplicationService::class);
        $application = $service->createDraft($user->id);

        $data = [
            'first_name' => 'Jane',
            'action' => 'next'
        ];

        $response = $this->actingAs($user)
            ->post(route('application.wizard.save', ['application' => $application, 'step' => 1]), $data);
            
        // Should redirect to wizard with step-2 hash
        $response->assertRedirect(route('application.wizard', $application) . '#step-2');
        
        $application->refresh();
        $this->assertEquals(2, $application->current_step);
    }
}
