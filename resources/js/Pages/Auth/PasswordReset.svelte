<script lang="ts">
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import AuthenticationLayout from '$lib/layouts/AuthenticationLayout.svelte';
    import { useForm } from '@inertiajs/svelte';
    import { _ } from '$lib/i18n';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';

    type Props = {
        token: string;
    };

    const { token = $bindable() }: Props = $props();

    const initialToken = token;

    const form = useForm({
        _method: 'POST',
        token: initialToken,
        email: new URLSearchParams(window.location.search).get('email') || '',
        password: '',
        password_confirmation: '',
    });

    function submit(e: SubmitEvent) {
        e.preventDefault();
        $form.post(route('password.update'), {
            onFinish: () => $form.reset('password', 'password_confirmation'),
        });
    }
</script>

<svelte:head>
    <title>{$_('Restablecer contraseña')}</title>
</svelte:head>

<AuthenticationLayout>
    <form class="flex flex-col gap-6" onsubmit={submit}>
        <div class="flex flex-col items-center gap-4 text-center">
            <h1 class="text-2xl font-bold">{$_('Restablecer contraseña')}</h1>
            <p class="text-balance text-sm text-muted-foreground">
                {$_(
                    'Por favor, ingresa tu nueva contraseña a continuación para restablecer la contraseña de tu cuenta.',
                )}
            </p>
        </div>

        <ErrorFeedback message={$form.errors.token} />

        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">{$_('Correo electrónico')}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    bind:value={$form.email}
                    class="block w-full"
                    autocomplete="username"
                    required
                    disabled
                />
                <ErrorFeedback message={$form.errors.email} />
            </div>

            <div class="grid gap-2">
                <Label for="password">{$_('Nueva contraseña')}</Label>
                <Input
                    id="password"
                    type="password"
                    name="password"
                    bind:value={$form.password}
                    class="block w-full"
                    autocomplete="new-password"
                    required
                />
                <ErrorFeedback message={$form.errors.password} />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation"
                    >{$_('Confirmar nueva contraseña')}</Label
                >
                <Input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    bind:value={$form.password_confirmation}
                    class="block w-full"
                    autocomplete="new-password"
                    required
                />
                <ErrorFeedback message={$form.errors.password_confirmation} />
            </div>

            <Button type="submit" class="w-full" disabled={$form.processing}>
                {$_('Restablecer contraseña')}
            </Button>

            <div class="text-center text-sm">
                {$_('¿Recuerdas tu contraseña?')}
                <a href={route('login')} class="underline underline-offset-4">
                    {$_('Iniciar sesión')}
                </a>
            </div>
        </div>
    </form>
</AuthenticationLayout>
