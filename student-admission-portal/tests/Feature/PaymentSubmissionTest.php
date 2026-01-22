<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PaymentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_and_submission_flow()
    {
        Storage::fake('public');

        // Setup user with application in progress
        $user = User::factory()->create(['status' => 'new']);

        // Ensure student record exists
        if (!$user->student) {
            $user->student()->create();
            $user->refresh();
        }

        $this->actingAs($user);

        // Pre-create application at payment step (Step 4 completed)
        $application = Application::factory()->create([
            'student_id' => $user->student->id,
            'status' => 'draft',
            'current_step' => 4,
            'total_steps' => 4,
        ]);

        // 1.3.1 Click "Proceed to Payment"
        $response = $this->get(route('application.payment', $application->id));
        $response->assertStatus(200);
        $response->assertSee('Payment'); // Ensure payment page is loaded

        // 1.3.2 Manual Payment
        $paymentData = [
            'transaction_code' => 'TESTABC123',
            'proof_document' => UploadedFile::fake()->create('payment_proof.jpg'),
            'payment_method' => 'manual',
        ];

        $response = $this->post(route('payment.manual.store', $application->id), $paymentData);
        $response->assertRedirect(route('application.payment', $application));

        $this->assertDatabaseHas('payments', [
            'application_id' => $application->id,
            'transaction_code' => 'TESTABC123',
            'status' => 'pending_verification',
        ]);

        // 1.3.3 Click "SUBMIT APPLICATION"
        // Ensure student has necessary data for validation
        $user->student->update([
            'first_name' => 'Minh',
            'last_name' => 'Nguyen',
            'date_of_birth' => '2005-01-01',
            'gender' => 'male',
            'nationality' => 'Vietnamese',
            'national_id' => '123456789',
        ]);
        $user->student->parentInfo()->create([
            'guardian_name' => 'Parent',
            'guardian_phone' => '0987654321',
            'relationship' => 'Father',
        ]);

        $program = \App\Models\Program::factory()->create();
        $application->update(['program_id' => $program->id]);

        $response = $this->post(route('application.submit', $application->id));
        $response->assertRedirect(route('dashboard'));

        // 1.3.5 Check database status
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'pending_approval',
        ]);
    }
}
