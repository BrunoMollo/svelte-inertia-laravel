<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import * as Dialog from '$lib/components/ui/dialog';
    import * as AlertDialog from '$lib/components/ui/alert-dialog';
    import { Link, useForm, router, page } from '@inertiajs/svelte';
    import { onDestroy, onMount } from 'svelte';
    import {
        Plus,
        Users,
        Lock,
        Unlock,
        KeyRound,
        Search,
        X,
    } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';
    import { debounce } from '$lib/utils';
    import {
        createSvelteTable,
        FlexRender,
    } from '$lib/components/ui/data-table';
    import { createColumnHelper, getCoreRowModel } from '@tanstack/table-core';
    import * as Table from '$lib/components/ui/table';

    type Role = {
        id: number;
        name: string;
    };

    type User = {
        id: number;
        name: string;
        email: string;
        disabled_at: string | null;
        created_at: string;
        roles: Role[];
    };

    type Props = {
        users: {
            data: User[];
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
        filters?: {
            search?: string;
            role?: string;
            status?: string;
            per_page?: string;
        };
    };

    const { users, filters = {} }: Props = $props();

    // Filter state from query params
    // Extract values using a function to avoid reactivity warnings
    function getFilterValue(key: keyof NonNullable<Props['filters']>): string {
        return filters?.[key] || '';
    }

    let filterSearch = $state(getFilterValue('search'));
    let filterRole = $state(getFilterValue('role'));
    let filterStatus = $state(getFilterValue('status'));
    let perPage = $state(getFilterValue('per_page') || '25');

    let createDialogOpen = $state(false);
    let changePasswordDialogOpen = $state(false);
    let changePasswordConfirmOpen = $state(false);
    let selectedUserForPasswordChange = $state<User | null>(null);
    let selectedUserIdForPasswordChange = $state<number | null>(null);

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'teacher' as 'teacher' | 'student',
    });

    const changePasswordForm = useForm({
        password: '',
        password_confirmation: '',
    });

    function getCurrentUrlSearchParams(): URLSearchParams {
        const url = new URL($page.url, 'http://localhost');
        return new URLSearchParams(url.search);
    }

    function replaceBrowserUrlSearchParams(params: URLSearchParams) {
        if (typeof window === 'undefined') return;

        const query = params.toString();
        const nextUrl =
            window.location.pathname +
            (query ? `?${query}` : '') +
            window.location.hash;
        window.history.replaceState({}, '', nextUrl);
    }

    function setChangePasswordUserQueryParam(userId: number | null) {
        const params =
            typeof window === 'undefined'
                ? getCurrentUrlSearchParams()
                : new URLSearchParams(window.location.search);

        if (userId === null) {
            params.delete('change_password_user');
        } else {
            params.set('change_password_user', String(userId));
        }

        replaceBrowserUrlSearchParams(params);
    }

    function submitCreateUser(e: SubmitEvent) {
        e.preventDefault();
        $form.post('/admin/users', {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User created successfully');
                createDialogOpen = false;
                $form.reset();
            },
            onError: () => {
                toast.error('Failed to create user');
            },
        });
    }

    function closeCreateDialog() {
        createDialogOpen = false;
        $form.reset();
    }

    function disableUser(user: User) {
        if (confirm(`Are you sure you want to disable ${user.name}?`)) {
            router.post(
                `/admin/users/${user.id}/disable`,
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.success('User disabled successfully');
                    },
                    onError: () => {
                        toast.error('Failed to disable user');
                    },
                },
            );
        }
    }

    function enableUser(user: User) {
        router.post(
            `/admin/users/${user.id}/enable`,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('User enabled successfully');
                },
                onError: () => {
                    toast.error('Failed to enable user');
                },
            },
        );
    }

    function openChangePasswordDialog(user: User) {
        selectedUserForPasswordChange = user;
        selectedUserIdForPasswordChange = user.id;
        changePasswordDialogOpen = true;
        changePasswordConfirmOpen = false;
        $changePasswordForm.reset();
        $changePasswordForm.clearErrors();
        setChangePasswordUserQueryParam(user.id);
    }

    function closeChangePasswordDialog() {
        changePasswordDialogOpen = false;
        changePasswordConfirmOpen = false;
        selectedUserForPasswordChange = null;
        selectedUserIdForPasswordChange = null;
        $changePasswordForm.reset();
        $changePasswordForm.clearErrors();
        setChangePasswordUserQueryParam(null);
    }

    function requestConfirmChangePassword(e: SubmitEvent) {
        e.preventDefault();
        changePasswordConfirmOpen = true;
    }

    function confirmChangePassword() {
        const userId = selectedUserIdForPasswordChange;
        if (!userId) return;

        $changePasswordForm.post(`/admin/users/${userId}/change-password`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Password updated successfully');
                closeChangePasswordDialog();
            },
            onError: () => {
                changePasswordConfirmOpen = false;
                toast.error('Failed to update password');
            },
        });
    }

    function formatDate(dateString: string | null): string {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString();
    }

    function getUserRole(user: User): string {
        return user.roles?.[0]?.name ?? 'N/A';
    }

    // Column definitions for DataTable
    const columnHelper = createColumnHelper<User>();

    const columns = [
        columnHelper.accessor('name', {
            header: 'Name',
        }),
        columnHelper.accessor('email', {
            header: 'Email',
        }),
        columnHelper.display({
            id: 'role',
            header: 'Role',
        }),
        columnHelper.display({
            id: 'status',
            header: 'Status',
        }),
        columnHelper.display({
            id: 'created_at',
            header: 'Created',
        }),
        columnHelper.display({
            id: 'actions',
            header: 'Actions',
        }),
    ];

    // Create table - recreate when data changes to ensure reactivity
    const table = $derived.by(() => {
        return createSvelteTable({
            data: users.data,
            columns,
            getCoreRowModel: getCoreRowModel(),
        });
    });

    function buildQueryParams(page?: number): string {
        const params = getCurrentUrlSearchParams();

        if (filterSearch) params.set('search', filterSearch);
        if (filterRole) params.set('role', filterRole);
        if (filterStatus) params.set('status', filterStatus);
        if (perPage && perPage !== '25') params.set('per_page', perPage);
        if (page && page > 1) params.set('page', page.toString());
        if (changePasswordDialogOpen && selectedUserIdForPasswordChange) {
            params.set(
                'change_password_user',
                String(selectedUserIdForPasswordChange),
            );
        }

        return params.toString() ? `?${params.toString()}` : '';
    }

    function applyFilters() {
        router.get(
            '/admin/users' + buildQueryParams(),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    const DEBOUNCE_DELAY = 120;

    // Debounced filter application for text inputs
    const debouncedApplyFilters = debounce(applyFilters, DEBOUNCE_DELAY);

    function handleSearchChange(value: string) {
        filterSearch = value;
        debouncedApplyFilters();
    }

    function handleRoleChange(value: string) {
        filterRole = value;
        applyFilters();
    }

    function handleStatusChange(value: string) {
        filterStatus = value;
        applyFilters();
    }

    function handlePerPageChange(value: string) {
        perPage = value;
        // Reset to page 1 when changing per_page
        router.get(
            '/admin/users' + buildQueryParams(1),
            {},
            {
                preserveState: true,
                preserveScroll: false,
                replace: true,
            },
        );
    }

    function clearFilters() {
        filterSearch = '';
        filterRole = '';
        filterStatus = '';
        perPage = '25';
        router.get(
            '/admin/users',
            {},
            {
                preserveState: false,
                preserveScroll: false,
                replace: true,
            },
        );
    }

    const hasActiveFilters = $derived(
        !!filterSearch || !!filterRole || !!filterStatus,
    );

    function syncChangePasswordModalFromUrl() {
        const params = getCurrentUrlSearchParams();
        const userIdParam = params.get('change_password_user');

        if (!userIdParam) {
            if (changePasswordDialogOpen) closeChangePasswordDialog();
            return;
        }

        const userId = Number(userIdParam);
        if (!Number.isFinite(userId) || userId <= 0) return;

        const user = users.data.find((u) => u.id === userId) ?? null;
        const isNewSelection = selectedUserIdForPasswordChange !== userId;

        selectedUserIdForPasswordChange = userId;
        selectedUserForPasswordChange = user;
        changePasswordDialogOpen = true;

        if (isNewSelection) {
            changePasswordConfirmOpen = false;
            $changePasswordForm.reset();
            $changePasswordForm.clearErrors();
        }
    }

    onMount(() => {
        syncChangePasswordModalFromUrl();
        window.addEventListener('popstate', syncChangePasswordModalFromUrl);
    });

    onDestroy(() => {
        window.removeEventListener('popstate', syncChangePasswordModalFromUrl);
    });

    let lastKnownUrl = $state('');
    $effect(() => {
        if ($page.url !== lastKnownUrl) {
            lastKnownUrl = $page.url;
            syncChangePasswordModalFromUrl();
        }
    });
</script>

<svelte:head>
    <title>Users</title>
</svelte:head>

<AuthenticatedLayout>
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Users</h1>
                <p class="text-muted-foreground">
                    Manage users and their access to the platform
                </p>
            </div>
            <Dialog.Root bind:open={createDialogOpen}>
                <Dialog.Trigger>
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        New User
                    </Button>
                </Dialog.Trigger>
                <Dialog.Content
                    class="sm:max-w-[500px]"
                    onCloseAutoFocus={closeCreateDialog}
                >
                    <Dialog.Header>
                        <Dialog.Title>Create New User</Dialog.Title>
                    </Dialog.Header>
                    <form
                        onsubmit={submitCreateUser}
                        class="flex flex-col gap-4"
                    >
                        <div class="flex flex-col gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                type="text"
                                bind:value={$form.name}
                                required
                                placeholder="John Doe"
                            />
                            <ErrorFeedback message={$form.errors.name} />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                bind:value={$form.email}
                                required
                                placeholder="john.doe@example.com"
                            />
                            <ErrorFeedback message={$form.errors.email} />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="role">Role</Label>
                            <select
                                id="role"
                                bind:value={$form.role}
                                required
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                            <ErrorFeedback message={$form.errors.role} />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                bind:value={$form.password}
                                required
                                placeholder="••••••••"
                            />
                            <ErrorFeedback message={$form.errors.password} />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="password_confirmation"
                                >Confirm Password</Label
                            >
                            <Input
                                id="password_confirmation"
                                type="password"
                                bind:value={$form.password_confirmation}
                                required
                                placeholder="••••••••"
                            />
                            <ErrorFeedback
                                message={$form.errors.password_confirmation}
                            />
                        </div>

                        <Dialog.Footer>
                            <Button
                                type="button"
                                variant="outline"
                                onclick={closeCreateDialog}
                                disabled={$form.processing}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={$form.processing}>
                                {#if $form.processing}
                                    Creating...
                                {:else}
                                    Create User
                                {/if}
                            </Button>
                        </Dialog.Footer>
                    </form>
                </Dialog.Content>
            </Dialog.Root>
        </div>

        <!-- Change password -->
        <Dialog.Root bind:open={changePasswordDialogOpen}>
            <Dialog.Content
                onCloseAutoFocus={closeChangePasswordDialog}
                class="sm:max-w-[500px]"
                data-testid="admin-change-user-password-dialog"
            >
                <Dialog.Header>
                    <Dialog.Title>Change user password</Dialog.Title>
                </Dialog.Header>

                <form
                    onsubmit={requestConfirmChangePassword}
                    class="flex flex-col gap-4"
                    data-testid="admin-change-user-password-form"
                >
                    <p class="text-sm text-muted-foreground">
                        {#if selectedUserForPasswordChange}
                            This will update the password for <span
                                class="font-medium"
                                >{selectedUserForPasswordChange.name}</span
                            >
                            and send them an email notification.
                        {:else if selectedUserIdForPasswordChange}
                            This will update the password for user ID <span
                                class="font-medium"
                                >{selectedUserIdForPasswordChange}</span
                            >
                            and send them an email notification.
                        {/if}
                    </p>

                    <div class="flex flex-col gap-2">
                        <Label for="change_password">New Password</Label>
                        <Input
                            id="change_password"
                            type="password"
                            bind:value={$changePasswordForm.password}
                            required
                            placeholder="••••••••"
                            data-testid="admin-change-user-password-input"
                        />
                        <ErrorFeedback
                            message={$changePasswordForm.errors.password}
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="change_password_confirmation">
                            Confirm Password
                        </Label>
                        <Input
                            id="change_password_confirmation"
                            type="password"
                            bind:value={
                                $changePasswordForm.password_confirmation
                            }
                            required
                            placeholder="••••••••"
                            data-testid="admin-change-user-password-confirmation-input"
                        />
                        <ErrorFeedback
                            message={$changePasswordForm.errors
                                .password_confirmation}
                        />
                    </div>

                    <Dialog.Footer>
                        <Button
                            type="button"
                            variant="outline"
                            onclick={closeChangePasswordDialog}
                            disabled={$changePasswordForm.processing}
                            data-testid="admin-change-user-password-cancel"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            disabled={$changePasswordForm.processing}
                            data-testid="admin-change-user-password-submit"
                        >
                            Continue
                        </Button>
                    </Dialog.Footer>
                </form>

                <AlertDialog.Root bind:open={changePasswordConfirmOpen}>
                    <AlertDialog.Content
                        data-testid="admin-change-user-password-confirm-dialog"
                    >
                        <AlertDialog.Header>
                            <AlertDialog.Title
                                >Confirm password change</AlertDialog.Title
                            >
                            <AlertDialog.Description>
                                {#if selectedUserForPasswordChange}
                                    Are you sure you want to change the password
                                    for {selectedUserForPasswordChange.name}?
                                    They will receive an email notification.
                                {:else if selectedUserIdForPasswordChange}
                                    Are you sure you want to change the password
                                    for user ID {selectedUserIdForPasswordChange}?
                                    They will receive an email notification.
                                {/if}
                            </AlertDialog.Description>
                        </AlertDialog.Header>
                        <AlertDialog.Footer>
                            <AlertDialog.Cancel
                                data-testid="admin-change-user-password-confirm-cancel"
                            >
                                Cancel
                            </AlertDialog.Cancel>
                            <AlertDialog.Action
                                onclick={confirmChangePassword}
                                disabled={$changePasswordForm.processing}
                                data-testid="admin-change-user-password-confirm-action"
                            >
                                Confirm
                            </AlertDialog.Action>
                        </AlertDialog.Footer>
                    </AlertDialog.Content>
                </AlertDialog.Root>
            </Dialog.Content>
        </Dialog.Root>

        <!-- Filters -->
        <div class="rounded-lg border bg-card p-4">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    {#if hasActiveFilters}
                        <Button
                            variant="ghost"
                            size="sm"
                            onclick={clearFilters}
                        >
                            <X class="mr-2 h-4 w-4" />
                            Clear Filters
                        </Button>
                    {/if}
                </div>
                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div class="flex flex-col gap-2">
                        <Label for="filter-search">Search</Label>
                        <div class="relative">
                            <Search
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="filter-search"
                                type="text"
                                value={filterSearch}
                                oninput={(e) =>
                                    handleSearchChange(
                                        (e.target as HTMLInputElement).value,
                                    )}
                                placeholder="Search by name or email..."
                                class="pl-9"
                            />
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Label for="filter-role">Role</Label>
                        <select
                            id="filter-role"
                            value={filterRole}
                            onchange={(e) =>
                                handleRoleChange(
                                    (e.target as HTMLSelectElement).value,
                                )}
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">All Roles</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <Label for="filter-status">Status</Label>
                        <select
                            id="filter-status"
                            value={filterStatus}
                            onchange={(e) =>
                                handleStatusChange(
                                    (e.target as HTMLSelectElement).value,
                                )}
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card">
            <div class="overflow-x-auto">
                <Table.Root>
                    <Table.Header>
                        {#each table.getHeaderGroups() as headerGroup (headerGroup.id)}
                            <Table.Row>
                                {#each headerGroup.headers as header (header.id)}
                                    <Table.Head
                                        class={header.id === 'actions'
                                            ? 'text-right'
                                            : ''}
                                        colspan={header.colSpan}
                                    >
                                        {#if !header.isPlaceholder}
                                            <FlexRender
                                                content={header.column.columnDef
                                                    .header}
                                                context={header.getContext()}
                                            />
                                        {/if}
                                    </Table.Head>
                                {/each}
                            </Table.Row>
                        {/each}
                    </Table.Header>
                    <Table.Body>
                        {#if table.getRowModel().rows.length === 0}
                            <Table.Row>
                                <Table.Cell
                                    colspan={columns.length}
                                    class="h-24 text-center"
                                >
                                    No users found
                                </Table.Cell>
                            </Table.Row>
                        {:else}
                            {#each table.getRowModel().rows as row (row.id)}
                                <Table.Row>
                                    {#each row.getVisibleCells() as cell (cell.id)}
                                        <Table.Cell
                                            class={[
                                                cell.column.id === 'actions'
                                                    ? 'text-right'
                                                    : '',
                                                cell.column.id === 'email' ||
                                                cell.column.id === 'created_at'
                                                    ? 'text-sm'
                                                    : '',
                                                cell.column.id === 'created_at'
                                                    ? 'text-muted-foreground'
                                                    : '',
                                            ]
                                                .filter(Boolean)
                                                .join(' ')}
                                        >
                                            {#if cell.column.id === 'name'}
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <Users
                                                        class="h-4 w-4 text-muted-foreground"
                                                    />
                                                    <span class="font-medium"
                                                        >{cell.row.original
                                                            .name}</span
                                                    >
                                                </div>
                                            {:else if cell.column.id === 'email'}
                                                <span class="text-sm"
                                                    >{cell.row.original
                                                        .email}</span
                                                >
                                            {:else if cell.column.id === 'role'}
                                                <span
                                                    class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                                >
                                                    {getUserRole(
                                                        cell.row.original,
                                                    )}
                                                </span>
                                            {:else if cell.column.id === 'status'}
                                                {#if cell.row.original.disabled_at}
                                                    <span
                                                        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200"
                                                    >
                                                        Disabled
                                                    </span>
                                                {:else}
                                                    <span
                                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                                                    >
                                                        Active
                                                    </span>
                                                {/if}
                                            {:else if cell.column.id === 'created_at'}
                                                <span
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {formatDate(
                                                        cell.row.original
                                                            .created_at,
                                                    )}
                                                </span>
                                            {:else if cell.column.id === 'actions'}
                                                <div
                                                    class="flex items-center justify-end gap-2"
                                                >
                                                    {#if cell.row.original.disabled_at}
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onclick={() =>
                                                                enableUser(
                                                                    cell.row
                                                                        .original,
                                                                )}
                                                        >
                                                            <Unlock
                                                                class="h-4 w-4"
                                                            />
                                                        </Button>
                                                    {:else}
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onclick={() =>
                                                                disableUser(
                                                                    cell.row
                                                                        .original,
                                                                )}
                                                        >
                                                            <Lock
                                                                class="h-4 w-4"
                                                            />
                                                        </Button>
                                                    {/if}
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onclick={() =>
                                                            openChangePasswordDialog(
                                                                cell.row
                                                                    .original,
                                                            )}
                                                        data-testid={`admin-user-change-password-${cell.row.original.id}`}
                                                    >
                                                        <KeyRound
                                                            class="h-4 w-4"
                                                        />
                                                    </Button>
                                                </div>
                                            {:else}
                                                <FlexRender
                                                    content={cell.column
                                                        .columnDef.cell}
                                                    context={cell.getContext()}
                                                />
                                            {/if}
                                        </Table.Cell>
                                    {/each}
                                </Table.Row>
                            {/each}
                        {/if}
                    </Table.Body>
                </Table.Root>
            </div>

            <div
                class="flex flex-col items-center justify-between gap-4 border-t px-6 py-4 sm:flex-row"
            >
                <div class="text-sm text-muted-foreground">
                    Showing {users.data.length} of {users.total} users
                    {#if users.total > 0}
                        (Page {users.current_page} of {users.last_page})
                    {/if}
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <select
                            id="per-page"
                            value={perPage}
                            onchange={(e) =>
                                handlePerPageChange(
                                    (e.target as HTMLSelectElement).value,
                                )}
                            class="flex h-9 rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    {#if users.last_page > 1}
                        <div class="flex gap-2">
                            {#if users.current_page > 1}
                                <Link
                                    href={`/admin/users${buildQueryParams(users.current_page - 1)}`}
                                >
                                    <Button variant="outline" size="sm"
                                        >Previous</Button
                                    >
                                </Link>
                            {/if}
                            {#if users.current_page < users.last_page}
                                <Link
                                    href={`/admin/users${buildQueryParams(users.current_page + 1)}`}
                                >
                                    <Button variant="outline" size="sm"
                                        >Next</Button
                                    >
                                </Link>
                            {/if}
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</AuthenticatedLayout>
