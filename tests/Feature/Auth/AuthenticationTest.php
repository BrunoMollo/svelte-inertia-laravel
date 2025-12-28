<?php

use App\Models\User;


test('login screen can be rendered', function () {
    /** @var \Pest\TestCase $this */
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $this->get('/login'); // boot session + token

    $response = $this->post('/login', [
        '_token' => csrf_token(),
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $this->get('/login'); // boot session + token

    $this->post('/login', [
        '_token' => csrf_token(),
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $this->get('/login'); // boot session + token

    $response = $this->post('/login', [
        '_token' => csrf_token(),
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = $this->actingAs($user)->post('/logout', [
        '_token' => csrf_token(),
    ]);

    $response->assertRedirect('/');
    $this->assertGuest();
});
