<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

test('callback rejects invalid ip in production', function () {
    Config::set('mpesa.env', 'production');
    
    // We need to apply the middleware to the route for this test
    // Or assume the route uses the middleware.
    // Let's modify the route in the test to use the middleware, or check if the controller uses it.
    
    // Better: Test the Middleware unit-wise or integration-wise.
    // Let's test the endpoint assuming middleware is applied.
    // I need to apply middleware in routes/api.php.
    
    $response = $this->postJson(route('payment.callback'), [], ['REMOTE_ADDR' => '1.2.3.4']);
    $response->assertStatus(403);
});

test('callback accepts valid ip in production', function () {
    Config::set('mpesa.env', 'production');
    
    $response = $this->postJson(route('payment.callback'), [], ['REMOTE_ADDR' => '196.201.214.5']);
    // It might fail validation or logic, but not 403.
    // Since payload is empty, it might be 200 (log error) or 500.
    // My controller logs error and returns 'ok' 200 even if invalid body.
    $response->assertStatus(200);
});

test('callback accepts any ip in sandbox', function () {
    Config::set('mpesa.env', 'sandbox');
    
    $response = $this->postJson(route('payment.callback'), [], ['REMOTE_ADDR' => '1.2.3.4']);
    $response->assertStatus(200);
});
