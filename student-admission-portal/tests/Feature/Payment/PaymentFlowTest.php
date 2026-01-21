<?php

use App\Models\Application;
use App\Models\User;
use App\Models\Payment;
use App\Services\Payment\MpesaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

use App\Models\Student;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->student = Student::factory()->create(['user_id' => $this->user->id]);
    $this->application = Application::factory()->create(['student_id' => $this->student->id]);
});

test('user can initiate payment', function () {
    $this->mock(MpesaService::class, function (MockInterface $mock) {
        $mock->shouldReceive('initiateStkPush')
            ->once()
            ->andReturn([
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success',
                'MerchantRequestID' => '1234',
                'CheckoutRequestID' => '5678',
            ]);
    });

    actingAs($this->user)
        ->postJson(route('payment.initiate', $this->application), [
            'phone_number' => '0700000000',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    // Assert Payment record created
    $this->assertDatabaseHas('payments', [
        'application_id' => $this->application->id,
        'checkout_request_id' => '5678',
        'status' => 'pending',
    ]);
});

test('user can check payment status', function () {
    $payment = Payment::factory()->create([
        'application_id' => $this->application->id,
        'status' => 'pending',
    ]);

    actingAs($this->user)
        ->getJson(route('payment.status', $this->application))
        ->assertOk()
        ->assertJson(['status' => 'pending']);

    $payment->update(['status' => 'completed']);

    actingAs($this->user)
        ->getJson(route('payment.status', $this->application))
        ->assertOk()
        ->assertJson(['status' => 'completed']);
});

test('mpesa callback updates payment status', function () {
    $payment = Payment::factory()->create([
        'application_id' => $this->application->id,
        'status' => 'pending',
        'checkout_request_id' => 'req_123',
    ]);

    $callbackData = [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => 'mer_123',
                'CheckoutRequestID' => 'req_123',
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 100],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'RC123'],
                    ]
                ]
            ]
        ]
    ];

    // Assuming we use the real service logic in callback or mock processCallback
    // Since callback calls processCallback, we can verify DB update if we use real service or verify method call if mocked.
    // Let's use real service logic test here, or partial mock.
    // But this is Feature test, so integration.
    
    // We need to bypass CSRF for callback
    postJson(route('payment.callback'), $callbackData)
        ->assertOk();

    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'status' => 'completed',
        'transaction_code' => 'RC123',
    ]);
});
