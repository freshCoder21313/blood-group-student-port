<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Models\Payment;
use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ProductionModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Config for Testing
        Config::set('services.mpesa.consumer_key', 'test_key');
        Config::set('services.mpesa.consumer_secret', 'test_secret');
        Config::set('services.mpesa.environment', 'sandbox');
        Config::set('services.asp.api_key', 'test_asp_key');
        Config::set('services.asp.api_secret', 'test_asp_secret');
    }

    /** @test */
    public function it_can_send_and_verify_otp_via_sms()
    {
        // 1. Test Send OTP
        $response = $this->postJson('/api/auth/otp/send', [
            'identifier' => '+84909000111' // Phone number
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify DB has OTP
        $this->assertDatabaseHas('otps', [
            'identifier' => '+84909000111',
            'type' => 'sms'
        ]);

        $otp = Otp::where('identifier', '+84909000111')->first();

        // 2. Test Verify OTP
        $verifyResponse = $this->postJson('/api/auth/otp/verify', [
            'identifier' => '+84909000111',
            'otp_code' => $otp->otp_code
        ]);

        $verifyResponse->assertStatus(200)
                       ->assertJson(['success' => true]);
                       
        $this->assertNotNull($otp->fresh()->verified_at);
    }

    /** @test */
    public function it_initiates_mpesa_payment_correctly()
    {
        // Mock M-Pesa API Response
        Http::fake([
            'sandbox.safaricom.co.ke/*' => Http::response([
                'CheckoutRequestID' => 'ws_CO_123456789',
                'MerchantRequestID' => '12345-67890',
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
                'CustomerMessage' => 'Success. Request accepted for processing'
            ], 200),
            'oauth/*' => Http::response(['access_token' => 'mock_token'], 200)
        ]);

        // Create dummy application
        $user = User::factory()->create();
        $app = Application::create([
            'user_id' => $user->id, 
            'status' => 'pending_payment',
            'course_id' => 1 // Assuming minimal fields
        ]);

        // Call API
        $response = $this->postJson('/api/v1/payments/initiate', [
            'application_id' => $app->id,
            'phone_number' => '254700000000'
        ]);

        // Assertions
        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        // Check DB
        $this->assertDatabaseHas('payments', [
            'application_id' => $app->id,
            'checkout_request_id' => 'ws_CO_123456789',
            'status' => 'pending',
            'payment_method' => 'mpesa'
        ]);
    }

    /** @test */
    public function asp_middleware_blocks_invalid_signature()
    {
        // Call health check protected by middleware (assuming we protect a route)
        // Or use the student list route
        
        $response = $this->getJson('/api/v1/students', [
            'X-API-Key' => 'test_asp_key',
            'X-Timestamp' => time(),
            'X-Signature' => 'invalid_signature'
        ]);

        $response->assertStatus(401)
                 ->assertJsonPath('message', 'Invalid signature');
    }

    /** @test */
    public function asp_middleware_accepts_valid_signature()
    {
        // Mock DB Data for student list
        $user = User::factory()->create();
        Application::create(['user_id' => $user->id, 'status' => 'submitted']);

        // Prepare Headers
        $timestamp = time();
        $apiKey = 'test_asp_key';
        $apiSecret = 'test_asp_secret';
        $payload = ''; // GET request has empty body
        
        $signature = hash_hmac('sha256', $payload . $timestamp, $apiSecret);

        $response = $this->getJson('/api/v1/students', [
            'X-API-Key' => $apiKey,
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature
        ]);

        // Should pass auth (might fail later on Controller logic if DB empty, but Auth should pass)
        // If Auth fails, it's 401. If Auth passes, it's 200 or 500 (logic error).
        // We expect NOT 401.
        $this->assertNotEquals(401, $response->status());
        
        // Check if Log created
        $this->assertDatabaseHas('api_logs', [
            'direction' => 'incoming',
            'status_code' => $response->status()
        ]);
    }
}
