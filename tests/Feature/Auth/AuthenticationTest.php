<?php

use App\Models\User;

test('login screen can be rendered', function () {
    /** @var \Pest\TestCase $this */
    $this->get('/login')->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $response = $this
        ->from('/login')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $this->from('/login')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

    $this->assertGuest();
});

test('users can logout', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});
