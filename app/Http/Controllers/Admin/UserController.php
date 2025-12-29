<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $users = User::query()
            ->with('roles')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Disable the specified user.
     */
    public function disable(Request $request, User $user): RedirectResponse
    {
        $user->disable();

        return redirect()->route('users.index')
            ->with('success', 'User disabled successfully.');
    }

    /**
     * Enable the specified user.
     */
    public function enable(Request $request, User $user): RedirectResponse
    {
        $user->enable();

        return redirect()->route('users.index')
            ->with('success', 'User enabled successfully.');
    }

    /**
     * Force password reset for the specified user.
     */
    public function forcePasswordReset(Request $request, User $user): RedirectResponse
    {
        // Generate a random password and set it
        $randomPassword = Str::random(32);
        $user->forceFill([
            'password' => Hash::make($randomPassword),
        ])->save();

        // Send password reset notification
        Password::sendResetLink(['email' => $user->email]);

        return redirect()->route('users.index')
            ->with('success', 'Password reset email sent successfully.');
    }
}
