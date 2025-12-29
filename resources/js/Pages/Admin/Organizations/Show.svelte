<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { Button } from '$lib/components/ui/button';
    import { Link } from '@inertiajs/svelte';
    import { ArrowLeft, Edit, Building2, Mail, Phone, Calendar, Users } from '@lucide/svelte';

    type User = {
        id: number;
        name: string;
        email: string;
    };

    type Organization = {
        id: number;
        name: string;
        contact_name: string;
        contact_email: string;
        contact_phone: string | null;
        activated_at: string | null;
        created_at: string;
        updated_at: string;
        users?: User[];
    };

    type Props = {
        organization: Organization;
    };

    const { organization }: Props = $props();

    function formatDate(dateString: string | null): string {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    }
</script>

<svelte:head>
    <title>{organization.name}</title>
</svelte:head>

<AuthenticatedLayout>
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link href="/admin/organizations">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold">{organization.name}</h1>
                    <p class="text-muted-foreground">Organization details</p>
                </div>
            </div>
            <Link href={`/admin/organizations/${organization.id}/edit`}>
                <Button>
                    <Edit class="mr-2 h-4 w-4" />
                    Edit
                </Button>
            </Link>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-lg border bg-card p-6">
                <h2 class="mb-4 text-lg font-semibold">Organization Information</h2>
                <div class="flex flex-col gap-4">
                    <div class="flex items-start gap-3">
                        <Building2 class="mt-1 h-5 w-5 text-muted-foreground" />
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-muted-foreground">Name</span>
                            <span class="text-base">{organization.name}</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Calendar class="mt-1 h-5 w-5 text-muted-foreground" />
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-muted-foreground">Status</span>
                            {#if organization.activated_at}
                                <span class="inline-flex w-fit items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            {:else}
                                <span class="inline-flex w-fit items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Blocked
                                </span>
                            {/if}
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Calendar class="mt-1 h-5 w-5 text-muted-foreground" />
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-muted-foreground">Created</span>
                            <span class="text-base">{formatDate(organization.created_at)}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border bg-card p-6">
                <h2 class="mb-4 text-lg font-semibold">Contact Information</h2>
                <div class="flex flex-col gap-4">
                    <div class="flex items-start gap-3">
                        <Users class="mt-1 h-5 w-5 text-muted-foreground" />
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-muted-foreground">Contact Name</span>
                            <span class="text-base">{organization.contact_name}</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Mail class="mt-1 h-5 w-5 text-muted-foreground" />
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-muted-foreground">Email</span>
                            <a
                                href="mailto:{organization.contact_email}"
                                class="text-base text-primary hover:underline"
                            >
                                {organization.contact_email}
                            </a>
                        </div>
                    </div>

                    {#if organization.contact_phone}
                        <div class="flex items-start gap-3">
                            <Phone class="mt-1 h-5 w-5 text-muted-foreground" />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-muted-foreground">Phone</span>
                                <a
                                    href="tel:{organization.contact_phone}"
                                    class="text-base text-primary hover:underline"
                                >
                                    {organization.contact_phone}
                                </a>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        {#if organization.users && organization.users.length > 0}
            <div class="rounded-lg border bg-card p-6">
                <h2 class="mb-4 text-lg font-semibold">Members ({organization.users.length})</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-2 text-left text-sm font-medium">Name</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each organization.users as user (user.id)}
                                <tr class="border-b">
                                    <td class="px-4 py-2">{user.name}</td>
                                    <td class="px-4 py-2 text-muted-foreground">{user.email}</td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>
        {:else}
            <div class="rounded-lg border bg-card p-6">
                <h2 class="mb-4 text-lg font-semibold">Members</h2>
                <p class="text-muted-foreground">No members assigned to this organization yet.</p>
            </div>
        {/if}
    </div>
</AuthenticatedLayout>

