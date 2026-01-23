# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A full-stack web application starter using Laravel 12, Inertia.js, Svelte 5 (with Runes), TypeScript, and Tailwind CSS 4. The project uses a monolithic architecture where Laravel serves as the backend API and Svelte 5 provides the frontend interface, connected seamlessly via Inertia.js.

## Key Technologies

- **Backend**: Laravel 12 (PHP 8.4), Laravel Fortify (auth), Laravel Sanctum, Spatie Permissions
- **Frontend**: Svelte 5 with Runes, TypeScript 5.x, Inertia.js 2.x
- **Build**: Vite 7.x, pnpm (NEVER use npm)
- **UI**: Tailwind CSS 4.x, shadcn-svelte components, Bits UI
- **Testing**: Pest PHP
- **Database**: SQLite (default)

## Development Commands

### Starting Development Environment

```bash
# Install dependencies
composer install
pnpm install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Start development servers (all-in-one command)
composer run dev
# This runs: php artisan serve, queue:listen, pail (logs), and npm run dev concurrently
```

### Frontend Development

```bash
pnpm dev                 # Start Vite dev server
pnpm build              # Build for production (includes SSR)
pnpm lint               # Run ESLint
pnpm lint:fix           # Auto-fix linting issues
pnpm format             # Format code with Prettier
pnpm format:check       # Check formatting
```

### Backend Development

```bash
php artisan serve       # Start Laravel development server
php artisan test        # Run Pest tests
./vendor/bin/pint       # Fix PHP code style (Laravel Pint)
php artisan migrate     # Run migrations
php artisan migrate:fresh --seed  # Fresh migrations with seeders
php artisan tinker      # REPL
php artisan pail        # View logs
```

### Testing

```bash
php artisan test        # Run all Pest tests
php artisan test --filter=TestName  # Run specific test
```

## Architecture & Patterns

### Inertia.js Integration

- **HandleInertiaRequests middleware** (`app/Http/Middleware/HandleInertiaRequests.php`): Shares global data to all pages including `auth.user` and `ziggy` routes
- **Page components** are in `resources/js/Pages/` organized by feature (Auth, Profile, Security, Admin, Public)
- **No layouts directory**: Pages handle their own layout composition using Svelte components
- **SSR enabled**: Both client and SSR builds are generated (`app.ts` and `ssr.ts`)

### Frontend Structure

- **Pages**: `resources/js/Pages/**/*.svelte` - Inertia page components
- **UI Components**: `resources/js/lib/components/ui/` - shadcn-svelte components
- **Custom Components**: `resources/js/lib/components/ui/custom/` - App-specific UI components
- **Utilities**: `resources/js/lib/utils.ts` - Shared utility functions
- **Types**: `resources/js/lib/types/` - TypeScript type definitions
- **Alias**: `$lib` maps to `/resources/js/lib` (defined in vite.config.js)

### Backend Structure

- **Controllers**: `app/Http/Controllers/` - Organized by feature (Account, Auth)
- **Fortify Actions**: `app/Actions/Fortify/` - Custom Fortify authentication actions
- **Middleware**: `app/Http/Middleware/` - Including HandleInertiaRequests
- **Models**: `app/Models/` - Eloquent models (User, Session)
- **Routes**:
    - `routes/web.php` - Main application routes
    - `routes/auth.php` - Authentication routes (loaded via Fortify)

### Authentication

- Uses **Laravel Fortify** for authentication features (login, register, 2FA, password reset)
- Custom Fortify responses in `app/Http/Responses/` to return Inertia responses
- Two-factor authentication is enabled (see migrations and SecurityController)
- Session management with browser session tracking

### Roles & Permissions

- **Spatie Laravel Permission** package is installed
- Example admin route with `role:superadmin` middleware at `/superadmin/dashboard`
- Migrations include permission tables

## Critical Svelte 5 Patterns

This project uses **Svelte 5 with Runes**, not Svelte 4. Key differences:

### Reactivity

```svelte
<!-- Use $state instead of let -->
<script>
    let count = $state(0); // Not: let count = 0
</script>
```

### Derived State

```svelte
<script>
    let count = $state(0);
    const double = $derived(count * 2); // Not: $: double = count * 2
</script>
```

