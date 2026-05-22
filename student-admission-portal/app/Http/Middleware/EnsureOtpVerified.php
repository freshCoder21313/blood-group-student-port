<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('auth.otp_enabled', true)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && ! $user->email_verified_at && ! $user->phone_verified_at) {
            return redirect()->route('otp.verify');
        }

        return $next($request);
    }
}
