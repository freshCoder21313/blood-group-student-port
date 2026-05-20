# KẾ HOẠCH TRIỂN KHAI PRODUCTION
## Student Admission Portal - From Mock to Production

---

## 📋 TỔNG QUAN CHUYỂN ĐỔI

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    MOCK → PRODUCTION TRANSFORMATION                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   HIỆN TẠI (Mock)                      MỤC TIÊU (Production)               │
│   ═══════════════                      ══════════════════════               │
│                                                                             │
│   ┌─────────────────┐                  ┌─────────────────┐                 │
│   │ OTP: Random     │      ───►        │ OTP: SMS/Email  │                 │
│   │ không gửi thật  │                  │ Twilio/SendGrid │                 │
│   └─────────────────┘                  └─────────────────┘                 │
│                                                                             │
│   ┌─────────────────┐                  ┌─────────────────┐                 │
│   │ Payment: Manual │      ───►        │ M-Pesa API      │                 │
│   │ verification    │                  │ Auto verify     │                 │
│   └─────────────────┘                  └─────────────────┘                 │
│                                                                             │
│   ┌─────────────────┐                  ┌─────────────────┐                 │
│   │ ASP: Stub       │      ───►        │ Real HTTP calls │                 │
│   │ return fake data│                  │ with retry/cache│                 │
│   └─────────────────┘                  └─────────────────┘                 │
│                                                                             │
│   ┌─────────────────┐                  ┌─────────────────┐                 │
│   │ Storage: Local  │      ───►        │ S3/Cloud Storage│                 │
│   │ filesystem      │                  │ with CDN        │                 │
│   └─────────────────┘                  └─────────────────┘                 │
│                                                                             │
│   ┌─────────────────┐                  ┌─────────────────┐                 │
│   │ Email: Log only │      ───►        │ SMTP/SendGrid   │                 │
│   │                 │                  │ Queue-based     │                 │
│   └─────────────────┘                  └─────────────────┘                 │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🗂️ MODULE 1: AUTHENTICATION SYSTEM

### 1.1 OTP Service - Production Implementation

**Hiện tại (Mock):**
```php
// Có thể đang như này
class OtpService
{
    public function generate(string $identifier): string
    {
        $otp = random_int(100000, 999999);
        // Chỉ lưu DB, không gửi thật
        return $otp;
    }
}
```

**Production Implementation:**

```php
<?php
// app/Services/Auth/OtpService.php

namespace App\Services\Auth;

use App\Models\Otp;
use App\Contracts\OtpChannelInterface;
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
    private const MAX_DAILY_REQUESTS = 10;

    public function __construct(
        private SmsChannel $smsChannel,
        private EmailChannel $emailChannel
    ) {}

    /**
     * Generate and send OTP
     */
    public function generate(string $identifier, string $type = 'email'): array
    {
        // Rate limiting check
        $this->checkRateLimit($identifier);

        // Invalidate previous OTPs
        $this->invalidatePrevious($identifier);

        // Generate secure OTP
        $otpCode = $this->generateSecureOtp();

        // Store OTP
        $otp = Otp::create([
            'identifier' => $identifier,
            'code' => hash('sha256', $otpCode), // Store hashed
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Send OTP via appropriate channel
        $this->sendOtp($identifier, $otpCode, $type);

        // Update rate limit counter
        $this->incrementDailyCounter($identifier);

        return [
            'success' => true,
            'message' => "OTP sent to {$this->maskIdentifier($identifier, $type)}",
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

        // Verify code (compare hashed)
        if (!hash_equals($otp->code, hash('sha256', $code))) {
            $remainingAttempts = self::MAX_ATTEMPTS - $otp->attempts;
            return [
                'success' => false,
                'message' => "Invalid OTP. {$remainingAttempts} attempts remaining",
                'code' => 'INVALID_OTP',
                'remaining_attempts' => $remainingAttempts
            ];
        }

        // Mark as verified
        $otp->update([
            'verified_at' => Carbon::now(),
            'verified_ip' => request()->ip()
        ]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
            'code' => 'VERIFIED'
        ];
    }

    /**
     * Check if resend is allowed
     */
    public function canResend(string $identifier): array
    {
        $lastOtp = Otp::where('identifier', $identifier)
            ->latest()
            ->first();

        if (!$lastOtp) {
            return ['can_resend' => true, 'wait_seconds' => 0];
        }

        $secondsSinceLastSend = Carbon::now()->diffInSeconds($lastOtp->created_at);
        $waitSeconds = max(0, self::RESEND_COOLDOWN_SECONDS - $secondsSinceLastSend);

        return [
            'can_resend' => $waitSeconds === 0,
            'wait_seconds' => $waitSeconds
        ];
    }

    /**
     * Generate cryptographically secure OTP
     */
    private function generateSecureOtp(): string
    {
        return str_pad(
            (string) random_int(0, (10 ** self::OTP_LENGTH) - 1),
            self::OTP_LENGTH,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Send OTP via appropriate channel
     */
    private function sendOtp(string $identifier, string $code, string $type): void
    {
        $channel = match ($type) {
            'sms', 'phone' => $this->smsChannel,
            'email' => $this->emailChannel,
            default => throw new \InvalidArgumentException("Unknown channel: {$type}")
        };

        $channel->send($identifier, $code);
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(string $identifier): void
    {
        $dailyKey = "otp_daily:{$identifier}:" . date('Y-m-d');
        $dailyCount = (int) Cache::get($dailyKey, 0);

        if ($dailyCount >= self::MAX_DAILY_REQUESTS) {
            throw new \App\Exceptions\RateLimitExceededException(
                'Daily OTP limit exceeded. Please try again tomorrow.'
            );
        }

        // Check resend cooldown
        $resendStatus = $this->canResend($identifier);
        if (!$resendStatus['can_resend']) {
            throw new \App\Exceptions\RateLimitExceededException(
                "Please wait {$resendStatus['wait_seconds']} seconds before requesting new OTP."
            );
        }
    }

    private function incrementDailyCounter(string $identifier): void
    {
        $dailyKey = "otp_daily:{$identifier}:" . date('Y-m-d');
        Cache::increment($dailyKey);
        Cache::put($dailyKey, Cache::get($dailyKey), now()->endOfDay());
    }

    private function invalidatePrevious(string $identifier): void
    {
        Otp::where('identifier', $identifier)
            ->whereNull('verified_at')
            ->update(['verified_at' => Carbon::now()]);
    }

    private function maskIdentifier(string $identifier, string $type): string
    {
        if ($type === 'email') {
            $parts = explode('@', $identifier);
            $name = $parts[0];
            $domain = $parts[1] ?? '';
            $masked = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
            return "{$masked}@{$domain}";
        }

        // Phone
        return substr($identifier, 0, 3) . str_repeat('*', strlen($identifier) - 5) . substr($identifier, -2);
    }
}
```