### Props

```svelte
<script>
    let { title, description = 'default' } = $props(); // Not: export let title
</script>
```

### Events

```svelte
<!-- Use onclick, not on:click -->
<button onclick={() => count++}>Click</button>
```

### Component Events

```svelte
<!-- Pass callback props, not createEventDispatcher -->
<script>
    let { onSubmit } = $props();
</script>

<form onsubmit={onSubmit}>...</form>
```

### Snippets (Not Slots)

```svelte
<script>
    let { children, header } = $props();
</script>

{@render header?.()}
{@render children?.()}
```

## Internationalization (i18n)

This project uses a custom i18n system for localization. **Spanish is the source language** and English translations are provided.

### Required Pattern for All Components

**ALWAYS use the `$_()` function for user-facing text** in Svelte components:

1. **Import the i18n helper**:

    ```svelte
    <script>
        import { _ } from '$lib/i18n';
    </script>
    ```

2. **Wrap all visible text with `$_()`** using **Spanish as the parameter**:

    ```svelte
    <!-- Page titles -->
    <svelte:head>
        <title>{$_('Bienvenido')}</title>
    </svelte:head>

    <!-- Navigation and links -->
    <a href="/login">{$_('Iniciar sesión')}</a>

    <!-- Buttons and labels -->
    <button>{$_('Registrarse')}</button>
    <label>{$_('Nombre de usuario')}</label>

    <!-- Paragraphs and headings -->
    <h1>{$_('Configuración de cuenta')}</h1>
    <p>{$_('Tu Landing va aquí')}</p>

    <!-- Form placeholders -->
    <input placeholder={$_('Ingresa tu email')} />
    ```

3. **Add English translations** to `resources/js/lib/i18n/locales/en.json`:
    ```json
    {
        "Bienvenido": "Welcome",
        "Iniciar sesión": "Login",
        "Registrarse": "Register"
    }
    ```

### What to Localize

- Page titles and meta descriptions
- Navigation text and menu items
- Button labels and link text
- Form labels, placeholders, and validation messages
- Headings, paragraphs, and body text
- Error messages and notifications
- Success/info messages
- Alt text for images
- ARIA labels for accessibility

### What NOT to Localize

- Code comments
- Console logs (unless user-facing)
- Variable names and function names
- API endpoints or route names
- Technical identifiers
- CSS classes
- Email addresses and URLs (unless part of user-facing text)

### Important Rules

- **Spanish is the KEY**: The Spanish text inside `$_()` serves as both the default text AND the translation key
- **Natural Spanish**: Write natural, idiomatic Spanish, not literal translations
- **Consistent terminology**: Use the same Spanish terms across all components
- **Check for duplicates**: Review `en.json` before adding new translations to avoid duplicates
- **Keep sorted**: Maintain alphabetical order in translation files for maintainability

### Example Reference

See `resources/js/Pages/Public/Welcome.svelte` for a complete example of a properly localized component.

### svelte-localizer Agent

For batch localization of components, use the specialized agent:

```bash
# The svelte-localizer agent can help convert existing components
# It uses Haiku model for cost-effectiveness
```

**Agent**: `svelte-localizer`
**Config**: `.claude/agents/svelte-localizer.yaml`
**Purpose**: Convert hardcoded text in Svelte components to use `$_()` with Spanish keys and add English translations

### Email Localization (Backend)

**Emails are sent in the recipient user's preferred locale**, falling back to `app()->getLocale()`.

- **Translations**: `lang/es/mail.php` (Spanish text), `lang/en/mail.php` (English translations)
- **Keys are Spanish text**: Like the frontend, use full Spanish text as translation keys (e.g., `'Has sido invitado a :app'`)
- **User locale**: Users have a `locale` field (nullable) and a `preferredLocale()` method
- **Usage pattern**: Use the `UsesUserLocale` trait in Mailable classes

Example:

```php
use App\Mail\Concerns\UsesUserLocale;

class SomeMail extends Mailable
{
    use Queueable, SerializesModels, UsesUserLocale;

    public function __construct(public User $user)
    {
        $this->setUserLocale(); // Set locale before envelope/content methods
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Has sido invitado a :app', ['app' => $value]),
        );
    }
}
```

**Translation files**:

