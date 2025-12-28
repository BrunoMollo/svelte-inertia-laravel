<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can update their password with valid data', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->withHeaders(['Accept' => 'application/json'])
        ->put('/user/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertStatus(200);

    $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
});

test('user cannot update password with incorrect current password', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->withHeaders(['Accept' => 'application/json'])
        ->put('user/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertStatus(422)->assertJsonValidationErrors('current_password');
});

test('new password must be confirmed', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->withHeaders(['Accept' => 'application/json'])
        ->put('user/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

    $response->assertStatus(422)->assertJsonValidationErrors('password');
});

test('new password must be at least 8 characters', function () {
    /** @var \Pest\TestCase $this */
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->withHeaders(['Accept' => 'application/json'])
        ->put('/user/password', [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

    $response->assertStatus(422)->assertJsonValidationErrors('password');
});
