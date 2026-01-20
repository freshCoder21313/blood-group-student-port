<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Integration\AspApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AspApiServiceTest extends TestCase
{
    private AspApiService $service;
    private string $baseUrl = 'http://test-asp.com/api';
    private string $apiKey = 'test-key';
    private string $apiSecret = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        // Config Mocking
        Config::set('asp_integration.base_url', $this->baseUrl);
        Config::set('asp_integration.api_key', $this->apiKey);
        Config::set('asp_integration.api_secret', $this->apiSecret);
        Config::set('asp_integration.timeout', 5);

        $this->service = new AspApiService();
    }

    public function test_get_student_grades_success()
    {
        // Mock HTTP Response
        Http::fake([
            $this->baseUrl . '/students/STU001/grades' => Http::response([
                'success' => true,
                'data' => [
                    ['subject' => 'Math', 'score' => 9.0],
                    ['subject' => 'Physics', 'score' => 8.5]
                ]
            ], 200),
        ]);

        $grades = $this->service->getStudentGrades('STU001');

        $this->assertCount(2, $grades);
        $this->assertEquals('Math', $grades[0]['subject']);
        $this->assertEquals(9.0, $grades[0]['score']);
    }

    public function test_get_student_grades_caches_result()
    {
        Http::fake([
            '*' => Http::response(['data' => []], 200),
        ]);

        // Call twice
        $this->service->getStudentGrades('STU002');
        $this->service->getStudentGrades('STU002');

        // Assert HTTP sent only once
        Http::assertSentCount(1);
    }

    public function test_api_request_includes_auth_headers()
    {
        Http::fake(function ($request) {
            return Http::response(['data' => []], 200);
        });

        $this->service->getStudentGrades('STU003');

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key', $this->apiKey) &&
                   $request->hasHeader('X-Timestamp') &&
                   $request->hasHeader('X-Signature');
        });
    }

    public function test_api_request_handles_error_gracefully()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Not Found'], 404),
        ]);

        $result = $this->service->getStudentGrades('STU_INVALID');

        // Logic hiện tại trả về mảng rỗng khi không có data hoặc lỗi
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
