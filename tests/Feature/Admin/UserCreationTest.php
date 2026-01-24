<?php

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'superadmin']);
    Role::create(['name' => 'teacher']);
    Role::create(['name' => 'student']);
});

describe('User Creation - Invitation Mode (Happy Path)', function () {
    test('superadmin can create a user with invitation email', function () {
        Mail::fake();

        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $response = $this->actingAs($superadmin)->post(route('superadmin.users.store'), [
            'name' => 'New Teacher',
            'email' => 'teacher@example.com',
            'role' => 'teacher',
            'mode' => 'invitation',
        ]);

        $response->assertRedirect(route('superadmin.users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'New Teacher',
            'email' => 'teacher@example.com',
        ]);

        $newUser = User::where('email', 'teacher@example.com')->first();
        expect($newUser->email_verified_at)->not->toBeNull();
        expect($newUser->hasRole('teacher'))->toBeTrue();

        Mail::assertSent(UserInvitationMail::class, function ($mail) use ($newUser) {
            return $mail->hasTo($newUser->email);
        });
    });

    test('invited user can set password and access teacher dashboard', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('teacher');

        $token = Password::broker()->createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));
        $response->assertStatus(200);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $this->assertAuthenticatedAs($user);
    });
});

describe('User Creation - Manual Password Mode (Happy Path)', function () {
    test('superadmin can create a user with manual password', function () {
        Notification::fake();

        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $response = $this->actingAs($superadmin)->post(route('superadmin.users.store'), [
            'name' => 'New Teacher',
            'email' => 'teacher@example.com',
            'role' => 'teacher',
            'mode' => 'manual',
        ]);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Create')
            ->has('generatedPassword')
            ->has('createdUser')
            ->where('createdUser.email', 'teacher@example.com')
        );

        $newUser = User::where('email', 'teacher@example.com')->first();
        expect($newUser)->not->toBeNull();
        expect($newUser->email_verified_at)->toBeNull();
        expect($newUser->hasRole('teacher'))->toBeTrue();

        Notification::assertSentTo($newUser, VerifyEmail::class);
    });

    test('manually created user can login and sees verification screen', function () {
        $user = User::factory()->unverified()->create([
            'password' => bcrypt('manualpassword123'),
        ]);
        $user->assignRole('teacher');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'manualpassword123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
    });

    test('manually created user can verify email and access dashboard', function () {
        $user = User::factory()->unverified()->create();
        $user->assignRole('teacher');

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    });
});

describe('Validation Rules', function () {
    test('email must be unique', function () {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($superadmin)->post(route('superadmin.users.store'), [
            'name' => 'Duplicate User',
            'email' => 'existing@example.com',
            'role' => 'teacher',
            'mode' => 'invitation',
        ]);

        $response->assertSessionHasErrors('email');
    });

    test('mode must be valid', function () {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $response = $this->actingAs($superadmin)->post(route('superadmin.users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'teacher',
            'mode' => 'invalid_mode',
        ]);

        $response->assertSessionHasErrors('mode');
    });
});
