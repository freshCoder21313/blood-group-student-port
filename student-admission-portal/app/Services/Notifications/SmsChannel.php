<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'africastalking');
    }

    public function send(string $recipient, string $code): bool
    {
        // Trong môi trường local/testing, chúng ta sẽ log ra thay vì gửi thật để tiết kiệm chi phí
        if (app()->environment('local', 'testing')) {
            Log::info("Mock SMS sent to {$recipient}: {$code}");
            return true;
        }

        return match ($this->provider) {
            'twilio' => $this->sendViaTwilio($recipient, $code),
            'africastalking' => $this->sendViaAfricasTalking($recipient, $code),
            default => $this->logOnly($recipient, $code)
        };
    }

    private function logOnly(string $recipient, string $code): bool
    {
        Log::info("SMS Service (Log Only) -> To: {$recipient}, Code: {$code}");
        return true;
    }

    private function sendViaTwilio(string $recipient, string $code): bool
    {
        // Implementation placeholder for Twilio
        Log::info("Sending via Twilio to {$recipient}");
        return true;
    }

    private function sendViaAfricasTalking(string $recipient, string $code): bool
    {
        // Implementation placeholder for Africa's Talking
        Log::info("Sending via AfricasTalking to {$recipient}");
        return true;
    }
}
