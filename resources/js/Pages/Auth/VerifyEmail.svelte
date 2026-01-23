<script lang="ts">
    import { Button } from '$lib/components/ui/button';
    import AuthenticationLayout from '$lib/layouts/AuthenticationLayout.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { _ } from '$lib/i18n';

    type Props = {
        status?: string;
    };

    const { status }: Props = $props();

    const form = useForm({});

    function submit(e: SubmitEvent) {
        e.preventDefault();
        $form.post(route('verification.send'));
    }
</script>

<svelte:head>
    <title>{$_('Verificar correo electrónico')}</title>
</svelte:head>

<AuthenticationLayout>
    <form class="flex flex-col gap-6" onsubmit={submit}>
        <div class="flex flex-col items-center gap-4 text-center">
            <h1 class="text-2xl font-bold">
                {$_('Verifica tu correo electrónico')}
            </h1>
            <p class="text-balance text-sm text-muted-foreground">
                {$_(
                    'Por favor, verifica tu correo electrónico haciendo clic en el enlace que te enviamos. Si no lo has recibido, podemos enviar un nuevo enlace de verificación.',
                )}
            </p>
        </div>

        {#if status === 'verification-link-sent'}
            <div
                class="text-center text-sm font-medium text-green-600 dark:text-green-400"
            >
                {$_(
                    'Se ha enviado un nuevo enlace de verificación a tu correo electrónico.',
                )}
            </div>
        {/if}

        <div class="grid gap-6">
            <Button type="submit" class="w-full" disabled={$form.processing}>
                {$_('Reenviar enlace de verificación')}
            </Button>

            <div class="text-center text-sm">
                <Link
                    href={route('logout')}
                    method="post"
                    as="button"
                    class="underline underline-offset-4"
                >
                    {$_('Cerrar sesión')}
                </Link>
            </div>
        </div>
    </form>
</AuthenticationLayout>
