<script lang="ts">
    import Ripple from '$lib/components/ui/custom/ripple.svelte';
    import type { PageProps } from '$lib/types';
    import { Link } from '@inertiajs/svelte';
    import { Command } from '@lucide/svelte';
    import { _ } from '$lib/i18n';

    let { auth } = $props() as PageProps;
</script>

<svelte:head>
    <title>{$_('Bienvenido')}</title>
</svelte:head>

<div class="flex h-svh flex-col">
    <header>
        <div
            class="mx-auto flex max-w-7xl items-center justify-between p-6 md:p-10"
        >
            <Link href="/" class="flex items-center gap-2 font-medium">
                <div
                    class="flex h-6 w-6 items-center justify-center rounded-md bg-primary text-primary-foreground"
                >
                    <Command class="size-4" />
                </div>
                Svelte Inertia Laravel
            </Link>

            <nav class="flex items-center gap-4">
                {#if auth.user}
                    <Link
                        href={route('superadmin.dashboard')}
                        class="rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                    >
                        {$_('Dashboard')}
                    </Link>
                {:else}
                    <Link
                        href={route('login')}
                        class="rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                    >
                        {$_('Iniciar sesión')}
                    </Link>
                    <Link
                        href={route('register')}
                        class="rounded-md text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                    >
                        {$_('Registrarse')}
                    </Link>
                {/if}
            </nav>
        </div>
    </header>

    <main class="mx-auto h-full w-full max-w-7xl grow">
        <div class="relative flex h-full items-center justify-center">
            <p class="text-[clamp(1.5rem,5vw,4rem)] leading-none">
                {$_('Tu Landing va aquí')}
            </p>
            <Ripple />
        </div>
    </main>
</div>
