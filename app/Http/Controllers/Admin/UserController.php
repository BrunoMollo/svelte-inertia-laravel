<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->with('roles');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($request->input('status') === 'disabled') {
            $query->whereNotNull('disabled_at');
        } elseif ($request->input('status') === 'active') {
            $query->whereNull('disabled_at');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => Role::all(),
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
            ]);

            $user->assignRole($request->role);

            $token = Password::broker()->createToken($user);
            $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));

            Mail::to($user)->send(new UserInvitationMail($user, $resetUrl));

            return $user;
        });

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', __('User created successfully. An invitation email has been sent.'));
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $user->syncRoles([$request->role]);

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', __('User deleted successfully.'));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);

        if ($user->isDisabled()) {
            $user->enable();
            $message = __('User enabled successfully.');
        } else {
            $user->disable();
            $message = __('User disabled successfully.');
        }

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', $message);
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        Password::sendResetLink(['email' => $user->email]);

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', __('Password reset email sent successfully.'));
    }
}
