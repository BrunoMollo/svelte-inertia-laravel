<script lang="ts">
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
    import * as Table from '$lib/components/ui/table';
    import {
        createSvelteTable,
        FlexRender,
    } from '$lib/components/ui/data-table';
    import { debounce } from '$lib/utils';
    import { router, page } from '@inertiajs/svelte';
    import { createColumnHelper, getCoreRowModel } from '@tanstack/table-core';
    import {
        ChevronDown,
        KeyRound,
        Lock,
        Search,
        Unlock,
        Users,
        X,
    } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';

    type Role = {
        id: number;
        name: string;
    };

    export type AdminUsersTableUser = {
        id: number;
        name: string;
        email: string;
        disabled_at: string | null;
        created_at: string;
        roles: Role[];
    };

    type Props = {
        users: {
            data: AdminUsersTableUser[];
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
        onOpenChangePasswordDialog: (user: AdminUsersTableUser) => void;
    };

    const { users, filters = {}, onOpenChangePasswordDialog }: Props = $props();

    // Filter state from query params
    function getFilterValue(key: keyof NonNullable<Props['filters']>): string {
        return filters?.[key] || '';
    }

    let filterSearch = $state(getFilterValue('search'));
    let filterRole = $state(getFilterValue('role'));
    let filterStatus = $state(getFilterValue('status'));
    let perPage = $state(getFilterValue('per_page') || '25');

    function getCurrentUrlSearchParams(): URLSearchParams {
        const url = new URL($page.url, 'http://localhost');
        return new URLSearchParams(url.search);
    }

    function buildQueryParams(pageNumber?: number): string {
        const params = getCurrentUrlSearchParams();

        if (filterSearch) params.set('search', filterSearch);
        else params.delete('search');

        if (filterRole) params.set('role', filterRole);
        else params.delete('role');

        if (filterStatus) params.set('status', filterStatus);
        else params.delete('status');

        if (perPage && perPage !== '25') params.set('per_page', perPage);
        else params.delete('per_page');

        if (pageNumber && pageNumber > 1)
            params.set('page', pageNumber.toString());
        else params.delete('page');

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

    function disableUser(user: AdminUsersTableUser) {
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

    function enableUser(user: AdminUsersTableUser) {
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

    function formatDate(dateString: string | null): string {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString();
    }

    function getUserRole(user: AdminUsersTableUser): string {
        return user.roles?.[0]?.name ?? 'N/A';
    }

    // Column definitions for DataTable
    const columnHelper = createColumnHelper<AdminUsersTableUser>();

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

    const table = $derived.by(() => {
        return createSvelteTable({
            data: users.data,
            columns,
            getCoreRowModel: getCoreRowModel(),
        });
    });
</script>

<!-- Filters -->
<div class="rounded-lg border bg-card p-4" data-testid="admin-users-filters">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            {#if hasActiveFilters}
                <Button
                    variant="ghost"
                    size="sm"
                    onclick={clearFilters}
                    data-testid="admin-users-clear-filters"
                >
                    <X class="mr-2 h-4 w-4" />
                    Clear Filters
                </Button>
            {/if}
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="flex flex-col gap-2">
                <Label for="filter-search">Search</Label>
                <div class="flex items-center gap-2">
                    <div class="relative w-full">
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
                            placeholder={'Search by name or email...'}
                            class="pl-9"
                            data-testid="admin-users-filter-search"
                        />
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <Label for="filter-role">Role</Label>
                <DropdownMenu.Root>
                    <DropdownMenu.Trigger>
                        <Button
                            variant="outline"
                            class="w-full justify-between"
                            data-testid="admin-users-filter-role-trigger"
                        >
                            {filterRole
                                ? filterRole.charAt(0).toUpperCase() +
                                  filterRole.slice(1)
                                : 'All Roles'}
                            <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenu.Trigger>
                    <DropdownMenu.Content align="start" class="w-[220px]">
                        <DropdownMenu.Item
                            onclick={() => handleRoleChange('')}
                            data-testid="admin-users-filter-role-all"
                        >
                            All Roles
                        </DropdownMenu.Item>
                        <DropdownMenu.Separator />
                        <DropdownMenu.Item
                            onclick={() => handleRoleChange('superadmin')}
                            data-testid="admin-users-filter-role-superadmin"
                        >
                            Superadmin
                        </DropdownMenu.Item>
                        <DropdownMenu.Item
                            onclick={() => handleRoleChange('teacher')}
                            data-testid="admin-users-filter-role-teacher"
                        >
                            Teacher
                        </DropdownMenu.Item>
                        <DropdownMenu.Item
                            onclick={() => handleRoleChange('student')}
                            data-testid="admin-users-filter-role-student"
                        >
                            Student
                        </DropdownMenu.Item>
                    </DropdownMenu.Content>
                </DropdownMenu.Root>
            </div>
            <div class="flex flex-col gap-2">
                <Label for="filter-status">Status</Label>
                <DropdownMenu.Root>
                    <DropdownMenu.Trigger>
                        <Button
                            variant="outline"
                            class="w-full justify-between"
                            data-testid="admin-users-filter-status-trigger"
                        >
                            {filterStatus
                                ? filterStatus.charAt(0).toUpperCase() +
                                  filterStatus.slice(1)
                                : 'All Status'}
                            <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenu.Trigger>
                    <DropdownMenu.Content align="start" class="w-[220px]">
                        <DropdownMenu.Item
                            onclick={() => handleStatusChange('')}
                            data-testid="admin-users-filter-status-all"
                        >
                            All Status
                        </DropdownMenu.Item>
                        <DropdownMenu.Separator />
                        <DropdownMenu.Item
                            onclick={() => handleStatusChange('active')}
                            data-testid="admin-users-filter-status-active"
                        >
                            Active
                        </DropdownMenu.Item>
                        <DropdownMenu.Item
                            onclick={() => handleStatusChange('disabled')}
                            data-testid="admin-users-filter-status-disabled"
                        >
                            Disabled
                        </DropdownMenu.Item>
                    </DropdownMenu.Content>
                </DropdownMenu.Root>
            </div>
        </div>
    </div>
</div>

<!-- Users table -->
<div class="rounded-lg border bg-card" data-testid="admin-users-table">
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
                                        content={header.column.columnDef.header}
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
                                        <div class="flex items-center gap-2">
                                            <Users
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                            <span class="font-medium"
                                                >{cell.row.original.name}</span
                                            >
                                        </div>
                                    {:else if cell.column.id === 'email'}
                                        <span class="text-sm"
                                            >{cell.row.original.email}</span
                                        >
                                    {:else if cell.column.id === 'role'}
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                        >
                                            {getUserRole(cell.row.original)}
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
                                                cell.row.original.created_at,
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
                                                            cell.row.original,
                                                        )}
                                                    data-testid={`admin-user-enable-${cell.row.original.id}`}
                                                >
                                                    <Unlock class="h-4 w-4" />
                                                </Button>
                                            {:else}
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onclick={() =>
                                                        disableUser(
                                                            cell.row.original,
                                                        )}
                                                    data-testid={`admin-user-disable-${cell.row.original.id}`}
                                                >
                                                    <Lock class="h-4 w-4" />
                                                </Button>
                                            {/if}
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() =>
                                                    onOpenChangePasswordDialog(
                                                        cell.row.original,
                                                    )}
                                                data-testid={`admin-user-change-password-${cell.row.original.id}`}
                                            >
                                                <KeyRound class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    {:else}
                                        <FlexRender
                                            content={cell.column.columnDef.cell}
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
        data-testid="admin-users-pagination"
    >
        <div class="text-sm text-muted-foreground">
            Showing {users.data.length} of {users.total} users
            {#if users.total > 0}
                (Page {users.current_page} of {users.last_page})
            {/if}
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <DropdownMenu.Root>
                    <DropdownMenu.Trigger>
                        <Button
                            variant="outline"
                            size="sm"
                            class="justify-between"
                            data-testid="admin-users-per-page"
                        >
                            {perPage} per page
                            <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenu.Trigger>
                    <DropdownMenu.Content align="end" class="w-[160px]">
                        {#each [10, 25, 50, 100] as perPageOption}
                            <DropdownMenu.Item
                                onclick={() =>
                                    handlePerPageChange(
                                        perPageOption.toString(),
                                    )}
                                data-testid="admin-users-per-page-10"
                            >
                                {perPageOption} per page
                            </DropdownMenu.Item>
                        {/each}
                    </DropdownMenu.Content>
                </DropdownMenu.Root>
            </div>
            {#if users.last_page > 1}
                <div class="flex gap-2">
                    {#if users.current_page > 1}
                        <Button
                            variant="outline"
                            size="sm"
                            onclick={() =>
                                router.get(
                                    '/admin/users' +
                                        buildQueryParams(
                                            users.current_page - 1,
                                        ),
                                    {},
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                    },
                                )}
                            data-testid="admin-users-prev-page"
                        >
                            Previous
                        </Button>
                    {/if}
                    {#if users.current_page < users.last_page}
                        <Button
                            variant="outline"
                            size="sm"
                            onclick={() =>
                                router.get(
                                    '/admin/users' +
                                        buildQueryParams(
                                            users.current_page + 1,
                                        ),
                                    {},
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                    },
                                )}
                            data-testid="admin-users-next-page"
                        >
                            Next
                        </Button>
                    {/if}
                </div>
            {/if}
        </div>
    </div>
</div>