### 1.2 SMS Channel (Twilio/Africa's Talking)

```php
<?php
// app/Services/Notifications/SmsChannel.php

namespace App\Services\Notifications;

use App\Contracts\OtpChannelInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel implements OtpChannelInterface
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'africastalking');
    }

    public function send(string $recipient, string $code): bool
    {
        return match ($this->provider) {
            'twilio' => $this->sendViaTwilio($recipient, $code),
            'africastalking' => $this->sendViaAfricasTalking($recipient, $code),
            default => throw new \Exception("Unknown SMS provider: {$this->provider}")
        };
    }

    /**
     * Send via Twilio
     */
    private function sendViaTwilio(string $recipient, string $code): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        $message = $this->formatMessage($code);

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $this->formatPhoneNumber($recipient),
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::channel('sms')->info('OTP sent via Twilio', [
                    'recipient' => $this->maskPhone($recipient),
                    'sid' => $response->json('sid')
                ]);
                return true;
            }

            Log::channel('sms')->error('Twilio SMS failed', [
                'recipient' => $this->maskPhone($recipient),
                'error' => $response->json()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::channel('sms')->error('Twilio exception', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send via Africa's Talking (phổ biến ở Kenya)
     */
    private function sendViaAfricasTalking(string $recipient, string $code): bool
    {
        $apiKey = config('services.africastalking.api_key');
        $username = config('services.africastalking.username');
        $from = config('services.africastalking.sender_id');

        $message = $this->formatMessage($code);

        try {
            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
                'username' => $username,
                'to' => $this->formatPhoneNumber($recipient),
                'message' => $message,
                'from' => $from,
            ]);

            $result = $response->json();

            if (isset($result['SMSMessageData']['Recipients'][0]['status']) 
                && $result['SMSMessageData']['Recipients'][0]['status'] === 'Success') {
                
                Log::channel('sms')->info('OTP sent via AfricasTalking', [
                    'recipient' => $this->maskPhone($recipient),
                    'messageId' => $result['SMSMessageData']['Recipients'][0]['messageId'] ?? null
                ]);
                return true;
            }

            Log::channel('sms')->error('AfricasTalking SMS failed', [
                'recipient' => $this->maskPhone($recipient),
                'response' => $result
            ]);
            return false;

        } catch (\Exception $e) {
            Log::channel('sms')->error('AfricasTalking exception', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function formatMessage(string $code): string
    {
        $appName = config('app.name');
        return "{$code} is your {$appName} verification code. Valid for 10 minutes. Do not share this code.";
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Kenya phone format: +254XXXXXXXXX
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }
        
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }

    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 4) . '****' . substr($phone, -2);
    }
}
```

### 1.3 Email Channel

```php
<?php
// app/Services/Notifications/EmailChannel.php

namespace App\Services\Notifications;

use App\Contracts\OtpChannelInterface;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailChannel implements OtpChannelInterface
{
    public function send(string $recipient, string $code): bool
    {
        try {
            Mail::to($recipient)->queue(new OtpVerificationMail($code));
            
            Log::channel('email')->info('OTP email queued', [
                'recipient' => $this->maskEmail($recipient)
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::channel('email')->error('OTP email failed', [
                'recipient' => $this->maskEmail($recipient),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        return substr($name, 0, 2) . '***@' . $domain;
    }
}
```

```php
<?php
// app/Mail/OtpVerificationMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' - Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification',
            with: [
                'code' => $this->otpCode,
                'expiresIn' => '10 minutes',
                'appName' => config('app.name'),
            ]
        );
    }
}
```

---

## 🗂️ MODULE 2: PAYMENT INTEGRATION (M-Pesa)

### 2.1 M-Pesa Service

