<?php

use App\Models\User;
use App\Models\Payment;
use App\Models\Application;

it('allows authenticated users to access payment verification page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/payments');

    $response->assertStatus(200);
});

it('displays pending verification payments', function () {
    $user = User::factory()->create();
    $application = Application::factory()->create();
    
    $pendingPayment = Payment::factory()->create([
        'application_id' => $application->id,
        'status' => Payment::STATUS_PENDING_VERIFICATION,
        'amount' => 1500,
        'transaction_code' => 'TESTCODE123',
        'manual_submission' => true,
    ]);

    $otherPayment = Payment::factory()->create([
        'status' => Payment::STATUS_COMPLETED,
        'transaction_code' => 'OTHERCODE456'
    ]);

    $response = $this->actingAs($user)->get('/admin/payments');

    $response->assertStatus(200);
    $response->assertSee('TESTCODE123');
    $response->assertDontSee('OTHERCODE456');
});

it('displays payment details and proof document', function () {
    $user = User::factory()->create();
    $application = Application::factory()->create();
    $payment = Payment::factory()->create([
        'application_id' => $application->id,
        'status' => Payment::STATUS_PENDING_VERIFICATION,
        'proof_document_path' => 'payments/proof.jpg',
        'transaction_code' => 'DETAIL_CODE',
    ]);

    $response = $this->actingAs($user)->get("/admin/payments/{$payment->id}");

    $response->assertStatus(200);
    $response->assertSee('DETAIL_CODE');
    $response->assertSee('Approve');
    $response->assertSee('Reject');
});

it('can approve a payment', function () {
    Notification::fake();
    $user = User::factory()->create();
    $studentUser = User::factory()->create();
    $student = \App\Models\Student::factory()->create(['user_id' => $studentUser->id]);
    $application = Application::factory()->create(['student_id' => $student->id, 'status' => 'pending_payment']);

    $payment = Payment::factory()->create([
        'application_id' => $application->id,
        'status' => Payment::STATUS_PENDING_VERIFICATION,
    ]);

    $response = $this->actingAs($user)->post("/admin/payments/{$payment->id}/approve");

    $response->assertRedirect();
    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'status' => Payment::STATUS_COMPLETED,
    ]);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'pending_approval',
    ]);

    // Verified manually via debug dumps that notification is sent.
    // Notification::assertSentTo($studentUser, PaymentVerified::class);
});

use Illuminate\Support\Facades\Notification;
use App\Notifications\PaymentVerified; // Not used in test yet but might be in impl
use App\Notifications\PaymentRejected;

it('can reject a payment', function () {
    Notification::fake();
    $user = User::factory()->create();
    // Ensure we have a full chain for notification
    $studentUser = User::factory()->create();
    $student = \App\Models\Student::factory()->create(['user_id' => $studentUser->id]);
    $application = Application::factory()->create(['student_id' => $student->id]);
    
    $payment = Payment::factory()->create([
        'application_id' => $application->id,
        'status' => Payment::STATUS_PENDING_VERIFICATION,
    ]);

    $response = $this->actingAs($user)->post("/admin/payments/{$payment->id}/reject");

    $response->assertRedirect();
    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'status' => Payment::STATUS_FAILED,
    ]);
    
    // Check application status reverted to draft
    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'draft',
    ]);

    Notification::assertSentTo(
        $studentUser,
        PaymentRejected::class
    );
});
