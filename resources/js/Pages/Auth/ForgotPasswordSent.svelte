<script lang="ts">
    import { Button } from '$lib/components/ui/button';
    import AuthenticationLayout from '$lib/layouts/AuthenticationLayout.svelte';
    import { Link } from '@inertiajs/svelte';
    import { _ } from '$lib/i18n';
    import { MailOpen } from '@lucide/svelte';
</script>

<svelte:head>
    <title>{$_('Enlace de restablecimiento de contraseña enviado')}</title>
</svelte:head>

<AuthenticationLayout>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col items-center gap-4 text-center">
            <div class="rounded-full bg-muted p-3">
                <MailOpen class="size-6" />
            </div>
            <h1 class="text-2xl font-bold">{$_('Revisa tu correo')}</h1>
            <p class="text-balance text-sm text-muted-foreground">
                {$_(
                    'Hemos enviado un enlace para restablecer tu contraseña a tu dirección de correo electrónico. Por favor, revisa tu bandeja de entrada y sigue las instrucciones para restablecer tu contraseña.',
                )}
            </p>
        </div>

        <div class="grid gap-4">
            <Button variant="outline" class="w-full">
                <Link href={route('login')}
                    >{$_('Volver a iniciar sesión')}</Link
                >
            </Button>

            <div class="text-center text-sm">
                {$_('¿No recibiste el correo?')}
                <Link
                    href={route('auth.forgot-password')}
                    class="underline underline-offset-4"
                >
                    {$_('Intenta de nuevo')}
                </Link>
            </div>
        </div>
    </div>
</AuthenticationLayout>
