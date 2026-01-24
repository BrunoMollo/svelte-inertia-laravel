<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;

class PasswordResetResponse implements PasswordResetResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        return redirect()->intended(
            match (true) {
                $user?->hasRole('superadmin') => route('superadmin.dashboard'),
                $user?->hasRole('teacher') => route('teacher.dashboard'),
                $user?->hasRole('student') => route('student.dashboard'),
                default => throw new \Exception('Pagina no desarollada aun'),
            }
        )->with('success', __('Contraseña restablecida exitosamente'));
    }
}
