<?php
declare(strict_types=1);

use App\Models\User;
use App\Services\Auth\OtpService;
use App\Services\Notifications\EmailChannel;
use App\Services\Notifications\SmsChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mock(EmailChannel::class)->shouldReceive('send')->andReturn(true);
    $this->mock(SmsChannel::class)->shouldReceive('send')->andReturn(true);
});

test('otp page can be rendered', function () {
    $user = User::factory()->create();
    $response = $this->withSession(['auth.otp.user_id' => $user->id])
                     ->get(route('otp.verify'));
                     
    $response->assertStatus(200);
});

test('user can verify otp and login', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    
    $service = app(OtpService::class);
    $service->generate($user, 'registration');
    $otp = $user->otps()->latest()->first();

    $response = $this->withSession(['auth.otp.user_id' => $user->id])
                     ->post(route('otp.verify'), [
                         'code' => $otp->otp_code,
                     ]);
                     
    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard'));
    $this->assertNotNull($user->fresh()->email_verified_at);
});

test('user invalid otp fails', function () {
    $user = User::factory()->create();
    $service = app(OtpService::class);
    $service->generate($user, 'registration');
    
    $response = $this->withSession(['auth.otp.user_id' => $user->id])
                     ->post(route('otp.verify'), [
                         'code' => '000000',
                     ]);
                     
    $this->assertGuest();
    $response->assertSessionHasErrors('code');
});

test('authenticated but unverified user can verify', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    $service = app(OtpService::class);
    $service->generate($user, 'registration'); 

    $otp = $user->otps()->latest()->first();

    $response = $this->actingAs($user)
                     ->post(route('otp.verify'), [
                         'code' => $otp->otp_code,
                     ]);
                     
    $response->assertRedirect(route('dashboard'));
    $this->assertNotNull($user->fresh()->email_verified_at);
});
