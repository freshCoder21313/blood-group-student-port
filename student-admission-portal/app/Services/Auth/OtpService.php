<?php

namespace App\Services\Auth;

use App\Models\Otp;
use App\Services\Notifications\SmsChannel;
use App\Services\Notifications\EmailChannel;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OtpService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 10;
    private const MAX_ATTEMPTS = 3;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(
        private SmsChannel $smsChannel,
        private EmailChannel $emailChannel
    ) {}

    /**
     * Generate and send OTP
     */
    public function generate(string $identifier, string $type = 'email'): array
    {
        // 1. Rate limiting check
        $this->checkRateLimit($identifier);

        // 2. Invalidate previous OTPs
        Otp::where('identifier', $identifier)
            ->whereNull('verified_at')
            ->update(['verified_at' => Carbon::now()]);

        // 3. Generate secure OTP
        $otpCode = $this->generateSecureOtp();

        // 4. Store OTP
        Otp::create([
            'identifier' => $identifier,
            'otp_code' => $otpCode, // In prod, consider hashing this
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
            'purpose' => 'login'
        ]);

        // 5. Send OTP via appropriate channel
        if ($type === 'email') {
            $this->emailChannel->send($identifier, $otpCode);
        } else {
            $this->smsChannel->send($identifier, $otpCode);
        }

        // 6. Update cooldown
        $this->setCooldown($identifier);

        return [
            'success' => true,
            'message' => "OTP sent to {$type}",
            'expires_in' => self::OTP_EXPIRY_MINUTES * 60,
            'resend_available_in' => self::RESEND_COOLDOWN_SECONDS,
        ];
    }

    /**
     * Verify OTP
     */
    public function verify(string $identifier, string $code): array
    {
        $otp = Otp::where('identifier', $identifier)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'OTP expired or not found',
                'code' => 'OTP_EXPIRED'
            ];
        }

        // Check max attempts
        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->update(['verified_at' => Carbon::now()]); // Invalidate
            return [
                'success' => false,
                'message' => 'Maximum attempts exceeded',
                'code' => 'MAX_ATTEMPTS_EXCEEDED'
            ];
        }

        // Increment attempts
        $otp->increment('attempts');

        // Verify code
        if ($otp->otp_code !== $code) {
            $remainingAttempts = self::MAX_ATTEMPTS - $otp->attempts;
            return [
                'success' => false,
                'message' => "Invalid OTP. {$remainingAttempts} attempts remaining",
                'code' => 'INVALID_OTP'
            ];
        }

        // Mark as verified
        $otp->update([
            'verified_at' => Carbon::now()
        ]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
            'code' => 'VERIFIED'
        ];
    }

    private function generateSecureOtp(): string
    {
        return str_pad(
            (string) random_int(0, (10 ** self::OTP_LENGTH) - 1),
            self::OTP_LENGTH,
            '0',
            STR_PAD_LEFT
        );
    }

    private function checkRateLimit(string $identifier): void
    {
        $cacheKey = "otp_cooldown:{$identifier}";
        if (Cache::has($cacheKey)) {
            throw new \Exception("Please wait before requesting new OTP.");
        }
    }

    private function setCooldown(string $identifier): void
    {
        $cacheKey = "otp_cooldown:{$identifier}";
        Cache::put($cacheKey, true, self::RESEND_COOLDOWN_SECONDS);
    }
}
