<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpVerificationMail;

class EmailChannel
{
    public function send(string $recipient, string $code): bool
    {
        // In local environment, log for easy debugging
        if (app()->environment('local', 'testing')) {
            Log::info("Mock Email sent to {$recipient}: {$code}");
        }

        try {
            // In production (and local if configured), use real Mailable class
            Mail::to($recipient)->send(new OtpVerificationMail($code));
            Log::info("Email sent to {$recipient} with code {$code}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email: " . $e->getMessage());
            // In local/testing, we don't want to fail if mail config is missing
            if (app()->environment('local', 'testing')) {
                return true;
            }
            return false;
        }
    }
}
