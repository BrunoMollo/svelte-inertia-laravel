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
                default => throw new \Exception('Pagina no desarollada aun'),
            }
        );
    }
}
