<script lang="ts">
    import { _ } from '$lib/i18n';
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import { cn } from '$lib/utils';
    import { useForm } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';

    type Props = {
        className?: string;
    };

    let { className }: Props = $props();

    let passwordInput: HTMLInputElement | undefined = $state();
    let currentPasswordInput: HTMLInputElement | undefined = $state();

    const form = useForm({
        _method: 'PUT',
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function updatePassword(e: SubmitEvent) {
        e.preventDefault();

        $form.post(route('user-password.update'), {
            errorBag: 'updatePassword',
            preserveScroll: true,
            onSuccess: () => {
                $form.reset();
                toast.success($_('Contraseña actualizada exitosamente'));
            },
            onError: (errors) => {
                if (errors.password) {
                    $form.reset('password', 'password_confirmation');
                    passwordInput?.focus();
                }

                if (errors.current_password) {
                    $form.reset('current_password');
                    currentPasswordInput?.focus();
                }
            },
        });
    }
</script>

<section class={cn('flex flex-col gap-6', className)}>
    <header class="flex flex-col gap-2">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {$_('Actualizar contraseña')}
        </h2>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            {$_(
                'Asegúrate de que tu cuenta esté usando una contraseña larga y aleatoria para mantenerte seguro.',
            )}
        </p>
    </header>

    <form onsubmit={updatePassword} class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <Label for="current_password">{$_('Contraseña actual')}</Label>

            <Input
                id="current_password"
                bind:value={$form.current_password}
                type="password"
                class="max-w-lg"
                autocomplete="current-password"
            />

            {#if $form.errors.current_password}
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {$form.errors.current_password}
                </p>
            {/if}
        </div>

        <div class="flex flex-col gap-2">
            <Label for="password">{$_('Nueva contraseña')}</Label>

            <Input
                id="password"
                bind:value={$form.password}
                type="password"
                class="max-w-lg"
                autocomplete="new-password"
            />

            {#if $form.errors.password}
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {$form.errors.password}
                </p>
            {/if}
        </div>

        <div class="flex flex-col gap-2">
            <Label for="password_confirmation"
                >{$_('Confirmar contraseña')}</Label
            >

            <Input
                id="password_confirmation"
                bind:value={$form.password_confirmation}
                type="password"
                class="max-w-lg"
                autocomplete="new-password"
            />

            {#if $form.errors.password_confirmation}
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {$form.errors.password_confirmation}
                </p>
            {/if}
        </div>

        <div class="flex items-center gap-4">
            <Button type="submit" disabled={$form.processing}
                >{$_('Guardar cambios')}</Button
            >
        </div>
    </form>
</section>
