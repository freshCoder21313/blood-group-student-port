<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Otp;
use App\Models\User;
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
    public function generate(User $user, string $purpose): Otp
    {
        // Determine channel(s) - Send to both if available to ensure delivery
        // This satisfies the "Email/SMS" requirement
        $identifier = $user->email;
        $type = 'email';

        // Check rate limit
        $this->checkRateLimit($identifier);

        // Invalidate previous OTPs for this identifier and purpose
        Otp::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->update(['verified_at' => Carbon::now()]);

        // Generate OTP
        $otpCode = $this->generateSecureOtp();

        // Create OTP record (Primary identifier is email for now)
        $otp = Otp::create([
            'user_id' => $user->id,
            'identifier' => $identifier,
            'otp_code' => $otpCode,
            'type' => $type,
            'purpose' => $purpose,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
        ]);

        // Send OTP via Email
        $this->emailChannel->send($user->email, $otpCode);

        // Send OTP via SMS if phone exists
        if (!empty($user->phone)) {
            $this->smsChannel->send($user->phone, $otpCode);
        }

        // Set cooldown
        $this->setCooldown($identifier);

        return $otp;
    }

    /**
     * Verify OTP
     */
    public function verify(string $identifier, string $code, string $purpose): array
    {
        $otp = Otp::where('identifier', $identifier)
            ->where('purpose', $purpose)
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
            // Throwing exception might be too harsh for UI, maybe return result? 
            // But strict service pattern often throws. 
            // Previous code threw Exception. I'll keep it or use a custom exception.
            // For now, keep as is.
             throw new \Exception("Please wait before requesting new OTP.");
        }
    }

    private function setCooldown(string $identifier): void
    {
        $cacheKey = "otp_cooldown:{$identifier}";
        Cache::put($cacheKey, true, self::RESEND_COOLDOWN_SECONDS);
    }
}
