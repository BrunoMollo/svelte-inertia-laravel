<script lang="ts">
    import AuthenticatedLayout from '$lib/layouts/AuthenticatedLayout.svelte';
    import { Button } from '$lib/components/ui/button';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { ArrowLeft } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';

    const form = useForm({
        name: '',
        contact_name: '',
        contact_email: '',
        contact_phone: '',
    });

    function submit(e: SubmitEvent) {
        e.preventDefault();
        $form.post('/admin/organizations', {
            onSuccess: () => {
                toast.success('Organization created successfully');
            },
            onError: () => {
                toast.error('Failed to create organization');
            },
        });
    }
</script>

<svelte:head>
    <title>Create Organization</title>
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
                <h1 class="text-2xl font-bold">Create Organization</h1>
                <p class="text-muted-foreground">
                    Add a new corporate organization to the platform
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
                        placeholder="Acme Corporation"
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
                        placeholder="John Doe"
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
                        placeholder="john.doe@example.com"
                    />
                    <ErrorFeedback message={$form.errors.contact_email} />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="contact_phone">Contact Phone (Optional)</Label>
                    <Input
                        id="contact_phone"
                        type="tel"
                        bind:value={$form.contact_phone}
                        placeholder="+1-555-0100"
                    />
                    <ErrorFeedback message={$form.errors.contact_phone} />
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link href="/admin/organizations">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" disabled={$form.processing}>
                        {#if $form.processing}
                            Creating...
                        {:else}
                            Create Organization
                        {/if}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</AuthenticatedLayout>

