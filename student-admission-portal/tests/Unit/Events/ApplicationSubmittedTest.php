<?php

declare(strict_types=1);

use App\Events\ApplicationSubmitted;
use App\Models\Application;

test('application submitted event can be instantiated', function () {
    $application = new Application();
    $event = new ApplicationSubmitted($application);

    expect($event)->toBeInstanceOf(ApplicationSubmitted::class);
    expect($event->application)->toBe($application);
});
