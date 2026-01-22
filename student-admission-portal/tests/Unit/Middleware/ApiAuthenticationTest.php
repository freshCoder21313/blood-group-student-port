<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\ApiAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Models\ApiLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private ApiAuthentication $middleware;
    private string $apiKey = 'test-api-key';
    private string $apiSecret = 'test-api-secret';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock configuration
        Config::set('services.asp.api_key', $this->apiKey);
        Config::set('services.asp.api_secret', $this->apiSecret);
        // Config::set('asp_integration.verify_signature', true); // Not used in middleware
        
        $this->middleware = new ApiAuthentication();
    }

    public function test_request_without_api_key_returns_401(): void
    {
        $request = Request::create('/api/v1/students', 'GET');
        
        $response = $this->middleware->handle($request, fn($r) => response('OK'));
        
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function test_request_with_valid_credentials_passes(): void
    {
        $timestamp = time();
        $payload = ''; // GET request has empty body
        $signature = hash_hmac('sha256', $timestamp . $payload, $this->apiSecret);

        $request = Request::create('/api/v1/students', 'GET');
        $request->headers->set('X-API-Key', $this->apiKey);
        $request->headers->set('X-Timestamp', (string) $timestamp);
        $request->headers->set('X-Signature', $signature);

        $response = $this->middleware->handle($request, fn($r) => response('OK'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_request_with_invalid_signature_returns_401(): void
    {
        $timestamp = time();
        
        $request = Request::create('/api/v1/students', 'GET');
        $request->headers->set('X-API-Key', $this->apiKey);
        $request->headers->set('X-Timestamp', (string) $timestamp);
        $request->headers->set('X-Signature', 'invalid-signature');

        $response = $this->middleware->handle($request, fn($r) => response('OK'));

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_request_expired_returns_401(): void
    {
        $expiredTimestamp = time() - 301; // 5 minutes 1 second ago
        
        $request = Request::create('/api/v1/students', 'GET');
        $request->headers->set('X-API-Key', $this->apiKey);
        $request->headers->set('X-Timestamp', (string) $expiredTimestamp);
        $request->headers->set('X-Signature', 'any'); // Fail at timestamp check first

        $response = $this->middleware->handle($request, fn($r) => response('OK'));

        $this->assertEquals(401, $response->getStatusCode());
    }
}
