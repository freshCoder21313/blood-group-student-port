<?php

declare(strict_types=1);

use App\Listeners\SendSubmissionConfirmation;

test('send submission confirmation listener exists', function () {
    $listener = new SendSubmissionConfirmation;
    expect($listener)->toBeInstanceOf(SendSubmissionConfirmation::class);
    expect(method_exists($listener, 'handle'))->toBeTrue();
});
