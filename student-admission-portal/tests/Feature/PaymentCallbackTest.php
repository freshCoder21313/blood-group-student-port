<?php

use Illuminate\Support\Facades\Config;
use function Pest\Laravel\postJson;

it('blocks requests from unauthorized ips in production', function () {
    Config::set('mpesa.env', 'production');

    $response = postJson(route('payment.callback'), [], [
        'REMOTE_ADDR' => '10.0.0.1'
    ]);

    $response->assertStatus(403);
});

it('allows requests from whitelisted ips in production', function () {
    Config::set('mpesa.env', 'production');

    // 196.201.214.0/24 is whitelisted. 196.201.214.1 should be allowed.
    $response = postJson(route('payment.callback'), [], [
        'REMOTE_ADDR' => '196.201.214.1'
    ]);

    // We expect it to pass middleware. 
    // If controller fails due to empty payload, it might be 500 or 200 (if handled).
    // We just verify it's NOT 403.
    $response->assertStatus(200); 
});

it('allows requests from any ip in sandbox', function () {
    Config::set('mpesa.env', 'sandbox');

    $response = postJson(route('payment.callback'), [], [
        'REMOTE_ADDR' => '10.0.0.1'
    ]);

    $response->assertStatus(200);
});

it('updates payment to completed on success callback but leaves application as draft', function () {
    // Setup
    $application = \App\Models\Application::factory()->create([
        'status' => 'draft'
    ]);
    
    $payment = \App\Models\Payment::factory()->create([
        'application_id' => $application->id,
        'checkout_request_id' => 'ws_CO_123456789',
        'status' => 'pending'
    ]);

    // Payload
    $payload = [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => '12345',
                'CheckoutRequestID' => 'ws_CO_123456789',
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 1500.00],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'LGR7OWQ812'],
                        ['Name' => 'TransactionDate', 'Value' => 20171129110606],
                        ['Name' => 'PhoneNumber', 'Value' => 254708374149]
                    ]
                ]
            ]
        ]
    ];

    // Act
    Config::set('mpesa.env', 'sandbox'); // Bypass IP check for logic test
    $response = postJson(route('payment.callback'), $payload);

    // Assert
    $response->assertStatus(200);

    $payment->refresh();
    expect($payment->status)->toBe('completed');
    expect($payment->transaction_code)->toBe('LGR7OWQ812');
    expect($payment->mpesa_receipt_number)->toBe('LGR7OWQ812');

    $application->refresh();
    // This Expectation SHOULD FAIL before the fix
    expect($application->status)->toBe('draft');
});

it('updates payment to failed on failed callback', function () {
    // Setup
    $payment = \App\Models\Payment::factory()->create([
        'checkout_request_id' => 'ws_CO_FAIL_123',
        'status' => 'pending'
    ]);

    // Payload
    $payload = [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => '12345',
                'CheckoutRequestID' => 'ws_CO_FAIL_123',
                'ResultCode' => 1032,
                'ResultDesc' => 'Request cancelled by user',
            ]
        ]
    ];

    Config::set('mpesa.env', 'sandbox');
    postJson(route('payment.callback'), $payload)->assertStatus(200);

    $payment->refresh();
    expect($payment->status)->toBe('failed');
    expect($payment->result_desc)->toBe('Request cancelled by user');
});

it('handles duplicate callbacks idempotently', function () {
    // Setup
    $payment = \App\Models\Payment::factory()->create([
        'checkout_request_id' => 'ws_CO_DUP_123',
        'status' => 'pending'
    ]);

    $payload = [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => '12345',
                'CheckoutRequestID' => 'ws_CO_DUP_123',
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 100],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'REF123'],
                        ['Name' => 'TransactionDate', 'Value' => 20210101120000],
                        ['Name' => 'PhoneNumber', 'Value' => 254700000000]
                    ]
                ]
            ]
        ]
    ];

    Config::set('mpesa.env', 'sandbox');

    // First Call
    postJson(route('payment.callback'), $payload)->assertStatus(200);
    
    // Second Call
    postJson(route('payment.callback'), $payload)->assertStatus(200);

    // Assert only 1 payment exists (checking ID didn't change/duplicate)
    expect(\App\Models\Payment::where('checkout_request_id', 'ws_CO_DUP_123')->count())->toBe(1);
    
    $payment->refresh();
    expect($payment->status)->toBe('completed');
});

it('fails payment if success callback is missing receipt number', function () {
    $payment = \App\Models\Payment::factory()->create([
        'checkout_request_id' => 'ws_CO_MISSING_REF',
        'status' => 'pending'
    ]);

    $payload = [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => '12345',
                'CheckoutRequestID' => 'ws_CO_MISSING_REF',
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 1500.00],
                        // Missing MpesaReceiptNumber
                    ]
                ]
            ]
        ]
    ];

    Config::set('mpesa.env', 'sandbox');
    postJson(route('payment.callback'), $payload)->assertStatus(200);

    $payment->refresh();
    expect($payment->status)->toBe('failed');
    expect($payment->result_desc)->toBe('Missing Receipt Number in Success Callback');
});

it('clears manual_submission flag on success callback', function () {
    $payment = \App\Models\Payment::factory()->create([
        'checkout_request_id' => 'ws_CO_MANUAL_OVERRIDE',
        'status' => 'pending_verification',
        'manual_submission' => true
    ]);

    $payload = [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => '12345',
                'CheckoutRequestID' => 'ws_CO_MANUAL_OVERRIDE',
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 1500.00],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'REF_MANUAL_OVERRIDE'],
                    ]
                ]
            ]
        ]
    ];

    Config::set('mpesa.env', 'sandbox');
    postJson(route('payment.callback'), $payload)->assertStatus(200);

    $payment->refresh();
    expect($payment->status)->toBe('completed');
    expect($payment->manual_submission)->toBe(false); // Should be false (0)
});

