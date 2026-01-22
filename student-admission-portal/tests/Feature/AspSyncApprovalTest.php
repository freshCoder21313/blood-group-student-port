<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AspSyncApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_asp_sync_and_approval_flow()
    {
        // Setup admin user for token generation (if needed by command)
        $admin = User::factory()->create(['role' => 'admin']);

        // Setup pending application
        $user = User::factory()->create();

        // Ensure student record exists
        if (!$user->student) {
            $user->student()->create();
            $user->refresh();
        }

        $application = Application::factory()->create([
            'student_id' => $user->student->id,
            'status' => 'pending_approval',
        ]);

        // 2.1.1 Simulate ASP: Token generation
        // Normally done via command, but for API test we can use Sanctum::actingAs or just create a token
        $token = $user->createToken('test-token')->plainTextToken;

        // 2.1.2 Sync Pending
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/sync/pending');

        // Note: The actual endpoint might be different, checking routes...
        // Routes say: Route::get('/api/v1/sync/pending', [AspSyncController::class, 'pending']);

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $application->id]);

        // 2.2.1 Approve Application
        $approveData = [
            'application_id' => $application->id,
            'status' => 'approved',
            'student_code' => 'STUDENT-2026-001',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/sync/status', $approveData);

        $response->assertStatus(200);

        // 2.2.2 Check database
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'approved',
            'student_code' => 'STUDENT-2026-001',
        ]);
    }
}
