<?php

test('registration screen redirects to login when public registration is disabled', function () {
    $response = $this->get('/register');

    $response->assertRedirect(route('login'));
});

test('registration submission redirects to login when public registration is disabled', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
