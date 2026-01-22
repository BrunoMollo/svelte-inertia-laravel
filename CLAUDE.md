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
  let count = $state(0);  // Not: let count = 0
</script>
```

### Derived State
```svelte
<script>
  let count = $state(0);
  const double = $derived(count * 2);  // Not: $: double = count * 2
</script>
```

### Props
```svelte
<script>
  let { title, description = 'default' } = $props();  // Not: export let title
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
