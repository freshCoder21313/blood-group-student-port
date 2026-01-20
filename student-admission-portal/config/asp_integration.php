<?php

return [
    'base_url' => env('ASP_INTEGRATION_BASE_URL', 'https://internal-asp.school.local/api'),
    'api_key' => env('ASP_INTEGRATION_API_KEY'),
    'api_secret' => env('ASP_INTEGRATION_API_SECRET'),
    'timeout' => env('ASP_INTEGRATION_TIMEOUT', 30),
    'verify_signature' => env('ASP_INTEGRATION_VERIFY_SIGNATURE', true),
    'allowed_ips' => explode(',', env('ASP_INTEGRATION_ALLOWED_IPS', '')),
];