```php
<?php
// app/Services/Payment/MpesaService.php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MpesaService
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private string $callbackUrl;
    private string $baseUrl;

    public function __construct()
    {
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->passkey = config('services.mpesa.passkey');
        $this->callbackUrl = config('services.mpesa.callback_url');
        $this->baseUrl = config('services.mpesa.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Initiate STK Push (Lipa Na M-Pesa Online)
     */
    public function initiateSTKPush(Application $application, string $phoneNumber, float $amount): array
    {
        $accessToken = $this->getAccessToken();
        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $transactionRef = $this->generateTransactionRef($application->id);

        try {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                    'BusinessShortCode' => $this->shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => (int) $amount,
                    'PartyA' => $this->formatPhoneNumber($phoneNumber),
                    'PartyB' => $this->shortcode,
                    'PhoneNumber' => $this->formatPhoneNumber($phoneNumber),
                    'CallBackURL' => $this->callbackUrl,
                    'AccountReference' => $transactionRef,
                    'TransactionDesc' => 'Application Fee - ' . $application->id,
                ]);

            $result = $response->json();

            if ($response->successful() && isset($result['CheckoutRequestID'])) {
                // Store pending payment
                $payment = Payment::create([
                    'application_id' => $application->id,
                    'checkout_request_id' => $result['CheckoutRequestID'],
                    'merchant_request_id' => $result['MerchantRequestID'],
                    'transaction_ref' => $transactionRef,
                    'phone_number' => $this->maskPhone($phoneNumber),
                    'amount' => $amount,
                    'status' => 'pending',
                    'initiated_at' => Carbon::now(),
                ]);

                Log::channel('mpesa')->info('STK Push initiated', [
                    'application_id' => $application->id,
                    'checkout_request_id' => $result['CheckoutRequestID'],
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment request sent. Please check your phone.',
                    'checkout_request_id' => $result['CheckoutRequestID'],
                    'payment_id' => $payment->id,
                ];
            }

            Log::channel('mpesa')->error('STK Push failed', [
                'application_id' => $application->id,
                'response' => $result
            ]);

            return [
                'success' => false,
                'message' => $result['errorMessage'] ?? 'Payment initiation failed',
                'error_code' => $result['errorCode'] ?? 'UNKNOWN'
            ];

        } catch (\Exception $e) {
            Log::channel('mpesa')->error('STK Push exception', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Payment service temporarily unavailable',
                'error_code' => 'SERVICE_ERROR'
            ];
        }
    }

    /**
     * Handle M-Pesa callback
     */
    public function handleCallback(array $callbackData): void
    {
        $body = $callbackData['Body']['stkCallback'] ?? null;

        if (!$body) {
            Log::channel('mpesa')->error('Invalid callback structure', $callbackData);
            return;
        }

        $checkoutRequestId = $body['CheckoutRequestID'];
        $resultCode = $body['ResultCode'];
        $resultDesc = $body['ResultDesc'];

        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) {
            Log::channel('mpesa')->error('Payment not found for callback', [
                'checkout_request_id' => $checkoutRequestId
            ]);
            return;
        }

        if ($resultCode == 0) {
            // Success
            $metadata = collect($body['CallbackMetadata']['Item'] ?? [])
                ->pluck('Value', 'Name')
                ->toArray();

            $payment->update([
                'status' => 'completed',
                'mpesa_receipt_number' => $metadata['MpesaReceiptNumber'] ?? null,
                'transaction_date' => isset($metadata['TransactionDate']) 
                    ? Carbon::createFromFormat('YmdHis', $metadata['TransactionDate'])
                    : null,
                'paid_amount' => $metadata['Amount'] ?? $payment->amount,
                'completed_at' => Carbon::now(),
                'callback_data' => json_encode($callbackData),
            ]);

            // Update application status
            $payment->application->update(['payment_status' => 'paid']);

            Log::channel('mpesa')->info('Payment completed', [
                'payment_id' => $payment->id,
                'receipt' => $metadata['MpesaReceiptNumber'] ?? null
            ]);

            // Dispatch notification event
            event(new \App\Events\PaymentCompleted($payment));

        } else {
            // Failed
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $resultDesc,
                'callback_data' => json_encode($callbackData),
            ]);

            Log::channel('mpesa')->warning('Payment failed', [
                'payment_id' => $payment->id,
                'reason' => $resultDesc
            ]);
        }
    }

    /**
     * Verify transaction manually (for uploaded receipts)
     */
    public function verifyTransaction(string $transactionCode): array
    {
        $accessToken = $this->getAccessToken();
        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        try {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post("{$this->baseUrl}/mpesa/transactionstatus/v1/query", [
                    'Initiator' => config('services.mpesa.initiator_name'),
                    'SecurityCredential' => $this->getSecurityCredential(),
                    'CommandID' => 'TransactionStatusQuery',
                    'TransactionID' => $transactionCode,
                    'PartyA' => $this->shortcode,
                    'IdentifierType' => '4',
                    'ResultURL' => $this->callbackUrl . '/status',
                    'QueueTimeOutURL' => $this->callbackUrl . '/timeout',
                    'Remarks' => 'Transaction verification',
                    'Occasion' => 'Verification',
                ]);

            return $response->json();

        } catch (\Exception $e) {
            Log::channel('mpesa')->error('Transaction verification failed', [
                'transaction_code' => $transactionCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Verification service unavailable'
            ];
        }
    }

    /**
     * Get OAuth access token
     */
    private function getAccessToken(): string
    {
        $cacheKey = 'mpesa_access_token';

        return Cache::remember($cacheKey, 3500, function () {
            $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
            ])->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            if ($response->successful()) {
                return $response->json('access_token');
            }

            throw new \Exception('Failed to get M-Pesa access token');
        });
    }

    private function generateTransactionRef(int $applicationId): string
    {
        return 'APP' . str_pad($applicationId, 8, '0', STR_PAD_LEFT) . time();
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }
        if (str_starts_with($phone, '+')) {
            return substr($phone, 1);
        }
        return $phone;
    }

    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 4) . '****' . substr($phone, -2);
    }

    private function getSecurityCredential(): string
    {
        // In production, this should be encrypted with M-Pesa public certificate
        return config('services.mpesa.security_credential');
    }
}
```

