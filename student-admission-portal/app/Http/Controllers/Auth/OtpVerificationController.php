<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OtpVerificationController extends Controller
{
    /**
     * Display the OTP verification view.
     */
    public function create(): View
    {
        return view('auth.verify-otp');
    }

    /**
     * Handle an incoming OTP verification request.
     */
    public function store(Request $request, OtpService $otpService)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $this->resolveUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        // Verify OTP (assuming registration purpose for initial verification)
        $result = $otpService->verify($user->email, $request->code, 'registration');

        if (! $result['success']) {
            throw ValidationException::withMessages([
                'code' => [$result['message']],
            ]);
        }

        // Mark user as verified
        $user->update([
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        
        // Login if not already logged in
        if (! Auth::check()) {
            Auth::login($user);
        }

        // Clear session
        $request->session()->forget('auth.otp.user_id');

        return redirect()->route('dashboard');
    }
    
    /**
     * Resend OTP
     */
    public function resend(Request $request, OtpService $otpService)
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return redirect()->route('login');
        }
        
        try {
            $otpService->generate($user, 'registration');
            return back()->with('status', 'OTP has been resent!');
        } catch (\Exception $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }
    }

    private function resolveUser(Request $request): ?User
    {
        if (Auth::check()) {
            return Auth::user();
        }
        
        $userId = $request->session()->get('auth.otp.user_id');
        if ($userId) {
            return User::find($userId);
        }
        
        return null;
    }
}
