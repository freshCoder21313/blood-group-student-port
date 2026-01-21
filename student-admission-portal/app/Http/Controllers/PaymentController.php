<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Payment;
use App\Services\Payment\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected MpesaService $mpesaService)
    {
    }

    public function store(Request $request, Application $application)
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^(07|01|2547|2541)[0-9]{8}$/'],
        ]);

        $phoneNumber = $request->phone_number;
        $amount = config('admission.payment.amount', 1000); 
        $reference = 'APP-' . $application->application_number;  

        // Initiate STK Push
        $response = $this->mpesaService->initiateStkPush($phoneNumber, $amount, $reference);

        $checkoutRequestId = $response['CheckoutRequestID'] ?? null;
        $merchantRequestId = $response['MerchantRequestID'] ?? null;

        if ($checkoutRequestId) {
             Payment::create([
                'application_id' => $application->id,
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId,
                'transaction_code' => null,
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'status' => 'pending',
                'result_desc' => 'Initiated',
            ]);

            return response()->json(['success' => true, 'message' => 'Payment initiated']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to initiate payment'], 500);
    }
    
    public function storeManual(Request $request, Application $application)
    {
        $request->validate([
            'transaction_code' => ['required', 'string', 'regex:/^[A-Z0-9]{10}$/'],
            'proof_document' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:5120'],
        ]);

        $data = $request->only(['transaction_code', 'proof_document']);
        $data['amount'] = config('admission.payment.amount', 1000); 

        $this->mpesaService->recordManualPayment($application, $data);

        return redirect()->route('application.payment', $application)
            ->with('status', 'payment-verification-pending');
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
        // Log callback for debugging/audit
        Log::info('M-Pesa Callback', $request->all());

        $this->mpesaService->processCallback($request->all());

        return response()->json(['result' => 'ok']);
    }
}
