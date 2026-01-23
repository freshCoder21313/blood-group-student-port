<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Application\ApplicationService;
use App\Models\Application;
use App\Models\ApplicationStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_createDraft_sets_status_to_draft_and_defaults()
    {
        $user = User::factory()->create();
        $service = new ApplicationService();
        
        $application = $service->createDraft($user->id);
        
        expect($application->status)->toBe('draft')
            ->and($application->program_id)->toBeNull()
            ->and($application->current_step)->toBe(1)
            ->and($application->total_steps)->toBeGreaterThanOrEqual(4);
    }

    public function test_createDraft_generates_unique_application_number()
    {
        $user = User::factory()->create();
        $service = new ApplicationService();
        
        $app1 = $service->createDraft($user->id);
        $app2 = $service->createDraft(User::factory()->create()->id);
        
        expect($app1->application_number)->not->toBe($app2->application_number)
            ->and($app1->application_number)->toContain('APP-' . date('Y'));
    }

    public function test_saveStep_correctly_updates_step_JSON_and_students_table()
    {
        $user = User::factory()->create();
        $service = new ApplicationService();
        $application = $service->createDraft($user->id);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'nationality' => 'Kenya'
        ];

        // Save Step 1 (Personal Info)
        $service->saveStep($application, 1, $data);

        // Verify JSON in application_steps
        $step = ApplicationStep::where('application_id', $application->id)
                               ->where('step_number', 1)
                               ->first();
        
        expect($step->data)->toMatchArray($data)
            ->and($step->is_completed)->toBeTrue();

        // Verify Data Sync to students table
        $student = $application->student->fresh();
        expect($student->first_name)->toBe('John')
            ->and($student->last_name)->toBe('Doe')
            ->and($student->gender)->toBe('male');
    }

    public function test_saveStep_updates_parent_info_table_for_step_2()
    {
        $user = User::factory()->create();
        $service = new ApplicationService();
        $application = $service->createDraft($user->id);

        $data = [
            'guardian_name' => 'Jane Doe',
            'guardian_phone' => '0700000000',
            'relationship' => 'Mother'
        ];

        // Save Step 2 (Parent Info)
        $service->saveStep($application, 2, $data);

        // Verify Sync
        // We need to refresh the student relation first to get the parent info
        $application->load('student.parentInfo');
        $parentInfo = $application->student->parentInfo;
        
        expect($parentInfo)->not->toBeNull()
            ->and($parentInfo->guardian_name)->toBe('Jane Doe')
            ->and($parentInfo->guardian_phone)->toBe('0700000000');
    }
}
