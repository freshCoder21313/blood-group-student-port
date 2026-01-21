<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCanSelectProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_program_selection_page()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $application = Application::factory()->create([
            'student_id' => $student->id,
            'status' => 'draft',
            'current_step' => 3
        ]);

        $response = $this->actingAs($user)->get(route('application.program', $application));

        $response->assertStatus(200);
        $response->assertViewIs('application.program');
        $response->assertViewHas('programs');
        $response->assertViewHas('application');
    }

    public function test_student_can_update_program_selection()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $application = Application::factory()->create([
            'student_id' => $student->id,
            'status' => 'draft',
            'current_step' => 3
        ]);
        $program = Program::factory()->create();

        $response = $this->actingAs($user)->post(route('application.program.update', $application), [
            'program_id' => $program->id,
        ]);

        $response->assertRedirect(route('dashboard')); // Or next step
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'program_id' => $program->id,
        ]);
    }

    public function test_draft_application_allows_empty_program()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $application = Application::factory()->create([
            'student_id' => $student->id,
            'status' => 'draft',
            'current_step' => 3
        ]);

        $response = $this->actingAs($user)->post(route('application.program.update', $application), [
            'program_id' => null, // Empty selection
        ]);

        $response->assertRedirect(); // Should save and redirect
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'program_id' => null,
        ]);
    }

    public function test_previously_selected_program_is_preselected()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $program = Program::factory()->create();
        $application = Application::factory()->create([
            'student_id' => $student->id,
            'status' => 'draft',
            'current_step' => 3,
            'program_id' => $program->id, // Already selected
        ]);

        $response = $this->actingAs($user)->get(route('application.program', $application));

        $response->assertStatus(200);
        $response->assertSee('value="' . $program->id . '" selected', false);
        $this->assertEquals($program->id, $application->fresh()->program_id);
    }
}
