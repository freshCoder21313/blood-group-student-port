<?php

test('login page is accessible', function () {
    $response = $this->get('/login');
    
    $response->assertStatus(200);
    $response->assertSee('Log in');
});

test('register page is accessible', function () {
    $response = $this->get('/register');
    
    $response->assertStatus(200);
    $response->assertSee('Register');
});
