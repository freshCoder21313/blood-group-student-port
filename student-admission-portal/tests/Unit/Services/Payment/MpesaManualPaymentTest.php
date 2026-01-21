<?php

namespace Tests\Unit\Services\Payment;

use App\Models\Application;
use App\Models\Payment;
use App\Services\Payment\MpesaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MpesaManualPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_manual_payment_creates_payment_and_stores_proof()
    {
        Storage::fake('private');

        // Ensure we have a student and program for application factory
        $user = \App\Models\User::factory()->create();
        $student = \App\Models\Student::factory()->create(['user_id' => $user->id]);
        $application = Application::factory()->create(['student_id' => $student->id]);
        
        $file = UploadedFile::fake()->create('proof.pdf', 100);
        $data = [
            'transaction_code' => 'QDH1234567',
            'amount' => 1000, 
            'proof_document' => $file,
        ];

        $service = new MpesaService();
        $payment = $service->recordManualPayment($application, $data);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(Payment::STATUS_PENDING_VERIFICATION, $payment->status);
        $this->assertEquals('QDH1234567', $payment->transaction_code);
        $this->assertNotNull($payment->proof_document_path);

        Storage::disk('private')->assertExists($payment->proof_document_path);
    }
}