```php
// lang/es/mail.php (Spanish is the source language)
return [
    'Has sido invitado a :app' => 'Has sido invitado a :app',
    // ...
];

// lang/en/mail.php (English translations)
return [
    'Has sido invitado a :app' => 'You have been invited to :app',
    // ...
];
```

**Important**: Call `$this->setUserLocale()` in the constructor to ensure the subject and body use the correct locale.

## Important Development Rules

### Package Manager

- **ALWAYS use pnpm**, NEVER use npm
- Install packages: `pnpm add <package>` or `pnpm add -D <package>`

### Svelte 5

- Use Svelte 5 runes (`$state`, `$derived`, `$effect`, `$props`)
- Avoid Svelte stores - use runes instead
- See `.cursor/rules/svelte-5.mdc` for comprehensive migration guide

### Styling

- Use Tailwind CSS 4 for styling
- Avoid writing plain CSS unless absolutely necessary
- Tailwind config is automatic with Vite plugin

### shadcn-svelte Components

To check available shadcn-svelte components and their usage, fetch the components documentation:

```bash
curl https://www.shadcn-svelte.com/llms.txt
```

### Laravel Models

- When creating models, always use `php artisan make:model -mf` (includes migration and factory)
- Keep factories synchronized with migrations
- Prefer timestamp columns over booleans (e.g., `activated_at` instead of `is_active`)

### Code Quality Validation

Before marking a significant task as complete, run these commands and ensure they pass:

```bash
./vendor/bin/pint       # PHP code style
php artisan test        # All tests pass
pnpm format             # Format code
pnpm lint:fix           # Fix linting issues
pnpm build              # Ensure build succeeds
```

Skip validation only for very minor changes (small style fixes). Always validate for feature additions or significant changes.

## Project Scripts & Tools

### Claude Agents

The project has specialized agent configurations for common tasks:

#### shadcn-installer

- **Model**: Haiku (cost-effective for simple tasks)
- **Purpose**: Non-interactive shadcn-svelte component installation
- **Config**: `.claude/agents/shadcn-installer.yaml`
- **Usage**: Install UI components without manual interaction

#### svelte-localizer

- **Model**: Haiku (cost-effective for simple tasks)
- **Purpose**: Localize Svelte components using the `$_()` pattern
- **Config**: `.claude/agents/svelte-localizer.yaml`
- **Usage**: Convert hardcoded text to Spanish `$_()` calls with English translations

## Routing & Navigation

- **Ziggy** provides Laravel routes to JavaScript: Use `route('route.name')` in Svelte components
- Routes are automatically available via the `ziggy` prop shared in HandleInertiaRequests
- Named routes are defined in `routes/web.php` and `routes/auth.php`

## Common Patterns

### Creating a New Page

1. Create Svelte component in `resources/js/Pages/FeatureName/PageName.svelte`
2. Add route in `routes/web.php`:

```php
Route::get('/path', function () {
    return Inertia::render('FeatureName/PageName', [
        'data' => $data
    ]);
})->name('page.name');
```

### Inertia Forms

Use Inertia's form helper with Svelte 5:

```svelte
<script>
    import { useForm } from '@inertiajs/svelte';

    const form = useForm({
        name: '',
        email: '',
    });

    function submit() {
        $form.post(route('route.name'));
    }
</script>
```

### Shared Props

Global props available to all pages (via HandleInertiaRequests):

- `auth.user` - Current authenticated user
- `ziggy` - Route helper data

## Debugging & Logs

```bash
php artisan pail        # Tail application logs
php artisan pail --filter=error  # Filter by level
```

## File Organization

### Backend

- Controllers should be thin and delegate to Actions when logic grows
- Keep related functionality grouped (Account/Profile, Account/Security)
- Use Form Requests for validation

### Frontend

- Page components in `Pages/`
- Reusable UI components in `lib/components/ui/`
- Custom app components in `lib/components/ui/custom/`
- Place TypeScript types in `lib/types/`

## SSR Considerations

- Server-Side Rendering is enabled
- Both client (`app.ts`) and server (`ssr.ts`) entry points exist
- Ensure components are SSR-compatible (avoid browser-only APIs without guards)
- Build command generates both bundles: `pnpm build`
