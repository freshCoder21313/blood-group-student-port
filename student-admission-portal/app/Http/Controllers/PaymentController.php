<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Payment;
use App\Services\Application\ApplicationService;
use App\Services\Payment\MpesaService;
use App\Services\Payment\MpesaPaymentProcessor;
use App\Services\Payment\ManualPaymentProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected MpesaService $mpesaService,
        protected ApplicationService $applicationService
    ) {
    }

    public function store(Request $request, Application $application)
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^(07|01|2547|2541)[0-9]{8}$/'],
        ]);

        try {
            $processor = new MpesaPaymentProcessor($this->mpesaService);
            $processor->process($application, [
                'phone_number' => $request->phone_number,
                'amount' => $this->applicationService->getAdmissionFee(),
            ]);

            return response()->json(['success' => true, 'message' => 'Payment initiated']);
        } catch (\Exception $e) {
            Log::error('M-Pesa payment initiation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function storeManual(Request $request, Application $application)
    {
        $request->validate([
            'transaction_code' => ['required', 'string', 'regex:/^[A-Z0-9]{10}$/'],
            'proof_document' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:5120'],
        ]);

        try {
            $processor = new ManualPaymentProcessor();
            $processor->process($application, [
                'transaction_code' => $request->transaction_code,
                'proof_document' => $request->file('proof_document'),
                'amount' => $this->applicationService->getAdmissionFee(),
            ]);

            return redirect()->route('application.payment', $application);
        } catch (\Exception $e) {
            Log::error('Manual payment submission failed', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['proof_document' => 'Failed to process manual payment: ' . $e->getMessage()]);
        }
    }

    public function checkStatus(Application $application)
    {
        // Find latest payment for this application
        $payment = Payment::where('application_id', $application->id)
            ->latest()
            ->first();
        
        if (!$payment) {
            return response()->json(['status' => 'none']);
        }
        
        return response()->json(['status' => $payment->status]);
    }

    public function callback(Request $request)
    {
        // ... (existing code)
        $this->mpesaService->processCallback($request->all());

        return response()->json(['result' => 'ok']);
    }

    public function simulateCallback(Application $application)
    {
        if (!app()->environment('local', 'testing')) {
            abort(404);
        }

        // Find pending payment
        $payment = Payment::where('application_id', $application->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$payment) {
            return response()->json(['message' => 'No pending payment found'], 404);
        }

        // Simulate successful callback payload
        $mockPayload = [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => $payment->merchant_request_id,
                    'CheckoutRequestID' => $payment->checkout_request_id,
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => $payment->amount],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'SIM' . strtoupper(uniqid())],
                            ['Name' => 'TransactionDate', 'Value' => now()->format('YmdHis')],
                            ['Name' => 'PhoneNumber', 'Value' => $payment->phone_number],
                        ]
                    ]
                ]
            ]
        ];

        $this->mpesaService->processCallback($mockPayload);

        return response()->json(['success' => true]);
    }
}
