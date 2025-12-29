<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Account\SessionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Public/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth', 'verified')->group(function () {
    // General dashboard route (redirects based on role)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user?->hasRole('superadmin')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    Route::prefix('admin')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');

        // User Management
        Route::resource('users', UserController::class)->only(['index', 'store']);
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::post('/users/{user}/disable', [UserController::class, 'disable'])->name('admin.users.disable');
        Route::post('/users/{user}/enable', [UserController::class, 'enable'])->name('admin.users.enable');
        Route::post('/users/{user}/force-password-reset', [UserController::class, 'forcePasswordReset'])->name('admin.users.force-password-reset');
    });

    // Profile
    Route::get('/account/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/account/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/account/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Security
    Route::get('/account/security', [SecurityController::class, 'show'])->name('security.show');

    // Sessions
    Route::delete('/account/sessions/other-browser-sessions', [SessionController::class, 'destroyOtherSessions'])->name('session.destroyOtherSessions');
    Route::delete('/account/sessions/browser-sessions/{id}', [SessionController::class, 'destroySession'])->name('session.destroySession');
});

require __DIR__ . '/auth.php';
