<script lang="ts">
    import ConfirmWithPassword from '$lib/components/ui/custom/confirm-with-password.svelte';
    import { Button } from '$lib/components/ui/button';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { Label } from '$lib/components/ui/label';
    import type { PageProps } from '$lib/types';
    import { page } from '@inertiajs/svelte';
    import { useForm } from '@inertiajs/svelte';
    import axios from 'axios';
    import { CheckCircle, Siren } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';
    import * as InputOTP from '$lib/components/ui/input-otp';
    import { _ } from '$lib/i18n';

    // State
    let enabling = $state(false);
    let disabling = $state(false);
    let qrCode = $state<string | null>(null);
    let recoveryCodes = $state<string[]>([]);

    // Form
    const form = useForm({
        code: '',
    });

    // User data
    const auth = $page.props.auth as PageProps['auth'];
    const user = auth.user;
    let twoFactorEnabled = $state(user.two_factor_confirmed_at !== null);

    // Functions
    function enableTwoFactorAuthentication() {
        enabling = true;

        $form.post(route('two-factor.enable'), {
            onSuccess: () => {
                enabling = false;
                showQrCode();
            },
        });
    }

    function showQrCode() {
        axios
            .get(route('two-factor.qr-code'))
            .then((response) => {
                qrCode = response.data.svg;
            })
            .then(() => {
                toast.success($_('Código QR de 2FA generado'));
            });
    }

    function confirmTwoFactorAuthentication(e: SubmitEvent) {
        e.preventDefault();

        $form.post(route('two-factor.confirm'), {
            errorBag: 'confirmTwoFactorAuthentication',
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                qrCode = null;
                twoFactorEnabled = true;
                showRecoveryCodes();
                toast.success($_('2FA habilitado exitosamente'));
            },
            onError: () => {
                toast.error($_('Hubo un error habilitando 2FA'));
            },
        });
    }

    function showRecoveryCodes() {
        axios.get(route('two-factor.recovery-codes')).then((response) => {
            recoveryCodes = response.data;
        });
    }

    function disableTwoFactorAuthentication() {
        disabling = true;

        $form.delete(route('two-factor.disable'), {
            preserveScroll: true,
            preserveState: true,
            onBefore: () => {
                disabling = true;
            },
            onSuccess: () => {
                disabling = false;
                twoFactorEnabled = false;
                toast.success($_('2FA deshabilitado exitosamente'));
            },
            onError: () => {
                disabling = false;
                twoFactorEnabled = true;
                toast.error($_('Hubo un error deshabilitando 2FA'));
            },
            onFinish: () => {
                disabling = false;
            },
        });
    }
</script>

<section class="flex max-w-xl flex-col gap-6">
    <header class="flex flex-col gap-2">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {$_('Autenticación de dos factores')}
        </h2>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            {$_(
                'Añade seguridad adicional a tu cuenta utilizando autenticación de dos factores.',
            )}
        </p>
    </header>

    {#if !twoFactorEnabled && !qrCode}
        <div class="flex flex-col gap-6">
            <div class="flex items-center gap-4 text-sm">
                <Siren class="text-red-500" />
                <h3>
                    {$_(
                        'La autenticación de dos factores no está habilitada. Te recomendamos habilitarla.',
                    )}
                </h3>
            </div>
            <ConfirmWithPassword
                title={$_('Habilitar 2FA')}
                description={$_(
                    'Para habilitar la autenticación de dos factores, debes confirmar tu contraseña.',
                )}
                onConfirm={enableTwoFactorAuthentication}
            >
                <Button type="button" disabled={enabling}>
                    {enabling ? $_('Habilitando...') : $_('Habilitar')}
                </Button>
            </ConfirmWithPassword>
        </div>
    {/if}

    {#if qrCode}
        <div class="flex flex-col gap-6">
            <p
                class="text-sm font-medium text-balance text-gray-900 dark:text-gray-100"
            >
                {$_(
                    'Para completar la habilitación de autenticación de dos factores, escanea el siguiente código QR con tu aplicación autenticadora del teléfono o ingresa la clave de configuración y proporciona el código OTP generado.',
                )}
            </p>

            <div class="contrast-200">{@html qrCode}</div>

            <div class="flex flex-col gap-2">
                <form onsubmit={confirmTwoFactorAuthentication}>
                    <Label for="code">{$_('Código')}</Label>

                    <div class="flex flex-col gap-2">
                        <div class="flex gap-2">
                            <InputOTP.Root
                                bind:value={$form.code}
                                maxlength={6}
                                autofocus
                            >
                                {#snippet children({ cells })}
                                    <InputOTP.Group>
                                        {#each cells as cell}
                                            <InputOTP.Slot {cell} />
                                        {/each}
                                    </InputOTP.Group>
                                {/snippet}
                            </InputOTP.Root>

                            <Button type="submit" disabled={$form.processing}>
                                {$form.processing
                                    ? $_('Confirmando...')
                                    : $_('Confirmar')}
                            </Button>
                        </div>

                        <ErrorFeedback message={$form.errors.code} />
                    </div>
                </form>
            </div>
        </div>
    {/if}

    {#if twoFactorEnabled && !qrCode}
        <div class="flex flex-col gap-6">
            <div class="flex items-center gap-4 text-sm">
                <CheckCircle class="text-green-500" />
                <h3>{$_('Ya tienes habilitado 2FA.')}</h3>
            </div>
            {#if recoveryCodes.length > 0}
                <div class="flex max-w-xl flex-col gap-4">
                    <div class="text-sm">
                        <span
                            class="font-semibold text-red-600 dark:text-red-400"
                        >
                            {$_('Importante:')}
                        </span>
                        {$_(
                            'Estos códigos de recuperación se mostrarán solo una vez. Guárdalos en un gestor de contraseñas seguro de inmediato. Pueden usarse para recuperar el acceso a tu cuenta si pierdes tu dispositivo de autenticación de dos factores.',
                        )}
                    </div>

                    <div
                        class="grid gap-1 rounded-lg bg-sidebar p-4 font-mono text-xs"
                    >
                        {#each recoveryCodes as code}
                            <div>{code}</div>
                        {/each}
                    </div>
                </div>
            {/if}
            <div class="flex gap-4">
                <ConfirmWithPassword
                    title={$_('Deshabilitar 2FA')}
                    description={$_(
                        'Para deshabilitar la autenticación de dos factores, debes confirmar tu contraseña.',
                    )}
                    onConfirm={disableTwoFactorAuthentication}
                >
                    <Button type="button" disabled={disabling}>
                        {disabling
                            ? $_('Deshabilitando...')
                            : $_('Deshabilitar')}
                    </Button>
                </ConfirmWithPassword>
            </div>
        </div>
    {/if}
</section>
