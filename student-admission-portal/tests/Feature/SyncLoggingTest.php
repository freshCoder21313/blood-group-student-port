<?php

use Illuminate\Support\Facades\Schema;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Route;

test('api_logs table has required columns', function () {
    expect(Schema::hasTable('api_logs'))->toBeTrue();
    
    $columns = [
        'ip_address',
        'method',
        'endpoint',
        'status_code',
        'duration_ms',
        'request_body',
        'response_body',
    ];

    foreach ($columns as $column) {
        expect(Schema::hasColumn('api_logs', $column))
            ->toBeTrue("Column '{$column}' is missing in api_logs table");
    }
});

test('status_histories table has required columns', function () {
    expect(Schema::hasTable('status_histories'))->toBeTrue();

    $columns = [
        'application_id',
        'from_status',
        'to_status',
        'comment', 
    ];

    foreach ($columns as $column) {
        expect(Schema::hasColumn('status_histories', $column))
            ->toBeTrue("Column '{$column}' is missing in status_histories table");
    }
});

test('api requests are logged by middleware', function () {
    // Authenticate as a user with ability
    $user = \App\Models\User::factory()->create();
    \Laravel\Sanctum\Sanctum::actingAs($user, ['asp:sync']);

    // We use the ping endpoint which doesn't have manual logging in the controller
    // but should be covered by the new middleware
    $response = $this->getJson('/api/v1/asp/ping');
    
    $response->assertOk();

    // Assert log was created
    $log = ApiLog::latest()->first();
    
    expect($log)->not->toBeNull()
        ->and($log->endpoint)->toBe('api/v1/asp/ping')
        ->and($log->method)->toBe('GET')
        ->and($log->status_code)->toBe(200)
        ->and($log->duration_ms)->not->toBeNull();
});

test('api requests with auth failure are logged', function () {
    // Request without auth
    $response = $this->getJson('/api/v1/asp/ping');
    
    $response->assertUnauthorized();

    // Assert log was created
    $log = ApiLog::latest()->first();
    
    expect($log)->not->toBeNull()
        ->and($log->endpoint)->toBe('api/v1/asp/ping')
        ->and($log->status_code)->toBe(401);
});

test('api requests with server error are logged', function () {
    Route::get('/api/test/error', function () {
        throw new \Exception('Test Server Error');
    })->middleware(\App\Http\Middleware\LogApiRequests::class);

    $response = $this->getJson('/api/test/error');
    
    $response->assertStatus(500);

    $log = ApiLog::latest()->first();
    
    expect($log)->not->toBeNull()
        ->and($log->endpoint)->toBe('api/test/error')
        ->and($log->status_code)->toBe(500);
});

test('pii sensitive data is masked in logs', function () {
    // Authenticate
    $user = \App\Models\User::factory()->create();
    \Laravel\Sanctum\Sanctum::actingAs($user, ['asp:sync']);

    // Send request with sensitive data (using a route that accepts POST, or a dummy one)
    // We can use the status update endpoint which accepts a body
    // Even if validation fails, middleware logs the request *before* validation? 
    // Wait, middleware runs, then controller. If validation fails, it's a 422 response.
    // The logs capture the request body in terminate().
    
    $payload = [
        'application_id' => 999,
        'status' => 'approved',
        'comment' => 'Test',
        'national_id' => '12345678', // Sensitive
        'password' => 'secret123',   // Sensitive
        'nested' => [
            'passport_number' => 'A1234567' // Sensitive nested
        ]
    ];

    $response = $this->postJson('/api/v1/sync/status', $payload);

    // It might return 404 or 422 depending on if ID 999 exists, but logging happens regardless
    
    $log = ApiLog::latest()->first();
    
    expect($log)->not->toBeNull();
    
    $body = json_decode($log->request_body, true);
    
    expect($body['national_id'])->toBe('******')
        ->and($body['password'])->toBe('******')
        ->and($body['nested']['passport_number'])->toBe('******')
        ->and($body['comment'])->toBe('Test'); // Non-sensitive preserved
});
