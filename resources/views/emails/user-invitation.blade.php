<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Bienvenido a :app', ['app' => config('app.name')]) }}</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.5; padding: 20px; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #1a1a1a; font-size: 24px; margin-bottom: 20px;">
        {{ __('¡Bienvenido a :app!', ['app' => config('app.name')]) }}
    </h1>

    <p style="color: #4a4a4a; margin-bottom: 16px;">
        {{ __('Hola :name,', ['name' => $user->name]) }}
    </p>

    <p style="color: #4a4a4a; margin-bottom: 16px;">
        {{ __('Se ha creado una cuenta para ti. Por favor, haz clic en el botón de abajo para establecer tu contraseña y acceder a tu cuenta.') }}
    </p>

    <p style="margin: 30px 0;">
        <a href="{{ $resetUrl }}"
           style="background-color: #000; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
            {{ __('Establecer tu contraseña') }}
        </a>
    </p>

    <p style="color: #4a4a4a; margin-bottom: 16px;">
        {{ __('Este enlace expirará en :count minutos.', ['count' => config('auth.passwords.users.expire', 60)]) }}
    </p>

    <p style="color: #4a4a4a; margin-bottom: 16px;">
        {{ __('Si no esperabas recibir esta invitación, puedes ignorar este correo.') }}
    </p>

    <hr style="border: none; border-top: 1px solid #e5e5e5; margin: 30px 0;">

    <p style="color: #9a9a9a; font-size: 12px;">
        {{ __('Si tienes problemas al hacer clic en el botón, copia y pega la siguiente URL en tu navegador web:') }}
        <br>
        <a href="{{ $resetUrl }}" style="color: #6a6a6a;">{{ $resetUrl }}</a>
    </p>
</body>
</html>
