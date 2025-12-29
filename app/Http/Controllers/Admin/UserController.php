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
        $query = User::query()->with('roles');

        // Filter by search (searches across searchable columns)
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->input('role'));
            });
        }

        // Filter by status (active/disabled)
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->whereNull('disabled_at');
            } elseif ($request->input('status') === 'disabled') {
                $query->whereNotNull('disabled_at');
            }
        }

        // Get per_page value, validate it's one of the allowed values
        $perPage = $request->input('per_page', 25);
        $allowedPerPage = [10, 25, 50, 100];
        if (! in_array((int) $perPage, $allowedPerPage, true)) {
            $perPage = 25;
        }

        $users = $query->latest()->paginate((int) $perPage)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status', 'per_page']),
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

        return redirect($request->header('Referer') ?? route('admin.users.index'))
            ->with('success', 'User disabled successfully.');
    }

    /**
     * Enable the specified user.
     */
    public function enable(Request $request, User $user): RedirectResponse
    {
        $user->enable();

        return redirect($request->header('Referer') ?? route('admin.users.index'))
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

        return redirect($request->header('Referer') ?? route('admin.users.index'))
            ->with('success', 'Password reset email sent successfully.');
    }
}
