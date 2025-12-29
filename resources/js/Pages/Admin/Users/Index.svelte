<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import * as Dialog from '$lib/components/ui/dialog';
    import { Link, useForm, router } from '@inertiajs/svelte';
    import { Plus, Users, Lock, Unlock, KeyRound } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';

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
    };

    const { users }: Props = $props();

    let createDialogOpen = $state(false);

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'teacher' as 'teacher' | 'student',
    });

    function submitCreateUser(e: SubmitEvent) {
        e.preventDefault();
        $form.post('/admin/users', {
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
                onSuccess: () => {
                    toast.success('User enabled successfully');
                },
                onError: () => {
                    toast.error('Failed to enable user');
                },
            },
        );
    }

    function forcePasswordReset(user: User) {
        if (
            confirm(
                `Are you sure you want to force password reset for ${user.name}?`,
            )
        ) {
            router.post(
                `/admin/users/${user.id}/force-password-reset`,
                {},
                {
                    onSuccess: () => {
                        toast.success('Password reset email sent successfully');
                    },
                    onError: () => {
                        toast.error('Failed to send password reset email');
                    },
                },
            );
        }
    }

    function formatDate(dateString: string | null): string {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString();
    }

    function getUserRole(user: User): string {
        return user.roles?.[0]?.name ?? 'N/A';
    }
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
                <Dialog.Content class="sm:max-w-[500px]">
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

        <div class="rounded-lg border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-6 py-3 text-left text-sm font-medium"
                                >Name</th
                            >
                            <th class="px-6 py-3 text-left text-sm font-medium"
                                >Email</th
                            >
                            <th class="px-6 py-3 text-left text-sm font-medium"
                                >Role</th
                            >
                            <th class="px-6 py-3 text-left text-sm font-medium"
                                >Status</th
                            >
                            <th class="px-6 py-3 text-left text-sm font-medium"
                                >Created</th
                            >
                            <th class="px-6 py-3 text-right text-sm font-medium"
                                >Actions</th
                            >
                        </tr>
                    </thead>
                    <tbody>
                        {#each users.data as user (user.id)}
                            <tr class="border-b hover:bg-muted/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Users
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        <span class="font-medium"
                                            >{user.name}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm">{user.email}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                    >
                                        {getUserRole(user)}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {#if user.disabled_at}
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
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-muted-foreground"
                                >
                                    {formatDate(user.created_at)}
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        {#if user.disabled_at}
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() => enableUser(user)}
                                            >
                                                <Unlock class="h-4 w-4" />
                                            </Button>
                                        {:else}
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() =>
                                                    disableUser(user)}
                                            >
                                                <Lock class="h-4 w-4" />
                                            </Button>
                                        {/if}
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onclick={() =>
                                                forcePasswordReset(user)}
                                        >
                                            <KeyRound class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-6 py-8 text-center text-muted-foreground"
                                >
                                    No users found
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            {#if users.last_page > 1}
                <div
                    class="flex items-center justify-between border-t px-6 py-4"
                >
                    <div class="text-sm text-muted-foreground">
                        Showing {users.data.length} of {users.total} users
                    </div>
                    <div class="flex gap-2">
                        {#if users.current_page > 1}
                            <Link
                                href={`/admin/users?page=${users.current_page - 1}`}
                            >
                                <Button variant="outline" size="sm"
                                    >Previous</Button
                                >
                            </Link>
                        {/if}
                        {#if users.current_page < users.last_page}
                            <Link
                                href={`/admin/users?page=${users.current_page + 1}`}
                            >
                                <Button variant="outline" size="sm">Next</Button
                                >
                            </Link>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</AuthenticatedLayout>
