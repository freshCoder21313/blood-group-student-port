<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualPaymentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_manual_payment()
    {
        Storage::fake('private');
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $application = Application::factory()->create(['student_id' => $student->id]);
        
        // Ensure application has a fee amount if needed? 
        // We might need to mock program fee or ensure default payment is created first?
        // But MpesaService logic creates one if missing.
        
        $file = UploadedFile::fake()->create('proof.jpg', 100);

        $response = $this->actingAs($user)
            ->post(route('payment.manual.store', $application), [
                'transaction_code' => 'QDH1234567',
                'proof_document' => $file,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('payments', [
            'application_id' => $application->id,
            'transaction_code' => 'QDH1234567',
            'status' => Payment::STATUS_PENDING_VERIFICATION,
            'manual_submission' => true,
        ]);
        
        // Verify file stored?
        $payment = Payment::where('transaction_code', 'QDH1234567')->first();
        Storage::disk('private')->assertExists($payment->proof_document_path);
        $this->assertTrue($payment->manual_submission);
    }
    
    public function test_manual_payment_validation()
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $application = Application::factory()->create(['student_id' => $student->id]);

        $response = $this->actingAs($user)
            ->post(route('payment.manual.store', $application), [
                'transaction_code' => 'invalid', // too short, lowercase
                'proof_document' => 'not-a-file',
            ]);

        $response->assertSessionHasErrors(['transaction_code', 'proof_document']);
    }
}
