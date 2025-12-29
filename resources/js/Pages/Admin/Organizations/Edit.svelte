<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { ArrowLeft } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';

    type Organization = {
        id: number;
        name: string;
        contact_name: string;
        contact_email: string;
        contact_phone: string | null;
        activated_at: string | null;
    };

    type Props = {
        organization: Organization;
    };

    const { organization }: Props = $props();

    const form = useForm({
        _method: 'PUT',
        name: organization.name,
        contact_name: organization.contact_name,
        contact_email: organization.contact_email,
        contact_phone: organization.contact_phone || '',
    });

    function submit(e: SubmitEvent) {
        e.preventDefault();
        $form.put(`/admin/organizations/${organization.id}`, {
            onSuccess: () => {
                toast.success('Organization updated successfully');
            },
            onError: () => {
                toast.error('Failed to update organization');
            },
        });
    }
</script>

<svelte:head>
    <title>Edit Organization</title>
</svelte:head>

<AuthenticatedLayout>
    <div class="flex max-w-2xl flex-col gap-4">
        <div class="flex items-center gap-4">
            <Link href="/admin/organizations">
                <Button variant="ghost" size="sm">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back
                </Button>
            </Link>
            <div>
                <h1 class="text-2xl font-bold">Edit Organization</h1>
                <p class="text-muted-foreground">
                    Update organization information
                </p>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6">
            <form onsubmit={submit} class="flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <Label for="name">Organization Name</Label>
                    <Input
                        id="name"
                        type="text"
                        bind:value={$form.name}
                        required
                    />
                    <ErrorFeedback message={$form.errors.name} />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="contact_name">Contact Name</Label>
                    <Input
                        id="contact_name"
                        type="text"
                        bind:value={$form.contact_name}
                        required
                    />
                    <ErrorFeedback message={$form.errors.contact_name} />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="contact_email">Contact Email</Label>
                    <Input
                        id="contact_email"
                        type="email"
                        bind:value={$form.contact_email}
                        required
                    />
                    <ErrorFeedback message={$form.errors.contact_email} />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="contact_phone">Contact Phone (Optional)</Label>
                    <Input
                        id="contact_phone"
                        type="tel"
                        bind:value={$form.contact_phone}
                    />
                    <ErrorFeedback message={$form.errors.contact_phone} />
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link href="/admin/organizations">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" disabled={$form.processing}>
                        {#if $form.processing}
                            Updating...
                        {:else}
                            Update Organization
                        {/if}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</AuthenticatedLayout>

