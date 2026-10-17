<?php

test('reset password link screen redirects to login when public reset is disabled', function () {
    $response = $this->get('/forgot-password');

    $response->assertRedirect(route('login'));
});

test('reset password link request redirects to login when public reset is disabled', function () {
    $response = $this->post('/forgot-password', ['email' => 'test@example.com']);

    $response->assertRedirect(route('login'));
});

test('reset password screen redirects to login when public reset is disabled', function () {
    $response = $this->get('/reset-password/sample-token');

    $response->assertRedirect(route('login'));
});

test('password reset submission redirects to login when public reset is disabled', function () {
    $response = $this->post('/reset-password', [
        'token' => 'sample-token',
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('login'));
});
