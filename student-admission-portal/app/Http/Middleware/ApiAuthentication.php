<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthentication
{
    private const TIMESTAMP_TOLERANCE_SECONDS = 300; // 5 minutes

    public function handle(Request $request, Closure $next): Response
    {
        $requestId = uniqid('in_', true);
        $startTime = microtime(true);

        try {
            // Validate required headers
            $apiKey = $request->header('X-API-Key');
            $timestamp = $request->header('X-Timestamp');
            $signature = $request->header('X-Signature');

            if (!$apiKey || !$timestamp || !$signature) {
                return $this->unauthorizedResponse('Missing authentication headers', $requestId);
            }

            // Validate API key
            if ($apiKey !== config('services.asp.api_key')) {
                return $this->unauthorizedResponse('Invalid API key', $requestId);
            }

            // Validate timestamp (prevent replay attacks)
            $timestampInt = (int) $timestamp;
            $currentTime = time();
            
            if (abs($currentTime - $timestampInt) > self::TIMESTAMP_TOLERANCE_SECONDS) {
                return $this->unauthorizedResponse('Request timestamp expired', $requestId);
            }

            // Validate signature
            $payload = $request->getContent();
            $expectedSignature = hash_hmac(
                'sha256',
                $payload . $timestamp,
                config('services.asp.api_secret')
            );

            if (!hash_equals($expectedSignature, $signature)) {
                return $this->unauthorizedResponse('Invalid signature', $requestId);
            }

            // Add request ID to request for tracking
            $request->attributes->set('api_request_id', $requestId);

            // Process request
            $response = $next($request);

            // Log successful request
            $this->logRequest($requestId, $request, $response, null, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('API Authentication error', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);

            return $this->unauthorizedResponse('Authentication error', $requestId);
        }
    }

    private function unauthorizedResponse(string $message, string $requestId): Response
    {
        Log::warning('API authentication failed', [
            'request_id' => $requestId,
            'message' => $message,
            'ip' => request()->ip()
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
            'request_id' => $requestId
        ], 401);
    }

    private function logRequest(
        string $requestId,
        Request $request,
        Response $response,
        ?\Exception $error,
        float $startTime
    ): void {
        try {
            ApiLog::create([
                'request_id' => $requestId,
                'direction' => 'incoming',
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'request_body' => $this->sanitizeRequestData($request->all()),
                'response_body' => json_decode($response->getContent(), true),
                'status_code' => $response->getStatusCode(),
                'error_message' => $error?->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Prevent logging failure from blocking the response
            Log::error('Failed to write ApiLog', ['error' => $e->getMessage()]);
        }
    }

    private function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = ['password', 'secret', 'id_number', 'phone'];

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveFields) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $value = '[REDACTED]';
            }
        });

        return $data;
    }
}
