<?php

return [
    'length' => env('OTP_LENGTH', 6),
    'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 10),
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 3),
    'resend_cooldown' => env('OTP_RESEND_COOLDOWN', 60),
];
