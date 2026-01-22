<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('start_time', microtime(true));
        $requestId = (string) Str::uuid();
        $request->attributes->set('request_id', $requestId);
        
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);
        
        return $response;
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        $startTime = $request->attributes->get('start_time', microtime(true));
        $duration = round((microtime(true) - $startTime) * 1000);
        $requestId = $request->attributes->get('request_id', (string) Str::uuid());

        try {
            $this->logRequest($request, $response, $duration, $requestId);
        } catch (\Exception $e) {
            Log::error('Failed to write api_log: ' . $e->getMessage());
        }
    }

    protected function logRequest(Request $request, Response $response, float $duration, string $requestId): void
    {
        $requestBody = $request->all();
        $this->maskPii($requestBody);

        $jsonRequestBody = json_encode($requestBody);
        if (strlen($jsonRequestBody) > 10000) {
            $jsonRequestBody = substr($jsonRequestBody, 0, 10000) . '... (truncated)';
        }

        $responseBody = $response->getContent();
        if (strlen($responseBody) > 10000) {
            $responseBody = substr($responseBody, 0, 10000) . '... (truncated)';
        }

        ApiLog::create([
            'request_id' => $requestId,
            'direction' => 'incoming',
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'duration_ms' => (int) $duration,
            'request_body' => $jsonRequestBody,
            'response_body' => $responseBody,
        ]);
    }

    protected function maskPii(array &$data): void
    {
        $sensitive = [
            'password', 
            'password_confirmation', 
            'national_id', 
            'passport_number', 
            'credit_card', 
            'cvv'
        ];

        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->maskPii($value);
            } elseif (in_array(strtolower($key), $sensitive)) {
                $value = '******';
            }
        }
    }
}
