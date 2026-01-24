# Plan: Implementación de Creación de Usuarios con Dos Modos

## Resumen

Extender la funcionalidad de creación de usuarios en `/superadmin/users/create` para soportar dos modos:

1. **Invitación por email** (actual): Envía email con link para definir contraseña
2. **Contraseña manual** (nuevo): Genera contraseña visible una vez, usuario debe verificar email

---

## Archivos a Modificar

| Archivo                                         | Acción                  |
| ----------------------------------------------- | ----------------------- |
| `resources/js/lib/components/ui/tabs/*`         | Crear (instalar shadcn) |
| `app/Rules/SecurePassword.php`                  | Crear                   |
| `app/Http/Requests/StoreUserRequest.php`        | Modificar               |
| `app/Http/Controllers/Admin/UserController.php` | Modificar               |
| `resources/js/Pages/Admin/Users/Create.svelte`  | Modificar               |
| `resources/js/lib/i18n/locales/en.json`         | Modificar               |
| `lang/es/messages.php`                          | Modificar               |
| `lang/en/messages.php`                          | Modificar               |
| `tests/Feature/Admin/UserCreationTest.php`      | Crear                   |

---

## Pasos de Implementación

### 1. Instalar componente Tabs de shadcn-svelte

```bash
pnpm dlx shadcn-svelte@latest add tabs
```

### 2. Crear Rule de contraseña segura

**Archivo:** `app/Rules/SecurePassword.php`

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SecurePassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 8) {
            $fail(__('La contraseña debe tener al menos 8 caracteres.'));
        }
    }
}
```

### 3. Actualizar StoreUserRequest

Agregar validación del campo `mode`:

```php
'mode' => ['sometimes', 'string', Rule::in(['invitation', 'manual'])],
```

### 4. Modificar UserController

**Método `create()`** - Pasar el modo desde query param:

```php
public function create(Request $request): Response
{
    $this->authorize('create', User::class);

    return Inertia::render('Admin/Users/Create', [
        'roles' => Role::all(),
        'mode' => $request->query('mode', 'invitation'),
    ]);
}
```

**Método `store()`** - Bifurcar según modo:

```php
public function store(StoreUserRequest $request): RedirectResponse|Response
{
    $mode = $request->input('mode', 'invitation');

    if ($mode === 'manual') {
        return $this->storeWithManualPassword($request);
    }

    return $this->storeWithInvitation($request);
}
```

**Nuevo método `storeWithInvitation()`** - Mover lógica actual aquí

**Nuevo método `storeWithManualPassword()`**:

- Generar contraseña segura aleatoria (14 chars, mayúsculas, minúsculas, números, símbolos)
- Crear usuario con `email_verified_at = null`
- Enviar notificación de verificación de email (`$user->sendEmailVerificationNotification()`)
- Retornar `Inertia::render()` con la contraseña generada

**Nuevo método privado `generateSecurePassword()`**:

- Garantizar al menos 1 mayúscula, 1 minúscula, 1 número, 1 símbolo
- Mezclar aleatoriamente

### 5. Modificar Create.svelte

**Cambios principales:**

- Importar componente Tabs
- Agregar props: `mode`, `generatedPassword`, `createdUser`
- Usar `$state` para `currentMode`, inicializado desde prop
- Al cambiar tab, actualizar URL con `router.get()` y `preserveState: true`
- Agregar `mode` al formulario
- Agregar Dialog/Modal para mostrar contraseña generada
- Botón de copiar contraseña con feedback visual

**Estructura de Tabs:**

```svelte
<Tabs.Root value={currentMode} onValueChange={handleTabChange}>
    <Tabs.List class="grid w-full grid-cols-2 mb-6">
        <Tabs.Trigger value="invitation">Invitación por email</Tabs.Trigger>
        <Tabs.Trigger value="manual">Contraseña manual</Tabs.Trigger>
    </Tabs.List>
    <Tabs.Content value="invitation">...</Tabs.Content>
    <Tabs.Content value="manual">...</Tabs.Content>
