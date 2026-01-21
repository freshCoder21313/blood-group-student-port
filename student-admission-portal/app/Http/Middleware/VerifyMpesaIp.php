<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\IpUtils;

class VerifyMpesaIp
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('mpesa.env') !== 'production') {
            return $next($request);
        }

        $ip = $request->ip();
        $whitelistedIps = config('mpesa.whitelisted_ips', []);
        
        if (IpUtils::checkIp($ip, $whitelistedIps)) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
