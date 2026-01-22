<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_transformation()
    {
        // 3.1.1 Log in as admitted student
        $user = User::factory()->create(['status' => 'active']);
        if (!$user->student) {
            $user->student()->create();
            $user->refresh();
        }

        $application = Application::factory()->create([
            'student_id' => $user->student->id,
            'status' => 'approved',
            'student_code' => 'STUDENT-2026-001',
        ]);

        $this->actingAs($user);

        // 3.1.2 Verify dashboard content
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('STUDENT-2026-001');
        $response->assertSee('Grades');
        $response->assertSee('Schedule');

        // 3.2.1 View Grades
        $response = $this->get(route('student.grades'));
        $response->assertStatus(200);

        // 3.2.2 View Schedule
        $response = $this->get(route('student.schedule'));
        $response->assertStatus(200);
    }
}
