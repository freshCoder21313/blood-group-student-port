<?php

use App\Exceptions\PaymentFailedException;
use Tests\TestCase;

uses(TestCase::class);

test('payment failed exception is instantiable', function () {
    $e = new PaymentFailedException('Payment failed');
    expect($e)->toBeInstanceOf(\Exception::class);
    expect($e->getMessage())->toBe('Payment failed');
});
