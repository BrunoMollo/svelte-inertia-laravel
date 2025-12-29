<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import AdminUsersChangePasswordDialog from './components/admin-users-change-password-dialog.svelte';
    import AdminUsersCreateUserDialog from './components/admin-users-create-user-dialog.svelte';
    import AdminUsersTable from './components/admin-users-table.svelte';
    import { page } from '@inertiajs/svelte';
    import { onDestroy, onMount } from 'svelte';

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

    let changePasswordDialogOpen = $state(false);
    let selectedUserForPasswordChange = $state<User | null>(null);
    let selectedUserIdForPasswordChange = $state<number | null>(null);

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

    function openChangePasswordDialog(user: User) {
        selectedUserForPasswordChange = user;
        selectedUserIdForPasswordChange = user.id;
        changePasswordDialogOpen = true;
        setChangePasswordUserQueryParam(user.id);
    }

    function closeChangePasswordDialog() {
        changePasswordDialogOpen = false;
        selectedUserForPasswordChange = null;
        selectedUserIdForPasswordChange = null;
        setChangePasswordUserQueryParam(null);
    }

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
            // Dialog internal state will reset when the selection changes.
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
            <AdminUsersCreateUserDialog />
        </div>

        <AdminUsersChangePasswordDialog
            bind:open={changePasswordDialogOpen}
            selectedUser={selectedUserForPasswordChange}
            selectedUserId={selectedUserIdForPasswordChange}
            onClose={closeChangePasswordDialog}
        />

        <AdminUsersTable
            {users}
            {filters}
            onOpenChangePasswordDialog={openChangePasswordDialog}
        />
    </div>
</AuthenticatedLayout>
