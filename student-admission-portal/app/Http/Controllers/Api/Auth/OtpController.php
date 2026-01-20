<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {}

    public function send(Request $request): JsonResponse
    {
        $request->validate(['identifier' => 'required']); // Email or Phone

        $type = filter_var($request->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'sms';

        try {
            $result = $this->otpService->generate($request->identifier, $type);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 429);
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required',
            'otp_code' => 'required|string|size:6'
        ]);

        $result = $this->otpService->verify($request->identifier, $request->otp_code);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