### 2.2 Payment Controller

```php
<?php
// app/Http/Controllers/Api/V1/PaymentController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Models\Application;
use App\Services\Payment\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private MpesaService $mpesaService
    ) {}

    /**
     * Initiate M-Pesa STK Push
     */
    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $application = Application::findOrFail($request->application_id);

        // Verify ownership
        $this->authorize('pay', $application);

        // Check if already paid
        if ($application->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Application fee already paid'
            ], 400);
        }

        $result = $this->mpesaService->initiateSTKPush(
            $application,
            $request->phone_number,
            config('services.mpesa.application_fee', 1000)
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * M-Pesa callback endpoint
     */
    public function callback(Request $request): JsonResponse
    {
        $this->mpesaService->handleCallback($request->all());
        
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    /**
     * Check payment status
     */
    public function status(string $checkoutRequestId): JsonResponse
    {
        $payment = \App\Models\Payment::where('checkout_request_id', $checkoutRequestId)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $payment->status,
                'receipt_number' => $payment->mpesa_receipt_number,
                'amount' => $payment->paid_amount,
                'completed_at' => $payment->completed_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Manual payment verification (for uploaded receipts)
     */
    public function verifyManual(VerifyPaymentRequest $request): JsonResponse
    {
        $application = Application::findOrFail($request->application_id);
        $this->authorize('pay', $application);

        // Store manual payment record
        $payment = \App\Models\Payment::create([
            'application_id' => $application->id,
            'transaction_ref' => $request->transaction_code,
            'mpesa_receipt_number' => $request->transaction_code,
            'amount' => config('services.mpesa.application_fee'),
            'status' => 'pending_verification',
            'proof_image_path' => $request->file('proof_image')?->store('payment-proofs', 's3'),
            'manual_submission' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment submitted for verification',
            'payment_id' => $payment->id
        ]);
    }
}
```

---

## 🗂️ MODULE 3: ASP SYSTEM INTEGRATION

### 3.1 ASP API Service - Production

