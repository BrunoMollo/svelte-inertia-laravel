<?php

use App\Models\User;
use App\Notifications\PasswordChangedByAdmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin']);
    Role::firstOrCreate(['name' => 'teacher']);
    Role::firstOrCreate(['name' => 'student']);
});

test('superadmin can change another users password and the user is notified', function () {
    Notification::fake();

    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);
    $user->assignRole('teacher');

    $response = $this
        ->actingAs($superadmin)
        ->from('/admin/users')
        ->post("/admin/users/{$user->id}/change-password", [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/admin/users');

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();

    Notification::assertSentTo(
        $user,
        PasswordChangedByAdmin::class,
        fn (PasswordChangedByAdmin $notification) => $notification->adminUser->is($superadmin)
    );
});

test('non-superadmin cannot change another users password', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);
    $user->assignRole('student');

    $this
        ->actingAs($teacher)
        ->post("/admin/users/{$user->id}/change-password", [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertForbidden();
});

test('password confirmation is required when changing a users password', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('superadmin');

    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);
    $user->assignRole('teacher');

    $response = $this
        ->actingAs($superadmin)
        ->from('/admin/users')
        ->post("/admin/users/{$user->id}/change-password", [
            'password' => 'new-password-123',
            'password_confirmation' => 'different-password',
        ]);

    $response->assertSessionHasErrors('password');
    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});
