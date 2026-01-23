<script lang="ts">
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import { Link, useForm } from '@inertiajs/svelte';
    import { _ } from '$lib/i18n';
    import AuthenticationLayout from '$lib/layouts/AuthenticationLayout.svelte';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';

    type Props = {
        isRegisterEnabled: boolean;
    };

    const { isRegisterEnabled }: Props = $props();

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(e: SubmitEvent) {
        e.preventDefault();
        $form.post(route('login'), {
            onFinish: () => $form.reset('password'),
        });
    }
</script>

<svelte:head>
    <title>{$_('Iniciar sesión')}</title>
</svelte:head>

<AuthenticationLayout>
    <form class="flex flex-col gap-6" onsubmit={submit}>
        <div class="flex flex-col items-center gap-2 text-center">
            <h1 class="text-2xl font-bold">{$_('Acceder a tu cuenta')}</h1>
            <p class="text-balance text-sm text-muted-foreground">
                {$_('Ingresa tu correo electrónico para acceder a tu cuenta')}
            </p>
        </div>
        <ErrorFeedback message={$form.errors.email} />
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">{$_('Correo electrónico')}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    bind:value={$form.email}
                    autocomplete="username"
                    placeholder="m@example.com"
                    required
                    autofocus
                />
            </div>
            <div class="grid gap-2">
                <Label for="password">{$_('Contraseña')}</Label>
                <Input
                    id="password"
                    type="password"
                    name="password"
                    bind:value={$form.password}
                    autocomplete="current-password"
                    placeholder="••••••••"
                    required
                />
            </div>
            <Button type="submit" class="w-full" disabled={$form.processing}>
                {$_('Ingresar')}
            </Button>
            <div class="text-center text-sm">
                <Link
                    href={route('auth.forgot-password')}
                    class="underline underline-offset-4"
                >
                    {$_('¿Olvidaste tu contraseña?')}
                </Link>
            </div>
        </div>
        {#if isRegisterEnabled}
            <div class="flex justify-center gap-1 text-sm">
                {$_('¿No tienes cuenta?')}
                <Link
                    href={route('register')}
                    class="underline underline-offset-4"
                >
                    {$_('Registrarse')}
                </Link>
            </div>
        {/if}
    </form>
</AuthenticationLayout>
