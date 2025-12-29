<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { Button } from '$lib/components/ui/button';
    import { Link } from '@inertiajs/svelte';
    import { Plus, Building2, Edit, Trash2, Eye } from '@lucide/svelte';
    import { router } from '@inertiajs/svelte';

    type Organization = {
        id: number;
        name: string;
        contact_name: string;
        contact_email: string;
        contact_phone: string | null;
        activated_at: string | null;
        created_at: string;
        updated_at: string;
    };

    type Props = {
        organizations: {
            data: Organization[];
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };

    const { organizations }: Props = $props();

    function deleteOrganization(id: number) {
        if (confirm('Are you sure you want to delete this organization?')) {
            router.delete(`/admin/organizations/${id}`);
        }
    }

    function formatDate(dateString: string | null): string {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString();
    }
</script>

<svelte:head>
    <title>Organizations</title>
</svelte:head>

<AuthenticatedLayout>
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Organizations</h1>
                <p class="text-muted-foreground">
                    Manage corporate organizations and their members
                </p>
            </div>
            <Link href="/admin/organizations/create">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    New Organization
                </Button>
            </Link>
        </div>

        <div class="rounded-lg border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-6 py-3 text-left text-sm font-medium">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Contact</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Created</th>
                            <th class="px-6 py-3 text-right text-sm font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each organizations.data as organization (organization.id)}
                            <tr class="border-b hover:bg-muted/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Building2 class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">{organization.name}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm">{organization.contact_name}</span>
                                        <span class="text-xs text-muted-foreground">
                                            {organization.contact_email}
                                        </span>
                                        {#if organization.contact_phone}
                                            <span class="text-xs text-muted-foreground">
                                                {organization.contact_phone}
                                            </span>
                                        {/if}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {#if organization.activated_at}
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Active
                                        </span>
                                    {:else}
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Blocked
                                        </span>
                                    {/if}
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">
                                    {formatDate(organization.created_at)}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link href={`/admin/organizations/${organization.id}`}>
                                            <Button variant="ghost" size="sm">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link href={`/admin/organizations/${organization.id}/edit`}>
                                            <Button variant="ghost" size="sm">
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onclick={() => deleteOrganization(organization.id)}
                                        >
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-muted-foreground">
                                    No organizations found
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            {#if organizations.last_page > 1}
                <div class="flex items-center justify-between border-t px-6 py-4">
                    <div class="text-sm text-muted-foreground">
                        Showing {organizations.data.length} of {organizations.total} organizations
                    </div>
                    <div class="flex gap-2">
                        {#if organizations.current_page > 1}
                            <Link href={`/admin/organizations?page=${organizations.current_page - 1}`}>
                                <Button variant="outline" size="sm">Previous</Button>
                            </Link>
                        {/if}
                        {#if organizations.current_page < organizations.last_page}
                            <Link href={`/admin/organizations?page=${organizations.current_page + 1}`}>
                                <Button variant="outline" size="sm">Next</Button>
                            </Link>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</AuthenticatedLayout>

