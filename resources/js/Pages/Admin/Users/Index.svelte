<script lang="ts">
    import type { PaginatedData, Role, User, UserFilters } from '$lib/types';
    import * as AlertDialog from '$lib/components/ui/alert-dialog';
    import { Badge } from '$lib/components/ui/badge';
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import * as Pagination from '$lib/components/ui/pagination';
    import * as Select from '$lib/components/ui/select';
    import * as Table from '$lib/components/ui/table';
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { router, useForm } from '@inertiajs/svelte';
    import {
        KeyRound,
        Loader2,
        Pencil,
        Plus,
        Power,
        Search,
        Trash2,
        UserX,
    } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';
    import { untrack } from 'svelte';
    import { _ } from '$lib/i18n';

    type Props = {
        users: PaginatedData<User>;
        roles: Role[];
        filters: UserFilters;
    };

    let { users, roles, filters }: Props = $props();

    // Use untrack to explicitly capture initial filter values
    const initialFilters = untrack(() => ({
        search: filters.search ?? '',
        role: filters.role ?? '',
        status: filters.status ?? '',
    }));

    let search = $state(initialFilters.search);
    let roleFilter = $state(initialFilters.role);
    let statusFilter = $state(initialFilters.status);

    let deleteUserId: number | null = $state(null);
    let toggleUserId: number | null = $state(null);
    let resetUserId: number | null = $state(null);

    const deleteForm = useForm({});
    const toggleForm = useForm({});
    const resetForm = useForm({});

    function applyFilters() {
        router.get(
            route('superadmin.users.index'),
            {
                search: search || undefined,
                role: roleFilter || undefined,
                status: statusFilter || undefined,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }

    function clearFilters() {
        search = '';
        roleFilter = '';
        statusFilter = '';
        router.get(route('superadmin.users.index'), {}, { replace: true });
    }

    function goToPage(page: number) {
        router.get(
            route('superadmin.users.index'),
            {
                page,
                search: search || undefined,
                role: roleFilter || undefined,
                status: statusFilter || undefined,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }

    function confirmDelete(userId: number) {
        deleteUserId = userId;
    }

    function deleteUser() {
        if (!deleteUserId) return;

        $deleteForm.delete(route('superadmin.users.destroy', deleteUserId), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success($_('Usuario eliminado exitosamente'));
                deleteUserId = null;
            },
            onError: () => {
                toast.error($_('Error al eliminar el usuario'));
            },
        });
    }

    function confirmToggle(userId: number) {
        toggleUserId = userId;
    }

    function toggleStatus() {
        if (!toggleUserId) return;

        $toggleForm.patch(
            route('superadmin.users.toggle-status', toggleUserId),
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success($_('Estado del usuario actualizado'));
                    toggleUserId = null;
                },
                onError: () => {
                    toast.error(
                        $_('Error al actualizar el estado del usuario'),
                    );
                },
            },
        );
    }

    function confirmReset(userId: number) {
        resetUserId = userId;
    }

    function resetPassword() {
        if (!resetUserId) return;

        $resetForm.post(route('superadmin.users.reset-password', resetUserId), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    $_('Correo de restablecimiento de contraseña enviado'),
                );
                resetUserId = null;
            },
            onError: () => {
                toast.error(
                    $_('Error al enviar el correo de restablecimiento'),
                );
            },
        });
    }

    function getUserToDelete() {
        return users.data.find((u) => u.id === deleteUserId);
    }

    function getUserToToggle() {
        return users.data.find((u) => u.id === toggleUserId);
    }

    function getUserToReset() {
        return users.data.find((u) => u.id === resetUserId);
    }

    function formatDate(dateString: string | undefined): string {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString();
    }
</script>

<svelte:head>
    <title>{$_('Gestión de usuarios')}</title>
</svelte:head>

