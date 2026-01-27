<?php

declare(strict_types=1);

namespace App\Services\Student;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AspStudentInformationService implements StudentInformationServiceInterface
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.asp.base_url', 'http://localhost:5000/api');
        $this->apiKey = config('services.asp.api_key', '');
    }

    public function getGrades(string $studentCode): array
    {
        return $this->fetchData("students/{$studentCode}/grades", []);
    }

    public function getSchedule(string $studentCode): array
    {
        return $this->fetchData("students/{$studentCode}/schedule", []);
    }

    public function getFees(string $studentCode): array
    {
        return $this->fetchData("students/{$studentCode}/fees", [
            'balance' => 0,
            'currency' => 'KES',
            'status' => 'Unknown',
            'invoice_history' => []
        ]);
    }

    private function fetchData(string $endpoint, array $default)
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['X-API-Key' => $this->apiKey])
                ->get("{$this->baseUrl}/{$endpoint}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("ASP API Error for {$endpoint}: " . $response->status());
            return $default;
        } catch (\Exception $e) {
            Log::error("ASP API Connection Failed for {$endpoint}: " . $e->getMessage());
            return $default;
        }
    }
}
