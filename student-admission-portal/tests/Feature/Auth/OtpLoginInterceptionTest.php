<?php
declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unverified user is redirected to otp verification when accessing protected route', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
        'phone_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('otp.verify'));
});

test('verified user can access protected route', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200); 
});