<AuthenticatedLayout>
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    {$_('Gestión de usuarios')}
                </h1>
                <p class="text-muted-foreground text-sm">
                    {$_('Gestiona usuarios y sus roles')}
                </p>
            </div>
            <Button href={route('superadmin.users.create')}>
                <Plus class="mr-2 size-4" />
                {$_('Añadir usuario')}
            </Button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-4">
            <div class="relative flex-1 md:max-w-sm">
                <Search
                    class="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
                />
                <Input
                    type="text"
                    placeholder={$_('Busca por nombre o correo electrónico...')}
                    class="pl-10"
                    bind:value={search}
                    onkeydown={(e) => e.key === 'Enter' && applyFilters()}
                />
            </div>

            <Select.Root
                type="single"
                value={roleFilter}
                onValueChange={(v) => {
                    roleFilter = v ?? '';
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-[160px]">
                    <Select.Value placeholder={$_('Todos los roles')} />
                </Select.Trigger>
                <Select.Content>
                    <Select.Item value="">{$_('Todos los roles')}</Select.Item>
                    {#each roles as role (role.id)}
                        <Select.Item value={role.name}>{role.name}</Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>

            <Select.Root
                type="single"
                value={statusFilter}
                onValueChange={(v) => {
                    statusFilter = v ?? '';
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-[160px]">
                    <Select.Value placeholder={$_('Todos los estados')} />
                </Select.Trigger>
                <Select.Content>
                    <Select.Item value="">{$_('Todos los estados')}</Select.Item
                    >
                    <Select.Item value="active">{$_('Activo')}</Select.Item>
                    <Select.Item value="disabled"
                        >{$_('Deshabilitado')}</Select.Item
                    >
                </Select.Content>
            </Select.Root>

            <Button variant="outline" onclick={applyFilters}
                >{$_('Buscar')}</Button
            >
            {#if search || roleFilter || statusFilter}
                <Button variant="ghost" onclick={clearFilters}
                    >{$_('Limpiar')}</Button
                >
            {/if}
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <Table.Root>
                <Table.Header>
                    <Table.Row>
                        <Table.Head>{$_('Nombre')}</Table.Head>
                        <Table.Head>{$_('Correo electrónico')}</Table.Head>
                        <Table.Head>{$_('Rol')}</Table.Head>
                        <Table.Head>{$_('Estado')}</Table.Head>
                        <Table.Head>{$_('Creado')}</Table.Head>
                        <Table.Head class="text-right"
                            >{$_('Acciones')}</Table.Head
                        >
                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#each users.data as user (user.id)}
                        <Table.Row>
                            <Table.Cell class="font-medium">
                                {user.name}
                            </Table.Cell>
                            <Table.Cell>{user.email}</Table.Cell>
                            <Table.Cell>
                                {#if user.roles && user.roles.length > 0}
                                    <Badge variant="secondary">
                                        {user.roles[0].name}
                                    </Badge>
                                {:else}
                                    <span class="text-muted-foreground">-</span>
                                {/if}
                            </Table.Cell>
                            <Table.Cell>
                                {#if user.disabled_at}
                                    <Badge variant="destructive">
                                        {$_('Deshabilitado')}
                                    </Badge>
                                {:else}
                                    <Badge variant="default"
                                        >{$_('Activo')}</Badge
                                    >
                                {/if}
                            </Table.Cell>
                            <Table.Cell>
                                {formatDate(user.created_at)}
                            </Table.Cell>
                            <Table.Cell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        href={route(
                                            'superadmin.users.edit',
                                            user.id,
                                        )}
                                        title={$_('Editar')}
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onclick={() => confirmReset(user.id)}
                                        title={$_('Restablecer contraseña')}
                                    >
                                        <KeyRound class="size-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onclick={() => confirmToggle(user.id)}
                                        title={user.disabled_at
                                            ? $_('Habilitar')
                                            : $_('Deshabilitar')}
                                    >
                                        {#if user.disabled_at}
                                            <Power
                                                class="size-4 text-green-600"
                                            />
                                        {:else}
                                            <UserX
                                                class="size-4 text-yellow-600"
                                            />
                                        {/if}
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onclick={() => confirmDelete(user.id)}
                                        title={$_('Eliminar')}
                                    >
                                        <Trash2 class="size-4 text-red-600" />
                                    </Button>
                                </div>
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        <Table.Row>
                            <Table.Cell colspan={6} class="py-8 text-center">
                                <p class="text-muted-foreground">
                                    {$_('No se encontraron usuarios')}
                                </p>
                            </Table.Cell>
                        </Table.Row>
                    {/each}
                </Table.Body>
            </Table.Root>
        </div>

        <!-- Pagination -->
        {#if users.last_page > 1}
            <Pagination.Root
                count={users.total}
                perPage={users.per_page}
                page={users.current_page}
                onPageChange={(page) => goToPage(page)}
            >
                {#snippet children({ pages, currentPage })}
                    <Pagination.Content>
                        <Pagination.Item>
                            <Pagination.Previous />
                        </Pagination.Item>
                        {#each pages as page (page.key)}
                            {#if page.type === 'ellipsis'}
                                <Pagination.Item>
                                    <Pagination.Ellipsis />
                                </Pagination.Item>
                            {:else}
                                <Pagination.Item>
                                    <Pagination.Link
                                        {page}
                                        isActive={currentPage === page.value}
                                    />
                                </Pagination.Item>
                            {/if}
                        {/each}
                        <Pagination.Item>
                            <Pagination.Next />
                        </Pagination.Item>
                    </Pagination.Content>
                {/snippet}
            </Pagination.Root>
        {/if}
    </div>
</AuthenticatedLayout>

<!-- Delete Confirmation Dialog -->
<AlertDialog.Root
    open={deleteUserId !== null}
    onOpenChange={(open) => {
        if (!open) deleteUserId = null;
    }}
>
    <AlertDialog.Content>
        <AlertDialog.Header>
            <AlertDialog.Title>{$_('Eliminar usuario')}</AlertDialog.Title>
            <AlertDialog.Description>
                {$_('¿Estás seguro de que deseas eliminar')}
                <strong>{getUserToDelete()?.name}</strong>? {$_(
                    'Esta acción no se puede deshacer.',
                )}
            </AlertDialog.Description>
        </AlertDialog.Header>
        <AlertDialog.Footer>
            <AlertDialog.Cancel>{$_('Cancelar')}</AlertDialog.Cancel>
            <AlertDialog.Action
                onclick={deleteUser}
                class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            >
                {#if $deleteForm.processing}
                    <Loader2 class="mr-2 size-4 animate-spin" />
                {/if}
                {$_('Eliminar')}
            </AlertDialog.Action>
        </AlertDialog.Footer>
    </AlertDialog.Content>
</AlertDialog.Root>

<!-- Toggle Status Confirmation Dialog -->
<AlertDialog.Root
    open={toggleUserId !== null}
    onOpenChange={(open) => {
        if (!open) toggleUserId = null;
    }}
>
    <AlertDialog.Content>
        <AlertDialog.Header>
            <AlertDialog.Title>
                {getUserToToggle()?.disabled_at
                    ? $_('Habilitar usuario')
                    : $_('Deshabilitar usuario')}
            </AlertDialog.Title>
            <AlertDialog.Description>
                {#if getUserToToggle()?.disabled_at}
                    {$_('¿Estás seguro de que deseas habilitar')}
                    <strong>{getUserToToggle()?.name}</strong>? {$_(
                        'Podrán iniciar sesión nuevamente.',
                    )}
                {:else}
                    {$_('¿Estás seguro de que deseas deshabilitar')}
                    <strong>{getUserToToggle()?.name}</strong>? {$_(
                        'Serán desconectados y no podrán acceder al sistema.',
                    )}
                {/if}
            </AlertDialog.Description>
        </AlertDialog.Header>
        <AlertDialog.Footer>
            <AlertDialog.Cancel>{$_('Cancelar')}</AlertDialog.Cancel>
            <AlertDialog.Action onclick={toggleStatus}>
                {#if $toggleForm.processing}
                    <Loader2 class="mr-2 size-4 animate-spin" />
                {/if}
                {getUserToToggle()?.disabled_at
                    ? $_('Habilitar')
                    : $_('Deshabilitar')}
            </AlertDialog.Action>
        </AlertDialog.Footer>
    </AlertDialog.Content>
</AlertDialog.Root>

<!-- Reset Password Confirmation Dialog -->
<AlertDialog.Root
    open={resetUserId !== null}
    onOpenChange={(open) => {
        if (!open) resetUserId = null;
    }}
>
    <AlertDialog.Content>
        <AlertDialog.Header>
            <AlertDialog.Title>{$_('Restablecer contraseña')}</AlertDialog.Title
            >
            <AlertDialog.Description>
                {$_('¿Enviar un correo de restablecimiento de contraseña a')}
                <strong>{getUserToReset()?.email}</strong>?
            </AlertDialog.Description>
        </AlertDialog.Header>
        <AlertDialog.Footer>
            <AlertDialog.Cancel>{$_('Cancelar')}</AlertDialog.Cancel>
            <AlertDialog.Action onclick={resetPassword}>
                {#if $resetForm.processing}
                    <Loader2 class="mr-2 size-4 animate-spin" />
                {/if}
                {$_('Enviar correo de restablecimiento')}
            </AlertDialog.Action>
        </AlertDialog.Footer>
    </AlertDialog.Content>
</AlertDialog.Root>
