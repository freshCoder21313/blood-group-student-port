<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AspApiService
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('asp_integration.base_url', '');
        $this->apiKey = config('asp_integration.api_key', '');
        $this->apiSecret = config('asp_integration.api_secret', '');
        $this->timeout = config('asp_integration.timeout', 30);
    }

    /**
     * Lấy điểm sinh viên từ ASP
     */
    public function getStudentGrades(string $studentCode): array
    {
        $cacheKey = "grades:{$studentCode}";
        
        return Cache::remember($cacheKey, 300, function () use ($studentCode) {
            $response = $this->makeRequest('GET', "/students/{$studentCode}/grades");
            return $response['data'] ?? [];
        });
    }

    /**
     * Lấy thời khóa biểu từ ASP
     */
    public function getStudentTimetable(string $studentCode): array
    {
        $cacheKey = "timetable:{$studentCode}";
        
        return Cache::remember($cacheKey, 600, function () use ($studentCode) {
            $response = $this->makeRequest('GET', "/students/{$studentCode}/timetable");
            return $response['data'] ?? [];
        });
    }

    /**
     * Lấy công nợ học phí từ ASP
     */
    public function getStudentFees(string $studentCode): array
    {
        $cacheKey = "fees:{$studentCode}";
        
        return Cache::remember($cacheKey, 300, function () use ($studentCode) {
            $response = $this->makeRequest('GET', "/students/{$studentCode}/fees");
            return $response['data'] ?? [];
        });
    }

    /**
     * Thực hiện request đến ASP API
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $timestamp = time();
        $url = rtrim($this->baseUrl, '/') . $endpoint;

        try {
            $response = Http::withHeaders($this->buildHeaders($timestamp, $data))
                           ->timeout($this->timeout)
                           ->$method($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('ASP API request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'API request failed',
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('ASP API connection error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Connection failed',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Xây dựng headers cho request
     */
    private function buildHeaders(int $timestamp, array $data): array
    {
        $payload = json_encode($data);
        $signature = hash_hmac('sha256', $timestamp . $payload, $this->apiSecret);

        return [
            'X-API-Key' => $this->apiKey,
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }
}