```php
<?php
// app/Services/Integration/AspApiService.php

namespace App\Services\Integration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use App\Exceptions\AspApiException;
use App\Models\ApiLog;

class AspApiService
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;
    private int $timeout;
    private int $retryTimes;
    private int $retryDelay;

    public function __construct()
    {
        $this->baseUrl = config('services.asp.base_url');
        $this->apiKey = config('services.asp.api_key');
        $this->apiSecret = config('services.asp.api_secret');
        $this->timeout = config('services.asp.timeout', 30);
        $this->retryTimes = config('services.asp.retry_times', 3);
        $this->retryDelay = config('services.asp.retry_delay', 1000);
    }

    /**
     * Get student grades from ASP system
     */
    public function getStudentGrades(string $studentCode): array
    {
        $cacheKey = "asp:grades:{$studentCode}";

        // Cache for 5 minutes
        return Cache::remember($cacheKey, 300, function () use ($studentCode) {
            return $this->makeRequest('GET', "/api/students/{$studentCode}/grades");
        });
    }

    /**
     * Get student timetable from ASP system
     */
    public function getStudentTimetable(string $studentCode): array
    {
        $cacheKey = "asp:timetable:{$studentCode}";

        // Cache for 1 hour
        return Cache::remember($cacheKey, 3600, function () use ($studentCode) {
            return $this->makeRequest('GET', "/api/students/{$studentCode}/timetable");
        });
    }

    /**
     * Get student fees/financial info from ASP system
     */
    public function getStudentFees(string $studentCode): array
    {
        // No caching for financial data - always fetch fresh
        return $this->makeRequest('GET', "/api/students/{$studentCode}/fees");
    }

    /**
     * Sync student data to ASP system (after approval)
     */
    public function syncStudentToAsp(array $studentData): array
    {
        return $this->makeRequest('POST', '/api/students/sync', $studentData);
    }

    /**
     * Get program/course list from ASP
     */
    public function getPrograms(): array
    {
        $cacheKey = 'asp:programs';

        // Cache for 24 hours
        return Cache::remember($cacheKey, 86400, function () {
            return $this->makeRequest('GET', '/api/programs');
        });
    }

    /**
     * Make authenticated request to ASP system
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = rtrim($this->baseUrl, '/') . $endpoint;
        $timestamp = time();
        $requestId = uniqid('req_', true);

        // Generate signature
        $payload = $method === 'GET' ? '' : json_encode($data);
        $signature = $this->generateSignature($payload, $timestamp);

        $startTime = microtime(true);
        $response = null;
        $error = null;

        try {
            $response = $this->getHttpClient()
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'X-Timestamp' => $timestamp,
                    'X-Signature' => $signature,
                    'X-Request-ID' => $requestId,
                ])
                ->{strtolower($method)}($url, $data);

            $responseData = $response->json();

            if (!$response->successful()) {
                throw new AspApiException(
                    $responseData['message'] ?? 'ASP API request failed',
                    $response->status()
                );
            }

            return $responseData;

        } catch (AspApiException $e) {
            $error = $e;
            throw $e;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $error = $e;
            throw new AspApiException('Cannot connect to ASP system', 503);

        } catch (\Exception $e) {
            $error = $e;
            throw new AspApiException('ASP API error: ' . $e->getMessage(), 500);

        } finally {
            // Log all requests
            $this->logRequest(
                $requestId,
                $method,
                $endpoint,
                $data,
                $response,
                $error,
                microtime(true) - $startTime
            );
        }
    }

    /**
     * Get configured HTTP client
     */
    private function getHttpClient(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->retry($this->retryTimes, $this->retryDelay, function ($exception, $request) {
                // Only retry on connection errors or 5xx responses
                return $exception instanceof \Illuminate\Http\Client\ConnectionException
                    || ($exception instanceof \Illuminate\Http\Client\RequestException 
                        && $exception->response->serverError());
            })
            ->withOptions([
                'verify' => config('services.asp.verify_ssl', true),
            ]);
    }

    /**
     * Generate HMAC signature
     */
    private function generateSignature(string $payload, int $timestamp): string
    {
        $data = $payload . $timestamp;
        return hash_hmac('sha256', $data, $this->apiSecret);
    }

    /**
     * Log API request/response
     */
    private function logRequest(
        string $requestId,
        string $method,
        string $endpoint,
        array $requestData,
        ?Response $response,
        ?\Throwable $error,
        float $duration
    ): void {
        ApiLog::create([
            'request_id' => $requestId,
            'direction' => 'outgoing',
            'method' => $method,
            'endpoint' => $endpoint,
            'request_body' => $this->sanitizeForLog($requestData),
            'response_body' => $response?->json(),
            'status_code' => $response?->status() ?? ($error ? 0 : null),
            'error_message' => $error?->getMessage(),
            'duration_ms' => round($duration * 1000),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Remove sensitive data from logs
     */
    private function sanitizeForLog(array $data): array
    {
        $sensitiveFields = ['password', 'secret', 'token', 'id_number'];

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveFields) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $value = '[REDACTED]';
            }
        });

        return $data;
    }

    /**
     * Clear cache for student data
     */
    public function clearStudentCache(string $studentCode): void
    {
        Cache::forget("asp:grades:{$studentCode}");
        Cache::forget("asp:timetable:{$studentCode}");
    }

    /**
     * Health check for ASP system
     */
    public function healthCheck(): array
    {
        try {
            $startTime = microtime(true);
            $response = $this->getHttpClient()
                ->timeout(5)
                ->get($this->baseUrl . '/api/health');
            
            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'response_time_ms' => round((microtime(true) - $startTime) * 1000),
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unreachable',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }
}
```

### 3.2 API Authentication Middleware (for incoming ASP requests)

```php
<?php
// app/Http/Middleware/ApiAuthentication.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthentication
{
    private const TIMESTAMP_TOLERANCE_SECONDS = 300; // 5 minutes

    public function handle(Request $request, Closure $next): Response
    {
        $requestId = uniqid('in_', true);
        $startTime = microtime(true);

        try {
            // Validate required headers
            $apiKey = $request->header('X-API-Key');
            $timestamp = $request->header('X-Timestamp');
            $signature = $request->header('X-Signature');

            if (!$apiKey || !$timestamp || !$signature) {
                return $this->unauthorizedResponse('Missing authentication headers', $requestId);
            }

            // Validate API key
            if ($apiKey !== config('services.asp.api_key')) {
                return $this->unauthorizedResponse('Invalid API key', $requestId);
            }

            // Validate timestamp (prevent replay attacks)
            $timestampInt = (int) $timestamp;
            $currentTime = time();
            
            if (abs($currentTime - $timestampInt) > self::TIMESTAMP_TOLERANCE_SECONDS) {
                return $this->unauthorizedResponse('Request timestamp expired', $requestId);
            }

            // Validate signature
            $payload = $request->getContent();
            $expectedSignature = hash_hmac(
                'sha256',
                $payload . $timestamp,
                config('services.asp.api_secret')
            );

            if (!hash_equals($expectedSignature, $signature)) {
                return $this->unauthorizedResponse('Invalid signature', $requestId);
            }

            // Add request ID to request for tracking
            $request->attributes->set('api_request_id', $requestId);

            // Process request
            $response = $next($request);

            // Log successful request
            $this->logRequest($requestId, $request, $response, null, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('API Authentication error', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);

            return $this->unauthorizedResponse('Authentication error', $requestId);
        }
    }

    private function unauthorizedResponse(string $message, string $requestId): Response
    {
        Log::warning('API authentication failed', [
            'request_id' => $requestId,
            'message' => $message,
            'ip' => request()->ip()
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
            'request_id' => $requestId
        ], 401);
    }

    private function logRequest(
        string $requestId,
        Request $request,
        Response $response,
        ?\Exception $error,
        float $startTime
    ): void {
        ApiLog::create([
            'request_id' => $requestId,
            'direction' => 'incoming',
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'request_body' => $this->sanitizeRequestData($request->all()),
            'response_body' => json_decode($response->getContent(), true),
            'status_code' => $response->getStatusCode(),
            'error_message' => $error?->getMessage(),
            'duration_ms' => round((microtime(true) - $startTime) * 1000),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = ['password', 'secret', 'id_number', 'phone'];

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveFields) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $value = '[REDACTED]';
            }
        });

        return $data;
    }
}
```

