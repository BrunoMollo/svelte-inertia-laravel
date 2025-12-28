<?php

use Illuminate\Support\Facades\Mail;

test('registration screen can be rendered', function () {
    /** @var \Pest\TestCase $this */
    $this->get('/register')->assertStatus(200);
});

test('new users can register', function () {
    /** @var \Pest\TestCase $this */
    Mail::fake();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);
});
