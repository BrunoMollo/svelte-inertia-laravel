<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Account\SessionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    \Log::info('Welcome to your Laravel application!');
    return Inertia::render('Public/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth', 'verified')->group(function () {
    Route::prefix('superadmin')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('superadmin.dashboard');

        // Users management
        Route::get('/users', [UserController::class, 'index'])->name('superadmin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('superadmin.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('superadmin.users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('superadmin.users.edit');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('superadmin.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('superadmin.users.destroy');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('superadmin.users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('superadmin.users.reset-password');
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
