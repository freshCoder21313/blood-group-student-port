<?php
declare(strict_types=1);

use App\Models\User;
use App\Models\Otp;
use App\Services\Auth\OtpService;
use App\Services\Notifications\EmailChannel;
use App\Services\Notifications\SmsChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mock(EmailChannel::class);
    $this->mock(SmsChannel::class)->shouldReceive('send')->andReturn(true);
});

test('it can generate an otp for a user', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);
    
    $this->mock(EmailChannel::class)->shouldReceive('send')->once();

    $service = app(OtpService::class);
    $service->generate($user, 'registration');

    $this->assertDatabaseHas('otps', [
        'user_id' => $user->id,
        'identifier' => $user->email,
        'purpose' => 'registration',
        'type' => 'email',
        'attempts' => 0,
    ]);
});

test('it can verify a valid otp', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $service = app(OtpService::class);

    $otp = Otp::create([
        'user_id' => $user->id,
        'identifier' => $user->email,
        'otp_code' => '123456',
        'type' => 'email',
        'purpose' => 'registration',
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);

    $result = $service->verify($user->email, '123456', 'registration');

    expect($result['success'])->toBeTrue()
        ->and($result['code'])->toBe('VERIFIED');
        
    $this->assertNotNull($otp->fresh()->verified_at);
});

test('it fails verification with invalid code', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $service = app(OtpService::class);

    $otp = Otp::create([
        'user_id' => $user->id,
        'identifier' => $user->email,
        'otp_code' => '123456',
        'type' => 'email',
        'purpose' => 'registration',
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);

    $result = $service->verify($user->email, '000000', 'registration');

    expect($result['success'])->toBeFalse()
        ->and($result['code'])->toBe('INVALID_OTP');
        
    expect($otp->fresh()->attempts)->toBe(1);
    expect($otp->fresh()->verified_at)->toBeNull();
});

test('it invalidates otp after max attempts', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $service = app(OtpService::class);

    $otp = Otp::create([
        'user_id' => $user->id,
        'identifier' => $user->email,
        'otp_code' => '123456',
        'type' => 'email',
        'purpose' => 'registration',
        'expires_at' => now()->addMinutes(10),
        'attempts' => 3, 
    ]);

    $result = $service->verify($user->email, '123456', 'registration'); 

    expect($result['success'])->toBeFalse()
        ->and($result['code'])->toBe('MAX_ATTEMPTS_EXCEEDED');
        
    expect($otp->fresh()->verified_at)->not->toBeNull(); // Should be invalidated
});

test('it fails verification if otp expired', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $service = app(OtpService::class);

    Otp::create([
        'user_id' => $user->id,
        'identifier' => $user->email,
        'otp_code' => '123456',
        'type' => 'email',
        'purpose' => 'registration',
        'expires_at' => now()->subMinute(),
        'attempts' => 0,
    ]);

    $result = $service->verify($user->email, '123456', 'registration');

    expect($result['success'])->toBeFalse()
        ->and($result['code'])->toBe('OTP_EXPIRED');
});
