<?php

declare(strict_types=1);

uses(Tests\TestCase::class);

test('private disk is configured', function () {
    $config = config('filesystems.disks.private');

    expect($config)->not->toBeNull()
        ->and($config['driver'])->toBe('local')
        ->and($config['root'])->toBe(storage_path('app/private'));
});
