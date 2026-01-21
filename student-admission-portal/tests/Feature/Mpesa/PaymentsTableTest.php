<?php

use Illuminate\Support\Facades\Schema;

test('payments table has correct schema', function () {
    expect(Schema::hasTable('payments'))->toBeTrue();

    $columns = [
        'id',
        'application_id',
        'transaction_code',
        'phone_number',
        'amount',
        'status',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'result_desc',
        'created_at',
        'updated_at',
    ];

    expect(Schema::hasColumns('payments', $columns))->toBeTrue();
});
