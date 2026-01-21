<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('asp ping endpoint requires authentication', function () {
    $this->getJson('/api/v1/asp/ping')
        ->assertUnauthorized();
});

test('asp ping endpoint rejects invalid token', function () {
    $this->withToken('invalid-token')
        ->getJson('/api/v1/asp/ping')
        ->assertUnauthorized();
});

test('asp ping endpoint rejects token without correct ability', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['other:ability']);

    $this->getJson('/api/v1/asp/ping')
        ->assertForbidden();
});

test('asp ping endpoint accepts valid token with ability', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['asp:sync']);

    $this->getJson('/api/v1/asp/ping')
        ->assertOk()
        ->assertJson(['message' => 'pong']);
});
