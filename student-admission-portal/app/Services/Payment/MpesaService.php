<?php

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
        $this->consumerKey = config('services.mpesa.consumer_key') ?? '';
        $this->consumerSecret = config('services.mpesa.consumer_secret') ?? '';
        $this->shortcode = config('services.mpesa.shortcode') ?? '';
        $this->passkey = config('services.mpesa.passkey') ?? '';
        $this->callbackUrl = config('services.mpesa.callback_url') ?? '';
        $this->baseUrl = config('services.mpesa.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    public function initiateSTKPush(Application $application, string $phoneNumber, float $amount): array
    {
        // Mock success for development if keys are missing
        if (empty($this->consumerKey)) {
            Log::warning("M-Pesa keys missing. Returning mock success.");
            return [
                'success' => true,
                'message' => 'Payment request sent (Mock).',
                'checkout_request_id' => 'MOCK_' . uniqid(),
                'payment_id' => 0,
            ];
        }

        try {
            $accessToken = $this->getAccessToken();
            $timestamp = Carbon::now()->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
            $transactionRef = 'APP' . str_pad($application->id, 8, '0', STR_PAD_LEFT) . time();

            $response = Http::withToken($accessToken)
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
                $payment = Payment::create([
                    'application_id' => $application->id,
                    'checkout_request_id' => $result['CheckoutRequestID'],
                    'merchant_request_id' => $result['MerchantRequestID'],
                    'transaction_ref' => $transactionRef,
                    'phone_number' => $phoneNumber,
                    'amount' => $amount,
                    'status' => 'pending',
                    'initiated_at' => Carbon::now(),
                    'payment_method' => 'mpesa'
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment request sent. Please check your phone.',
                    'checkout_request_id' => $result['CheckoutRequestID'],
                    'payment_id' => $payment->id,
                ];
            }

            return [
                'success' => false,
                'message' => $result['errorMessage'] ?? 'Payment initiation failed',
                'error_code' => $result['errorCode'] ?? 'UNKNOWN'
            ];

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment service error',
                'error_code' => 'SERVICE_ERROR'
            ];
        }
    }

    public function handleCallback(array $callbackData): void
    {
        $body = $callbackData['Body']['stkCallback'] ?? null;

        if (!$body) {
            Log::error('Invalid M-Pesa callback structure');
            return;
        }

        $checkoutRequestId = $body['CheckoutRequestID'];
        $resultCode = $body['ResultCode'];

        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) return;

        if ($resultCode == 0) {
            $metadata = collect($body['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name')->toArray();
            
            $payment->update([
                'status' => 'completed',
                'mpesa_receipt_number' => $metadata['MpesaReceiptNumber'] ?? null,
                'paid_amount' => $metadata['Amount'] ?? $payment->amount,
                'completed_at' => Carbon::now(),
            ]);

            $payment->application->update(['payment_status' => 'paid']);
        } else {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $body['ResultDesc'] ?? 'Unknown error'
            ]);
        }
    }

    private function getAccessToken(): string
    {
        return Cache::remember('mpesa_access_token', 3000, function () {
            $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
            $response = Http::withHeaders(['Authorization' => 'Basic ' . $credentials])
                ->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");
            
            return $response->json('access_token');
        });
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) return '254' . substr($phone, 1);
        if (str_starts_with($phone, '+')) return substr($phone, 1);
        return $phone;
    }
}
