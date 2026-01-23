<?php

namespace Tests\Feature\Api;

use App\Models\Application;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentSyncControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.asp.api_key', 'test-api-key');
    }

    public function test_list_pending_students()
    {
        // Create application
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $app = Application::factory()->create([
            'student_id' => $student->id,
            'status' => 'pending_approval'
        ]);

        $response = $this->getJson('/api/v1/students?status=pending', [
            'X-API-KEY' => 'test-api-key'
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $app->id);
    }

    public function test_list_pending_students_unauthorized()
    {
        $response = $this->getJson('/api/v1/students?status=pending', [
            'X-API-KEY' => 'wrong-key'
        ]);

        $response->assertStatus(401);
    }

    public function test_update_status_approved_with_code()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $app = Application::factory()->create([
            'student_id' => $student->id,
            'status' => 'pending_approval'
        ]);

        $response = $this->postJson('/api/v1/update-status', [
            'application_id' => $app->id,
            'status' => 'approved',
            'student_code' => 'STU-2024-001',
            'note' => 'Approved via API'
        ], [
            'X-API-KEY' => 'test-api-key'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('applications', [
            'id' => $app->id,
            'status' => 'approved'
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'student_code' => 'STU-2024-001'
        ]);
    }
}
