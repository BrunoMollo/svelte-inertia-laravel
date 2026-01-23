<script lang="ts">
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import AuthenticationLayout from '$lib/layouts/AuthenticationLayout.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { _ } from '$lib/i18n';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { onMount } from 'svelte';

    type Props = {
        isRegisterEnabled: boolean;
    };

    const { isRegisterEnabled }: Props = $props();

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e: SubmitEvent) {
        e.preventDefault();
        $form.post(route('register'), {
            onFinish: () => $form.reset('password', 'password_confirmation'),
        });
    }

    onMount(() => {
        if (!isRegisterEnabled) {
            window.location.href = route('login');
        }
    });
</script>

<svelte:head>
    <title>{$_('Registrar cuenta')}</title>
</svelte:head>

{#if isRegisterEnabled}
    <AuthenticationLayout>
        <form class="flex flex-col gap-6" onsubmit={submit}>
            <div class="flex flex-col items-center gap-2 text-center">
                <h1 class="text-2xl font-bold">{$_('Registrar tu cuenta')}</h1>
                <p class="text-balance text-sm text-muted-foreground">
                    {$_(
                        'Ingresa tu correo electrónico para registrar tu cuenta',
                    )}
                </p>
            </div>

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">{$_('Nombre')}</Label>
                    <Input
                        id="name"
                        type="text"
                        name="name"
                        bind:value={$form.name}
                        autocomplete="username"
                        placeholder={$_('Ingresa tu nombre')}
                        required
                        autofocus
                    />
                    <ErrorFeedback message={$form.errors.name} />
                </div>
            </div>

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">{$_('Correo electrónico')}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        bind:value={$form.email}
                        autocomplete="username"
                        placeholder="you@example.com"
                        required
                    />
                    <ErrorFeedback message={$form.errors.email} />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center">
                        <Label for="password">{$_('Contraseña')}</Label>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        bind:value={$form.password}
                        autocomplete="current-password"
                        placeholder="••••••••"
                        required
                    />
                    <ErrorFeedback message={$form.errors.password} />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center">
                        <Label for="password_confirmation"
                            >{$_('Confirmar contraseña')}</Label
                        >
                    </div>
                    <Input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        bind:value={$form.password_confirmation}
                        autocomplete="current-password"
                        placeholder="••••••••"
                        required
                    />
                    <ErrorFeedback
                        message={$form.errors.password_confirmation}
                    />
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    disabled={$form.processing}
                >
                    {$_('Registrarse')}
                </Button>
            </div>

            <div class="flex justify-center gap-1 text-sm">
                {$_('¿Ya tienes cuenta?')}
                <Link
                    href={route('login')}
                    class="underline underline-offset-4"
                >
                    {$_('Iniciar sesión')}
                </Link>
            </div>
        </form>
    </AuthenticationLayout>
{/if}