---

## 🗂️ MODULE 4: FILE STORAGE (Cloud)

### 4.1 Document Service

```php
<?php
// app/Services/Storage/DocumentStorageService.php

namespace App\Services\Storage;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class DocumentStorageService
{
    private string $disk;
    private array $allowedMimeTypes;
    private int $maxFileSize;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 's3');
        $this->allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
        ];
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
    }

    /**
     * Upload document
     */
    public function upload(UploadedFile $file, int $applicationId, string $documentType): Document
    {
        // Validate file
        $this->validateFile($file);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);
        $path = "applications/{$applicationId}/{$documentType}/{$filename}";

        // Process and upload
        if ($this->isImage($file)) {
            $processedFile = $this->processImage($file);
            Storage::disk($this->disk)->put($path, $processedFile, 'private');
        } else {
            Storage::disk($this->disk)->putFileAs(
                "applications/{$applicationId}/{$documentType}",
                $file,
                $filename,
                'private'
            );
        }

        // Create document record
        return Document::create([
            'application_id' => $applicationId,
            'document_type' => $documentType,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'disk' => $this->disk,
            'uploaded_at' => now(),
        ]);
    }

    /**
     * Get temporary signed URL for viewing
     */
    public function getTemporaryUrl(Document $document, int $expiryMinutes = 60): string
    {
        return Storage::disk($document->disk)->temporaryUrl(
            $document->file_path,
            now()->addMinutes($expiryMinutes)
        );
    }

    /**
     * Delete document
     */
    public function delete(Document $document): bool
    {
        // Delete from storage
        Storage::disk($document->disk)->delete($document->file_path);

        // Delete record
        return $document->delete();
    }

    /**
     * Validate file
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \App\Exceptions\InvalidFileTypeException(
                'File type not allowed. Accepted types: JPG, PNG, GIF, PDF'
            );
        }

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \App\Exceptions\FileTooLargeException(
                'File size exceeds maximum allowed (10MB)'
            );
        }

        // Validate file is not corrupted
        if (!$file->isValid()) {
            throw new \App\Exceptions\InvalidFileException('File upload failed');
        }

        // Additional security: scan for malicious content
        $this->scanForMalware($file);
    }

    /**
     * Generate secure filename
     */
    private function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $hash = Str::random(32);
        return "{$hash}.{$extension}";
    }

    /**
     * Process and optimize image
     */
    private function processImage(UploadedFile $file): string
    {
        $image = Image::make($file);

        // Resize if too large (max 2000px)
        $image->resize(2000, 2000, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Strip metadata (EXIF) for privacy
        $image->orientate();

        // Encode with quality
        return $image->encode($file->getClientOriginalExtension(), 85)->getEncoded();
    }

    /**
     * Check if file is image
     */
    private function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Scan for malware (integrate with ClamAV or similar)
     */
    private function scanForMalware(UploadedFile $file): void
    {
        // In production, integrate with ClamAV or cloud-based scanning
        // Example using ClamAV:
        
        if (config('services.clamav.enabled', false)) {
            $scanner = new \App\Services\Security\ClamAvScanner();
            $result = $scanner->scan($file->getRealPath());
            
            if (!$result['clean']) {
                throw new \App\Exceptions\MalwareDetectedException(
                    'File rejected due to security concerns'
                );
            }
        }
    }
}
```

---

## 🗂️ MODULE 5: CONFIGURATION

### 5.1 Environment Configuration

```env
# .env.production

# Application
APP_NAME="Student Admission Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://portal.yourschool.ac.ke

# Database
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.amazonaws.com
DB_PORT=3306
DB_DATABASE=admission_portal
DB_USERNAME=portal_user
DB_PASSWORD=your_secure_password

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=your-redis-endpoint.cache.amazonaws.com
REDIS_PORT=6379
REDIS_PASSWORD=null

# Mail (SendGrid)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourschool.ac.ke
MAIL_FROM_NAME="${APP_NAME}"

# SMS Provider (Africa's Talking)
SMS_PROVIDER=africastalking
AFRICASTALKING_USERNAME=your_username
AFRICASTALKING_API_KEY=your_api_key
AFRICASTALKING_SENDER_ID=SCHOOL

# Alternative: Twilio
TWILIO_SID=your_account_sid
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=+1234567890

# M-Pesa Integration
MPESA_ENVIRONMENT=production
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=123456
MPESA_PASSKEY=your_passkey
MPESA_CALLBACK_URL=https://portal.yourschool.ac.ke/api/v1/payments/callback
MPESA_INITIATOR_NAME=your_initiator
MPESA_SECURITY_CREDENTIAL=your_credential
MPESA_APPLICATION_FEE=1000

# ASP System Integration
ASP_BASE_URL=https://internal-asp.yourschool.ac.ke/api
ASP_API_KEY=your_asp_api_key
ASP_API_SECRET=your_asp_api_secret
ASP_TIMEOUT=30
ASP_RETRY_TIMES=3
ASP_VERIFY_SSL=true

# Storage (S3)
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=af-south-1
AWS_BUCKET=admission-portal-documents
AWS_URL=https://your-bucket.s3.af-south-1.amazonaws.com

# Security
SANCTUM_STATEFUL_DOMAINS=portal.yourschool.ac.ke
SESSION_DOMAIN=.yourschool.ac.ke

# Rate Limiting
RATE_LIMIT_PER_MINUTE=60

# Logging
LOG_CHANNEL=stack
LOG_STACK=daily,slack
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/xxx

# Monitoring
SENTRY_LARAVEL_DSN=https://xxx@sentry.io/xxx
```

