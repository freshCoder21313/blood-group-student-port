<?php

declare(strict_types=1);

use App\Events\ApplicationSubmitted;
use App\Listeners\SendSubmissionConfirmation;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;

test('send submission confirmation listener exists', function () {
    $listener = new SendSubmissionConfirmation();
    expect($listener)->toBeInstanceOf(SendSubmissionConfirmation::class);
    expect(method_exists($listener, 'handle'))->toBeTrue();
});
