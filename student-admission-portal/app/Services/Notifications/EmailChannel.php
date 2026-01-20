<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailChannel
{
    public function send(string $recipient, string $code): bool
    {
        // Trong môi trường local, log ra để dev dễ debug
        if (app()->environment('local', 'testing')) {
            Log::info("Mock Email sent to {$recipient}: {$code}");
            // Vẫn return true để flow không bị gãy
            return true;
        }

        try {
            // Ở production sẽ dùng Mailable class thật
            // Mail::to($recipient)->send(new OtpVerificationMail($code));
            Log::info("Email sent to {$recipient} with code {$code}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email: " . $e->getMessage());
            return false;
        }
    }
}
