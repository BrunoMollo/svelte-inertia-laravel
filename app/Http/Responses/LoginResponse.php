<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        return redirect()->intended(
            match (true) {
                $user->hasRole('superadmin') => route('superadmin.dashboard'),
                $user->hasRole('teacher') => route('teacher.dashboard'),
                $user->hasRole('student') => route('student.dashboard'),
                default => throw new \Exception('Pagina no desarollada aun'),
            }
        );
    }
}
