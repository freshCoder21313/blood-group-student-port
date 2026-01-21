<?php

use Tests\TestCase;

uses(TestCase::class);

test('mpesa config has required keys', function () {
    $config = config('mpesa');

    expect($config)->toBeArray();
    expect($config)->toHaveKeys([
        'consumer_key',
        'consumer_secret',
        'passkey',
        'shortcode',
        'callback_url',
        'env', // sandbox or production
    ]);
});
