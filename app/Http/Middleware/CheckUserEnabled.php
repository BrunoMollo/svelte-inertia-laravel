<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && $request->user()->isDisabled()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => __('Your account has been disabled.'),
            ]);
        }

        return $next($request);
    }
}
