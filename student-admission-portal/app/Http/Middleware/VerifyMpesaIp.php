<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\IpUtils;

class VerifyMpesaIp
{
    private array $whitelistedIps = [
        '196.201.214.0/24',
        '196.201.213.0/24',
        '196.201.212.0/24',
        '196.201.211.0/24',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (config('mpesa.env') !== 'production') {
            return $next($request);
        }

        $ip = $request->ip();
        
        if (IpUtils::checkIp($ip, $this->whitelistedIps)) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
