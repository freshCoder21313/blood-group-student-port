<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Application;
use App\Models\Payment;

class MpesaPaymentProcessor implements PaymentProcessorInterface
{
    /**
     * Create a new MpesaPaymentProcessor instance.
     */
    public function __construct(
        protected MpesaService $mpesaService
    ) {}

    /**
     * Process the electronic M-Pesa payment by triggering an STK Push.
     */
    public function process(Application $application, array $data): Payment
    {
        $phoneNumber = $data['phone_number'];
        $amount = $data['amount'];
        $reference = 'APP-'.$application->application_number;

        $response = $this->mpesaService->initiateStkPush($phoneNumber, $amount, $reference);

        $checkoutRequestId = $response['CheckoutRequestID'] ?? null;
        $merchantRequestId = $response['MerchantRequestID'] ?? null;

        if (! $checkoutRequestId) {
            throw new \RuntimeException($response['ResponseDescription'] ?? 'Failed to initiate M-Pesa STK Push');
        }

        return Payment::create([
            'application_id' => $application->id,
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $merchantRequestId,
            'transaction_code' => null,
            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'result_desc' => 'Initiated',
        ]);
    }
}
