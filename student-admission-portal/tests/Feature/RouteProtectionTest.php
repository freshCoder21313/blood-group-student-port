<?php

test('legacy api auth routes are accessible', function () {
    // Verify /api/verify-otp exists
    $response = $this->postJson('/api/verify-otp');
    $this->assertNotEquals(404, $response->status(), 'Route /api/verify-otp should exist');

    // Verify /api/register exists
    $response = $this->postJson('/api/register');
    $this->assertNotEquals(404, $response->status(), 'Route /api/register should exist');

    // Verify /api/login exists
    $response = $this->postJson('/api/login');
    $this->assertNotEquals(404, $response->status(), 'Route /api/login should exist');
});
