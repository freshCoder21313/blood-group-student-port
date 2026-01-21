<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\PersonalAccessToken;

test('asp:create-token command creates user and token with correct abilities', function () {
    $email = 'asp-system@example.com';
    
    $this->artisan('asp:create-token', ['email' => $email])
        ->assertExitCode(0)
        ->expectsOutputToContain('Token:');

    $user = User::where('email', $email)->first();
    $this->assertNotNull($user);
    
    // Check if token exists
    $this->assertCount(1, $user->tokens);
    $token = $user->tokens->first();
    
    $this->assertTrue($token->can('asp:sync'));
    $this->assertFalse($token->can('other:ability'));
});
