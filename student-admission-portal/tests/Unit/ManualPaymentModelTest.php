<?php

namespace Tests\Unit;

use App\Models\Payment;
use PHPUnit\Framework\TestCase;

class ManualPaymentModelTest extends TestCase
{
    public function test_payment_model_has_manual_fallback_fillables()
    {
        $payment = new Payment();
        $this->assertContains('proof_document_path', $payment->getFillable(), 'proof_document_path should be fillable');
    }

    public function test_payment_model_has_status_constants()
    {
        $this->assertTrue(defined('App\Models\Payment::STATUS_PENDING_VERIFICATION'), 'STATUS_PENDING_VERIFICATION constant missing');
        $this->assertEquals('pending_verification', Payment::STATUS_PENDING_VERIFICATION);
    }
}
