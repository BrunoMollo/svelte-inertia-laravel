<script lang="ts">
    import type { Role, User } from '$lib/types';
    import { Button } from '$lib/components/ui/button';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import * as Select from '$lib/components/ui/select';
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { useForm } from '@inertiajs/svelte';
    import { ArrowLeft, Loader2 } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';
    import { untrack } from 'svelte';
    import { _, translateRole } from '$lib/i18n';

    type Props = {
        user: User;
        roles: Role[];
    };

    let { user, roles }: Props = $props();

    // Use untrack to explicitly capture initial values without reactive dependency
    const initialData = untrack(() => ({
        name: user.name,
        email: user.email,
        role: user.roles && user.roles.length > 0 ? user.roles[0].name : '',
        userId: user.id,
    }));

    const form = useForm({
        name: initialData.name,
        email: initialData.email,
        role: initialData.role,
    });

    function submit(e: SubmitEvent) {
        e.preventDefault();

        $form.patch(route('superadmin.users.update', initialData.userId), {
            onSuccess: () => {
                toast.success($_('Usuario actualizado exitosamente'));
            },
            onError: () => {
                toast.error($_('Error al actualizar el usuario'));
            },
        });
    }
</script>

<svelte:head>
    <title>{$_('Editar usuario')} - {user.name}</title>
</svelte:head>

<AuthenticatedLayout>
    <div class="mx-auto max-w-2xl">
        <div class="mb-6">
            <Button variant="ghost" href={route('superadmin.users.index')}>
                <ArrowLeft class="mr-2 size-4" />
                {$_('Volver a usuarios')}
            </Button>
        </div>

        <div class="rounded-lg border p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold">{$_('Editar usuario')}</h1>
                <p class="text-muted-foreground text-sm">
                    {$_('Actualiza la información del usuario y su rol.')}
                </p>
            </div>

            <form onsubmit={submit} class="space-y-6">
                <div class="space-y-2">
                    <Label for="name">{$_('Nombre')}</Label>
                    <Input
                        id="name"
                        type="text"
                        bind:value={$form.name}
                        placeholder={$_('Ingresa el nombre completo')}
                        required
                    />
                    <ErrorFeedback message={$form.errors.name} />
                </div>

                <div class="space-y-2">
                    <Label for="email">{$_('Correo electrónico')}</Label>
                    <Input
                        id="email"
                        type="email"
                        bind:value={$form.email}
                        placeholder={$_(
                            'Ingresa la dirección de correo electrónico',
                        )}
                        required
                    />
                    <ErrorFeedback message={$form.errors.email} />
                </div>

                <div class="space-y-2">
                    <Label for="role">{$_('Rol')}</Label>
                    <Select.Root
                        type="single"
                        value={$form.role}
                        onValueChange={(v) => ($form.role = v ?? '')}
                    >
                        <Select.Trigger class="w-full">
                            <Select.Value
                                placeholder={$_('Selecciona un rol')}
                            />
                        </Select.Trigger>
                        <Select.Content>
                            {#each roles as role (role.id)}
                                <Select.Item value={role.name}>
                                    {$translateRole(role.name)}
                                </Select.Item>
                            {/each}
                        </Select.Content>
                    </Select.Root>
                    <ErrorFeedback message={$form.errors.role} />
                </div>

                <div class="flex justify-end gap-3">
                    <Button
                        variant="outline"
                        type="button"
                        href={route('superadmin.users.index')}
                    >
                        {$_('Cancelar')}
                    </Button>
                    <Button type="submit" disabled={$form.processing}>
                        {#if $form.processing}
                            <Loader2 class="mr-2 size-4 animate-spin" />
                        {/if}
                        {$_('Guardar cambios')}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</AuthenticatedLayout>
