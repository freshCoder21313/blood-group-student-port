<?php

declare(strict_types=1);

use App\Mail\SubmissionConfirmation;
use App\Models\Application;

test('submission confirmation mailable contains application', function () {
    $application = new Application();
    $mailable = new SubmissionConfirmation($application);
    
    expect($mailable->application)->toBe($application);
    expect($mailable->envelope()->subject)->toBe('Application Submitted Successfully');
});
