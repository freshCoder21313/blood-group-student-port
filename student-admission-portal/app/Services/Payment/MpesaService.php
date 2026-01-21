<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Application;

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
        $this->consumerKey = config('mpesa.consumer_key') ?? '';
        $this->consumerSecret = config('mpesa.consumer_secret') ?? '';
        $this->shortcode = config('mpesa.shortcode') ?? '';
        $this->passkey = config('mpesa.passkey') ?? '';
        $this->callbackUrl = config('mpesa.callback_url') ?? '';
        $this->baseUrl = config('mpesa.env') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    public function initiateStkPush(string $phoneNumber, float $amount, string $reference)
    {
        if (empty($this->consumerKey)) {
            Log::warning("M-Pesa keys missing. Returning mock success.");
            return [
                'MerchantRequestID' => 'MOCK_' . uniqid(),
                'CheckoutRequestID' => 'MOCK_' . uniqid(),
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success (Mock)',
                'CustomerMessage' => 'Success (Mock)',
            ];
        }

        try {
            $accessToken = $this->getAccessToken();
            $timestamp = Carbon::now()->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);

            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                    'BusinessShortCode' => $this->shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => (int) $amount,
                    'PartyA' => $formattedPhone,
                    'PartyB' => $this->shortcode,
                    'PhoneNumber' => $formattedPhone,
                    'CallBackURL' => $this->callbackUrl,
                    'AccountReference' => $reference,
                    'TransactionDesc' => 'Payment ' . $reference,
                ]);

            $result = $response->json();
            
            if (!$response->successful()) {
                Log::error('M-Pesa STK Push Failed', $result);
                // We could throw exception here, but returning result allows controller to handle
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function processCallback(array $payload)
    {
        $body = $payload['Body']['stkCallback'] ?? null;

        if (!$body) {
            Log::error('Invalid M-Pesa callback structure');
            return;
        }

        $checkoutRequestId = $body['CheckoutRequestID'];
        $resultCode = $body['ResultCode'];

        // Find payment by checkout_request_id
        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) {
            Log::warning("Payment not found for Callback CheckoutRequestID: $checkoutRequestId");
            return;
        }

        if ($resultCode == 0) {
            $metadata = collect($body['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name')->toArray();
            
            $payment->update([
                'status' => 'completed',
                'mpesa_receipt_number' => $metadata['MpesaReceiptNumber'] ?? null,
                'transaction_code' => $metadata['MpesaReceiptNumber'] ?? null, // Aligning transaction_code
                'amount' => $metadata['Amount'] ?? $payment->amount,
                'result_desc' => 'Success',
            ]);
            
            // Update application status as per AC
            if ($payment->application) {
                // 'payment_received' is not in enum, using 'pending_approval'
                $payment->application->update(['status' => 'pending_approval']);
            }

        } else {
            $payment->update([
                'status' => 'failed',
                'result_desc' => $body['ResultDesc'] ?? 'Unknown error'
            ]);
        }
    }

    public function recordManualPayment(Application $application, array $data): Payment
    {
        $path = null;
        if (isset($data['proof_document']) && $data['proof_document'] instanceof \Illuminate\Http\UploadedFile) {
            $path = $data['proof_document']->store('payment_proofs', 'private');
        }

        $payment = Payment::where('application_id', $application->id)
            ->where('status', Payment::STATUS_PENDING)
            ->first();

        if (!$payment) {
            $payment = new Payment();
            $payment->application_id = $application->id;
        }

        $payment->status = Payment::STATUS_PENDING_VERIFICATION;
        $payment->transaction_code = $data['transaction_code'];
        $payment->amount = $data['amount'] ?? $payment->amount ?? 0;
        $payment->proof_document_path = $path;
        $payment->manual_submission = true;
        $payment->save();

        return $payment;
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

