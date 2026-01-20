<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Payment;
use App\Services\Payment\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private MpesaService $mpesaService
    ) {}

    /**
     * Initiate M-Pesa STK Push
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|integer|exists:applications,id',
            'phone_number' => 'required|string',
        ]);

        $application = Application::findOrFail($request->application_id);

        // Check if already paid
        if ($application->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Application fee already paid'
            ], 400);
        }

        $amount = config('services.mpesa.application_fee', 1000);

        $result = $this->mpesaService->initiateSTKPush(
            $application,
            $request->phone_number,
            $amount
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
     * Nộp bằng chứng thanh toán (Manual Upload)
     */
    public function submitPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|integer|exists:applications,id',
            'amount' => 'required|numeric|min:0',
            'transaction_code' => 'required|string|max:50',
            'receipt_image' => 'required|image|max:5120',
            'payment_method' => 'required|string|in:bank_transfer,cash,momo,vnpay'
        ]);

        $application = Application::findOrFail($validated['application_id']);

        $path = null;
        if ($request->hasFile('receipt_image')) {
            $path = $request->file('receipt_image')->store('payments', 'public');
        }

        $payment = Payment::create([
            'application_id' => $application->id,
            'amount' => $validated['amount'],
            'transaction_code' => $validated['transaction_code'],
            'payment_method' => $validated['payment_method'],
            'proof_url' => $path,
            'status' => 'pending',
            'payment_date' => now(),
            'manual_submission' => true
        ]);

        if ($application->status === 'draft' || $application->status === 'request_info') {
            $application->update(['status' => 'pending_payment']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment submitted successfully',
            'data' => $payment
        ]);
    }

    /**
     * Lấy lịch sử thanh toán của hồ sơ
     */
    public function history(int $applicationId): JsonResponse
    {
        $payments = Payment::where('application_id', $applicationId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }
}
