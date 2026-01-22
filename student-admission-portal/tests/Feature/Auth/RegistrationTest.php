<?php
declare(strict_types=1);

use App\Services\Notifications\EmailChannel;
use App\Services\Notifications\SmsChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register and are redirected to otp verification', function () {
    $this->mock(EmailChannel::class)->shouldReceive('send');
    $this->mock(SmsChannel::class)->shouldReceive('send')->andReturn(true);

    $response = $this->post('/register', [
        'email' => 'test@example.com',
        'phone' => '0712345678',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('otp.verify'));
    
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    $this->assertDatabaseHas('otps', [
        'identifier' => 'test@example.com', 
        'purpose' => 'registration'
    ]);
});
