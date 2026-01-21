<?php

use Illuminate\Support\Facades\File;

test('sanctum configuration file exists', function () {
    $this->assertTrue(File::exists(config_path('sanctum.php')), 'Sanctum configuration file not found.');
});
