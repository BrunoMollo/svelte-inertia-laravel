<?php

use App\Models\User;
use App\Notifications\PasswordChangedByAdmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

test('superadmin can change a user password and user is notified', function () {
    Role::firstOrCreate(['name' => 'superadmin']);
    Role::firstOrCreate(['name' => 'student']);

    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('superadmin');

    $user = User::factory()->create();
    $user->assignRole('student');

    $response = $this
        ->actingAs($admin)
        ->post("/admin/users/{$user->id}/change-password", [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

    $response->assertRedirect();
    $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));

    Notification::assertSentTo($user, PasswordChangedByAdmin::class, function (PasswordChangedByAdmin $notification) use ($admin) {
        return $notification->adminUser->is($admin);
    });
});

test('non-superadmin cannot change a user password', function () {
    Role::firstOrCreate(['name' => 'student']);

    Notification::fake();

    $student = User::factory()->create();
    $student->assignRole('student');

    $user = User::factory()->create();
    $user->assignRole('student');

    $response = $this
        ->actingAs($student)
        ->post("/admin/users/{$user->id}/change-password", [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

    $response->assertForbidden();
    Notification::assertNothingSent();
});

test('admin change password requires confirmed password input', function () {
    Role::firstOrCreate(['name' => 'superadmin']);
    Role::firstOrCreate(['name' => 'student']);

    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('superadmin');

    $user = User::factory()->create();
    $user->assignRole('student');

    $response = $this
        ->actingAs($admin)
        ->from('/admin/users')
        ->post("/admin/users/{$user->id}/change-password", [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'does-not-match',
        ]);

    $response->assertSessionHasErrors(['password']);
    Notification::assertNothingSent();
});
