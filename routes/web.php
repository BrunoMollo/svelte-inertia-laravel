<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Account\SessionController;
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
    Route::prefix('superamdin')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('superadmin.dashboard');
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

require __DIR__.'/auth.php';
