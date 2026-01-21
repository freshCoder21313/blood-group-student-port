<?php

use App\Services\Payment\MpesaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('mpesa service can be instantiated', function () {
    $service = new MpesaService();
    expect($service)->toBeInstanceOf(MpesaService::class);
});

test('mpesa service has required methods', function () {
    $service = new MpesaService();
    expect(method_exists($service, 'initiateStkPush'))->toBeTrue();
    expect(method_exists($service, 'processCallback'))->toBeTrue();
});

test('mpesa service initiates stk push', function () {
    config(['mpesa.consumer_key' => 'test_key']);
    config(['mpesa.consumer_secret' => 'test_secret']);
    config(['mpesa.shortcode' => '174379']);
    config(['mpesa.passkey' => 'test_passkey']);
    config(['mpesa.callback_url' => 'http://test.com/callback']);
    config(['mpesa.env' => 'sandbox']);

    Http::fake([
        '*/oauth/v1/generate?grant_type=client_credentials' => Http::response(['access_token' => 'mock_token', 'expires_in' => 3599]),
        '*/mpesa/stkpush/v1/processrequest' => Http::response([
            'MerchantRequestID' => '1234',
            'CheckoutRequestID' => '5678',
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success',
            'CustomerMessage' => 'Success',
        ]),
    ]);

    $service = new MpesaService();
    $response = $service->initiateStkPush('254700000000', 100, 'TEST1234');

    expect($response)->toBeArray();
    expect($response['MerchantRequestID'])->toBe('1234');
});