</Tabs.Root>
```

**Modal de contraseña:**

- Mostrar cuando `generatedPassword` tiene valor
- Mostrar nombre, email y contraseña en código monoespaciado
- Botón copiar con icono Check cuando se copia
- Advertencia sobre verificación de email
- Al cerrar, redirigir a lista de usuarios

### 6. Actualizar traducciones

**Frontend (`en.json`):**

```json
{
    "Invitación por email": "Email Invitation",
    "Contraseña manual": "Manual Password",
    "Selecciona el método para crear el nuevo usuario.": "Select the method to create the new user.",
    "Se generará una contraseña segura que se mostrará una sola vez. El usuario deberá verificar su correo electrónico.": "A secure password will be generated and shown only once. The user must verify their email address.",
    "Usuario creado exitosamente": "User created successfully",
    "La contraseña se muestra a continuación. Cópiala ahora, ya que no podrás verla de nuevo.": "The password is shown below. Copy it now, as you won't be able to see it again.",
    "Copiar contraseña": "Copy password",
    "Contraseña copiada al portapapeles": "Password copied to clipboard",
    "El usuario deberá verificar su correo electrónico antes de poder acceder al sistema.": "The user must verify their email before accessing the system.",
    "Importante:": "Important:"
}
```

**Backend (`lang/es/messages.php` y `lang/en/messages.php`):**

- Agregar mensaje de contraseña mínima 8 caracteres

### 7. Crear tests

**Archivo:** `tests/Feature/Admin/UserCreationTest.php`

**Test 1 - Invitación (Happy Path):**

1. Superadmin crea usuario con `mode=invitation`
2. Verificar: usuario creado, email verificado, rol asignado
3. Verificar: UserInvitationMail enviado
4. Usuario usa link de reset → define contraseña → accede a dashboard

**Test 2 - Manual (Happy Path):**

1. Superadmin crea usuario con `mode=manual`
2. Verificar: respuesta Inertia con `generatedPassword`
3. Verificar: usuario creado, email NO verificado, rol asignado
4. Verificar: notificación de verificación enviada
5. Usuario login → ve página de verificación
6. Usuario verifica email → accede al sistema

**Test 3 - Validaciones:**

- Email único (no duplicados)
- Mode válido (invitation o manual)

---

## Flujos de Usuario

### Modo Invitación

```
Admin selecciona tab "Invitación por email"
  → Llena formulario (nombre, email, rol)
  → Submit
  → Backend: crea usuario, email_verified_at=now(), envía UserInvitationMail
  → Redirect a lista con mensaje éxito

Usuario recibe email con link
  → Entra al link (ruta password.reset sin auth)
  → Define contraseña
  → Entra al sistema (dashboard según rol)
```

### Modo Manual

```
Admin selecciona tab "Contraseña manual"
  → Llena formulario (nombre, email, rol)
  → Submit
  → Backend: crea usuario, email_verified_at=null, genera password, envía VerifyEmail
  → Retorna página con modal mostrando contraseña
  → Admin copia contraseña y cierra modal
  → Redirect a lista

Usuario intenta login con email y contraseña
  → Login exitoso pero email no verificado
  → Redirect a página VerifyEmail
  → Usuario verifica email vía link
  → Usuario puede acceder al sistema
```

---

## Verificación

Después de implementar, ejecutar:

```bash
# Lint y formato
./vendor/bin/pint
pnpm format
pnpm lint:fix

# Tests
php artisan test --filter=UserCreation

# Build
pnpm build
```

**Pruebas manuales:**

1. Crear usuario con modo invitación → verificar email recibido
2. Crear usuario con modo manual → verificar modal con contraseña
3. Cambiar tabs → verificar URL query param se actualiza
4. Recargar página con `?mode=manual` → verificar tab correcto seleccionado
5. Login con usuario manual → verificar flujo de verificación de email
