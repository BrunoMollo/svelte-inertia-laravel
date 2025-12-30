<script lang="ts">
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { Button } from '$lib/components/ui/button';
    import * as Dialog from '$lib/components/ui/dialog';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import { useForm } from '@inertiajs/svelte';
    import { Plus } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';

    let open = $state(false);

    const form = useForm({
        name: '',
        description: '',
        available_from: '',
        available_until: '',
        is_draft: true,
    });

    function submitCreateCourse(e: SubmitEvent) {
        e.preventDefault();
        $form.post('/admin/courses', {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Course created successfully');
                close();
            },
            onError: () => {
                toast.error('Failed to create course');
            },
        });
    }

    function close() {
        open = false;
        $form.reset();
        $form.clearErrors();
    }
</script>

<Dialog.Root bind:open>
    <Dialog.Trigger>
        <Button data-testid="admin-courses-create-open">
            <Plus class="mr-2 h-4 w-4" />
            New Course
        </Button>
    </Dialog.Trigger>
    <Dialog.Content
        class="sm:max-w-[560px]"
        onCloseAutoFocus={close}
        data-testid="admin-courses-create-dialog"
    >
        <Dialog.Header>
            <Dialog.Title>Create New Course</Dialog.Title>
        </Dialog.Header>

        <form
            onsubmit={submitCreateCourse}
            class="flex flex-col gap-4"
            data-testid="admin-courses-create-form"
        >
            <div class="flex flex-col gap-2">
                <Label for="course-name">Name</Label>
                <Input
                    id="course-name"
                    type="text"
                    bind:value={$form.name}
                    required
                    placeholder="Intro to Algebra"
                    data-testid="admin-courses-create-name"
                />
                <ErrorFeedback message={$form.errors.name} />
            </div>

            <div class="flex flex-col gap-2">
                <Label for="course-description">Description</Label>
                <textarea
                    id="course-description"
                    bind:value={$form.description}
                    rows={4}
                    placeholder="What is this course about?"
                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    data-testid="admin-courses-create-description"
                ></textarea>
                <ErrorFeedback message={$form.errors.description} />
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <Label for="course-available-from">Available from</Label>
                    <Input
                        id="course-available-from"
                        type="date"
                        bind:value={$form.available_from}
                        data-testid="admin-courses-create-available-from"
                    />
                    <ErrorFeedback message={$form.errors.available_from} />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="course-available-until">Available until</Label>
                    <Input
                        id="course-available-until"
                        type="date"
                        bind:value={$form.available_until}
                        data-testid="admin-courses-create-available-until"
                    />
                    <ErrorFeedback message={$form.errors.available_until} />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <input
                        id="course-is-draft"
                        type="checkbox"
                        bind:checked={$form.is_draft}
                        class="h-4 w-4"
                        data-testid="admin-courses-create-is-draft"
                    />
                    <Label for="course-is-draft">Draft</Label>
                </div>
                <ErrorFeedback message={$form.errors.is_draft} />
            </div>

            <Dialog.Footer>
                <Button
                    type="button"
                    variant="outline"
                    onclick={close}
                    disabled={$form.processing}
                    data-testid="admin-courses-create-cancel"
                >
                    Cancel
                </Button>
                <Button
                    type="submit"
                    disabled={$form.processing}
                    data-testid="admin-courses-create-submit"
                >
                    {$form.processing ? 'Creating...' : 'Create Course'}
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
