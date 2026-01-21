<?php

return [
    'env' => env('MPESA_ENV', 'sandbox'),
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'passkey' => env('MPESA_PASSKEY'),
    'shortcode' => env('MPESA_SHORTCODE'),
    'paybill' => env('MPESA_PAYBILL', '888888'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'whitelisted_ips' => [
        '196.201.214.0/24',
        '196.201.213.0/24',
        '196.201.212.0/24',
        '196.201.211.0/24',
    ],
];