### 5.2 Services Configuration

```php
<?php
// config/services.php

return [
    // ... existing config

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'africastalking'),
    ],

    'africastalking' => [
        'username' => env('AFRICASTALKING_USERNAME'),
        'api_key' => env('AFRICASTALKING_API_KEY'),
        'sender_id' => env('AFRICASTALKING_SENDER_ID'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'mpesa' => [
        'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'callback_url' => env('MPESA_CALLBACK_URL'),
        'initiator_name' => env('MPESA_INITIATOR_NAME'),
        'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),
        'application_fee' => env('MPESA_APPLICATION_FEE', 1000),
    ],

    'asp' => [
        'base_url' => env('ASP_BASE_URL'),
        'api_key' => env('ASP_API_KEY'),
        'api_secret' => env('ASP_API_SECRET'),
        'timeout' => env('ASP_TIMEOUT', 30),
        'retry_times' => env('ASP_RETRY_TIMES', 3),
        'retry_delay' => env('ASP_RETRY_DELAY', 1000),
        'verify_ssl' => env('ASP_VERIFY_SSL', true),
    ],

    'clamav' => [
        'enabled' => env('CLAMAV_ENABLED', false),
        'socket' => env('CLAMAV_SOCKET', '/var/run/clamav/clamd.ctl'),
    ],
];
```

---

## 🗂️ MODULE 6: DATABASE MIGRATIONS

### 6.1 Payments Table

