<script lang="ts">
    import type { Role } from '$lib/types';
    import { Button } from '$lib/components/ui/button';
    import * as Dialog from '$lib/components/ui/dialog';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import * as Select from '$lib/components/ui/select';
    import * as Tabs from '$lib/components/ui/tabs';
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { router, useForm } from '@inertiajs/svelte';
    import { ArrowLeft, Check, Copy, Loader2 } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';
    import { _, translateRole } from '$lib/i18n';
    import { untrack } from 'svelte';

    type Props = {
        roles: Role[];
        mode?: string;
        generatedPassword?: string;
        createdUser?: { name: string; email: string };
    };

    let {
        roles,
        mode = 'invitation',
        generatedPassword,
        createdUser,
    }: Props = $props();

    const initialMode = untrack(() => mode);
    let currentMode = $state(initialMode);

    const showPasswordModal = $derived(!!generatedPassword);
    let passwordCopied = $state(false);

    const form = useForm({
        name: '',
        email: '',
        role: '',
        mode: initialMode,
    });

    $effect(() => {
        $form.mode = currentMode;
    });

    function handleTabChange(value: string | undefined) {
        if (!value) return;
        currentMode = value;
        router.get(
            route('superadmin.users.create'),
            { mode: value },
            { preserveState: true, replace: true },
        );
    }

    function submit(e: SubmitEvent) {
        e.preventDefault();

        $form.post(route('superadmin.users.store'), {
            preserveState: true,
            onSuccess: () => {
                if (currentMode === 'invitation') {
                    toast.success($_('Usuario creado exitosamente'));
                }
            },
            onError: () => {
                toast.error($_('Error al crear el usuario'));
            },
        });
    }

    async function copyPassword() {
        if (typeof navigator !== 'undefined' && generatedPassword) {
            await navigator.clipboard.writeText(generatedPassword);
            passwordCopied = true;
            toast.success($_('Contraseña copiada al portapapeles'));
            setTimeout(() => {
                passwordCopied = false;
            }, 2000);
        }
    }

    function closePasswordModal() {
        router.visit(route('superadmin.users.index'));
    }
</script>

<svelte:head>
    <title>{$_('Crear usuario')}</title>
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
                <h1 class="text-2xl font-semibold">{$_('Crear usuario')}</h1>
                <p class="text-muted-foreground text-sm">
                    {$_('Selecciona el método para crear el nuevo usuario.')}
                </p>
            </div>

            <Tabs.Root value={currentMode} onValueChange={handleTabChange}>
                <Tabs.List class="mb-6 grid w-full grid-cols-2">
                    <Tabs.Trigger value="invitation">
                        {$_('Invitación por email')}
                    </Tabs.Trigger>
                    <Tabs.Trigger value="manual">
                        {$_('Contraseña manual')}
                    </Tabs.Trigger>
                </Tabs.List>

                <div class="relative mb-4 min-h-[3rem]">
                    <Tabs.Content value="invitation" class="tab-content">
                        <p class="text-muted-foreground text-sm">
                            {$_(
                                'El usuario recibirá un correo de invitación para establecer su contraseña.',
                            )}
                        </p>
                    </Tabs.Content>

                    <Tabs.Content value="manual" class="tab-content">
                        <p class="text-muted-foreground text-sm">
                            {$_(
                                'Se generará una contraseña segura que se mostrará una sola vez. El usuario deberá verificar su correo electrónico.',
                            )}
                        </p>
                    </Tabs.Content>
                </div>
            </Tabs.Root>

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
                        {$_('Crear usuario')}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</AuthenticatedLayout>

<Dialog.Root
    open={showPasswordModal}
    onOpenChange={(open) => !open && closePasswordModal()}
>
    <Dialog.Content class="sm:max-w-md">
        <Dialog.Header>
            <Dialog.Title>{$_('Usuario creado exitosamente')}</Dialog.Title>
            <Dialog.Description>
                {$_(
                    'La contraseña se muestra a continuación. Cópiala ahora, ya que no podrás verla de nuevo.',
                )}
            </Dialog.Description>
        </Dialog.Header>

        {#if createdUser}
            <div class="space-y-4">
                <div>
                    <Label class="text-muted-foreground">{$_('Nombre')}</Label>
                    <p class="font-medium">{createdUser.name}</p>
                </div>
                <div>
                    <Label class="text-muted-foreground"
                        >{$_('Correo electrónico')}</Label
                    >
                    <p class="font-medium">{createdUser.email}</p>
                </div>
                <div>
                    <Label class="text-muted-foreground"
                        >{$_('Contraseña')}</Label
                    >
                    <div class="mt-1 flex items-center gap-2">
                        <code
                            class="bg-muted flex-1 rounded px-3 py-2 font-mono text-sm"
                        >
                            {generatedPassword}
                        </code>
                        <Button
                            variant="outline"
                            size="icon"
                            onclick={copyPassword}
                            title={$_('Copiar contraseña')}
                        >
                            {#if passwordCopied}
                                <Check class="size-4 text-green-600" />
                            {:else}
                                <Copy class="size-4" />
                            {/if}
                        </Button>
                    </div>
                </div>

                <div
                    class="rounded-md bg-yellow-50 p-3 text-sm text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200"
                >
                    <p class="font-medium">{$_('Importante:')}</p>
                    <p>
                        {$_(
                            'El usuario deberá verificar su correo electrónico antes de poder acceder al sistema.',
                        )}
                    </p>
                </div>
            </div>
        {/if}

        <Dialog.Footer>
            <Button onclick={closePasswordModal}>
                {$_('Cerrar')}
            </Button>
        </Dialog.Footer>
    </Dialog.Content>
</Dialog.Root>

<style>
    :global(.tab-content) {
        position: absolute;
        inset: 0;
        opacity: 0;
        transform: translateX(8px);
        transition:
            opacity 200ms ease-out,
            transform 200ms ease-out;
        pointer-events: none;
    }

    :global(.tab-content[data-state='active']) {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
    }

    :global(.tab-content[data-state='inactive']) {
        opacity: 0;
        transform: translateX(-8px);
    }
</style>
