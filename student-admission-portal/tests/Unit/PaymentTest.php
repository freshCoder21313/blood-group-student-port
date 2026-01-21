<?php

use App\Models\Application;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('payment belongs to application', function () {
    // We mock the relationship check to avoid needing a factory right now if it doesn't exist
    // But ideally we use factory.
    // Let's check if Payment model exists first.
    
    // If Payment model doesn't exist, this test file will fail to parse/load.
    // That matches "Write FAILING tests first".
    
    $payment = new Payment();
    expect($payment->application())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
});

test('payment has correct fillable attributes', function () {
    $payment = new Payment();
    $fillable = [
        'application_id',
        'transaction_code',
        'phone_number',
        'amount',
        'status',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'result_desc',
    ];

    expect($payment->getFillable())->toContain(...$fillable);
});