```php
<?php
// database/migrations/xxxx_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            
            // M-Pesa STK Push fields
            $table->string('checkout_request_id')->nullable()->index();
            $table->string('merchant_request_id')->nullable();
            $table->string('transaction_ref')->unique();
            
            // Payment details
            $table->string('phone_number', 20);
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('KES');
            
            // M-Pesa response
            $table->string('mpesa_receipt_number')->nullable()->index();
            $table->timestamp('transaction_date')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'processing', 
                'completed',
                'failed',
                'cancelled',
                'pending_verification'
            ])->default('pending')->index();
            $table->string('failure_reason')->nullable();
            
            // Manual submission
            $table->boolean('manual_submission')->default(false);
            $table->string('proof_image_path')->nullable();
            
            // Metadata
            $table->json('callback_data')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

### 6.2 API Logs Table

```php
<?php
// database/migrations/xxxx_create_api_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 50)->unique();
            
            // Direction: incoming (ASP -> Portal) or outgoing (Portal -> ASP)
            $table->enum('direction', ['incoming', 'outgoing'])->index();
            
            // Request info
            $table->string('method', 10);
            $table->string('endpoint', 255);
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            
            // Response info
            $table->json('response_body')->nullable();
            $table->integer('status_code')->nullable()->index();
            $table->text('error_message')->nullable();
            
            // Performance
            $table->integer('duration_ms')->nullable();
            
            // Client info
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            
            $table->timestamps();
            
            // Indexes for querying
            $table->index(['direction', 'created_at']);
            $table->index(['endpoint', 'status_code']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
```

### 6.3 OTPs Table

```php
<?php
// database/migrations/xxxx_create_otps_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            
            $table->string('identifier')->index(); // email or phone
            $table->string('code', 64); // stored as hash
            $table->enum('type', ['email', 'sms', 'phone'])->default('email');
            
            // Security
            $table->integer('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            
            // Tracking
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->ipAddress('verified_ip')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['identifier', 'expires_at']);
            $table->index(['identifier', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
```

---

## 📅 DEPLOYMENT TIMELINE

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    PRODUCTION DEPLOYMENT TIMELINE                            │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  WEEK 1: Core Services                                                      │
│  ══════════════════════                                                     │
│  Day 1-2: OTP Service Implementation                                        │
│           □ OtpService với SMS/Email channels                               │
│           □ Rate limiting                                                   │
│           □ Unit tests                                                      │
│                                                                             │
│  Day 3-4: SMS/Email Integration                                             │
│           □ Africa's Talking / Twilio integration                           │
│           □ SendGrid email setup                                            │
│           □ Email templates                                                 │
│                                                                             │
│  Day 5: Testing & Bug fixes                                                 │
│                                                                             │
│  WEEK 2: Payment & Storage                                                  │
│  ══════════════════════════                                                 │
│  Day 6-7: M-Pesa Integration                                                │
│           □ STK Push implementation                                         │
│           □ Callback handling                                               │
│           □ Transaction verification                                        │
│                                                                             │
│  Day 8-9: Cloud Storage                                                     │
│           □ S3 configuration                                                │
│           □ DocumentStorageService                                          │
│           □ Signed URLs                                                     │
│                                                                             │
│  Day 10: Testing & Bug fixes                                                │
│                                                                             │
│  WEEK 3: ASP Integration & Security                                         │
│  ═════════════════════════════════                                          │
│  Day 11-12: ASP System Integration                                          │
│             □ AspApiService production implementation                       │
│             □ ApiAuthentication middleware                                  │
│             □ Error handling & retries                                      │
│                                                                             │
│  Day 13-14: Security Hardening                                              │
│             □ Rate limiting all endpoints                                   │
│             □ Input validation                                              │
│             □ Security headers                                              │
│             □ Logging & monitoring                                          │
│                                                                             │
│  Day 15: End-to-end Testing                                                 │
│                                                                             │
│  WEEK 4: Deployment & Go-Live                                               │
│  ═════════════════════════════                                              │
│  Day 16-17: Staging Deployment                                              │
│             □ Deploy to staging                                             │
│             □ UAT testing                                                   │
│             □ Performance testing                                           │
│                                                                             │
│  Day 18-19: Production Deployment                                           │
│             □ Database migration                                            │
│             □ Deploy to production                                          │
│             □ Smoke testing                                                 │
│                                                                             │
│  Day 20: Monitoring & Documentation                                         │
│          □ Setup alerts                                                     │
│          □ Documentation                                                    │
│          □ Training                                                         │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## ✅ PRODUCTION CHECKLIST

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       PRODUCTION READINESS CHECKLIST                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  SECURITY                                                                   │
│  ════════                                                                   │
│  □ All secrets in environment variables                                     │
│  □ HTTPS enforced                                                           │
│  □ CORS properly configured                                                 │
│  □ Rate limiting on all public endpoints                                    │
│  □ SQL injection prevention verified                                        │
│  □ XSS prevention verified                                                  │
│  □ File upload validation                                                   │
│  □ HMAC signatures for API auth                                            │
│  □ Sensitive data encrypted at rest                                        │
│  □ API keys rotatable                                                       │
│                                                                             │
│  RELIABILITY                                                                │
│  ═══════════                                                                │
│  □ Database backups configured                                              │
│  □ Error handling all edge cases                                            │
│  □ Retry logic for external APIs                                            │
│  □ Circuit breaker for ASP calls                                            │
│  □ Queue workers for async tasks                                            │
│  □ Health check endpoints                                                   │
│                                                                             │
│  MONITORING                                                                 │
│  ══════════                                                                 │
│  □ Application error logging (Sentry/Bugsnag)                              │
│  □ API request/response logging                                             │
│  □ Performance monitoring (New Relic/DataDog)                              │
│  □ Uptime monitoring                                                        │
│  □ Alert channels configured (Slack/Email)                                 │
│                                                                             │
│  PERFORMANCE                                                                │
│  ═══════════                                                                │
│  □ Database indexes optimized                                               │
│  □ Redis caching configured                                                 │
│  □ Query optimization (no N+1)                                              │
│  □ Asset optimization (if any)                                              │
│  □ CDN for static files                                                     │
│                                                                             │
│  DOCUMENTATION                                                              │
│  ═════════════                                                              │
│  □ API documentation complete                                               │
│  □ Deployment guide                                                         │
│  □ Runbook for common issues                                                │
│  □ Environment setup guide                                                  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 📁 ROUTES CONFIGURATION

```php
<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\ApplicationController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\StatusController;
use App\Http\Controllers\Api\V1\DataProxyController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('login', [LoginController::class, 'login']);
    Route::post('otp/send', [OtpController::class, 'send']);
    Route::post('otp/verify', [OtpController::class, 'verify']);
    Route::post('otp/resend', [OtpController::class, 'resend']);
});

// M-Pesa callback (must be public)
Route::post('v1/payments/callback', [PaymentController::class, 'callback'])
    ->name('mpesa.callback');

/*
|--------------------------------------------------------------------------
| Protected Routes (Student Authentication via Sanctum)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Profile
    Route::get('me', [StudentController::class, 'profile']);
    Route::put('me', [StudentController::class, 'updateProfile']);
    Route::post('logout', [LoginController::class, 'logout']);
    
    // Application Management
    Route::apiResource('applications', ApplicationController::class);
    Route::post('applications/{application}/submit', [ApplicationController::class, 'submit']);
    Route::get('applications/{application}/status', [ApplicationController::class, 'status']);
    
    // Documents
    Route::post('applications/{application}/documents', [DocumentController::class, 'upload']);
    Route::delete('documents/{document}', [DocumentController::class, 'destroy']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download']);
    
    // Payments
    Route::post('payments/initiate', [PaymentController::class, 'initiate']);
    Route::get('payments/{checkoutRequestId}/status', [PaymentController::class, 'status']);
    Route::post('payments/verify-manual', [PaymentController::class, 'verifyManual']);
    
    // Data Proxy (for approved students)
    Route::middleware('verified.student')->group(function () {
        Route::get('my/grades', [DataProxyController::class, 'grades']);
        Route::get('my/timetable', [DataProxyController::class, 'timetable']);
        Route::get('my/fees', [DataProxyController::class, 'fees']);
    });
});

/*
|--------------------------------------------------------------------------
| ASP Integration Routes (HMAC Authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')
    ->middleware('api.auth')  // Custom HMAC middleware
    ->group(function () {
        
        // Fetch students for review
        Route::get('students', [StudentController::class, 'index']);
        Route::get('students/{id}', [StudentController::class, 'show']);
        
        // Update status
        Route::post('update-status', [StatusController::class, 'update']);
        Route::post('bulk-update-status', [StatusController::class, 'bulkUpdate']);
        
        // Sync endpoints
        Route::get('sync/pending', [StudentController::class, 'pendingSync']);
        Route::post('sync/confirm', [StudentController::class, 'confirmSync']);
    });

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'version' => config('app.version', '1.0.0'),
    ]);
});
```

---
