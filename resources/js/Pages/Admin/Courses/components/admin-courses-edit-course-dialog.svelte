<script lang="ts">
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { Button } from '$lib/components/ui/button';
    import * as Dialog from '$lib/components/ui/dialog';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import { useForm } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';

    type Course = {
        id: number;
        name: string;
        description: string | null;
        available_from: string | null;
        available_until: string | null;
        is_draft: boolean;
        deleted_at: string | null;
    };

    interface Props {
        open?: boolean;
        selectedCourse: Course | null;
        selectedCourseId: number | null;
        onClose: () => void;
    }

    let {
        open = $bindable(false),
        selectedCourse,
        selectedCourseId,
        onClose,
    }: Props = $props();

    const form = useForm({
        name: '',
        description: '',
        available_from: '',
        available_until: '',
        is_draft: true,
    });

    let lastSelectedCourseId = $state<number | null>(null);
    $effect(() => {
        if (!open) return;
        if (!selectedCourseId) return;
        if (selectedCourseId === lastSelectedCourseId) return;

        const course = selectedCourse;
        $form.clearErrors();
        $form.reset();

        if (course) {
            $form.name = course.name ?? '';
            $form.description = course.description ?? '';
            $form.available_from = course.available_from ?? '';
            $form.available_until = course.available_until ?? '';
            $form.is_draft = course.is_draft ?? true;
        }

        lastSelectedCourseId = selectedCourseId;
    });

    function close() {
        $form.clearErrors();
        $form.reset();
        onClose();
    }

    function submitUpdateCourse(e: SubmitEvent) {
        e.preventDefault();
        if (!selectedCourseId) return;

        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const anyForm = $form as any;
        if (typeof anyForm.patch !== 'function') {
            toast.error('Update method not available');
            return;
        }

        anyForm.patch(`/admin/courses/${selectedCourseId}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Course updated successfully');
                close();
            },
            onError: () => {
                toast.error('Failed to update course');
            },
        });
    }
</script>

<Dialog.Root bind:open>
    <Dialog.Content
        onCloseAutoFocus={close}
        class="sm:max-w-[560px]"
        data-testid="admin-courses-edit-dialog"
    >
        <Dialog.Header>
            <Dialog.Title>Edit course</Dialog.Title>
        </Dialog.Header>

        <form
            onsubmit={submitUpdateCourse}
            class="flex flex-col gap-4"
            data-testid="admin-courses-edit-form"
        >
            {#if selectedCourse?.deleted_at}
                <p class="text-sm text-muted-foreground">
                    This course is deleted. Editing is disabled.
                </p>
            {/if}

            <div class="flex flex-col gap-2">
                <Label for="edit-course-name">Name</Label>
                <Input
                    id="edit-course-name"
                    type="text"
                    bind:value={$form.name}
                    required
                    disabled={$form.processing || !!selectedCourse?.deleted_at}
                    data-testid="admin-courses-edit-name"
                />
                <ErrorFeedback message={$form.errors.name} />
            </div>

            <div class="flex flex-col gap-2">
                <Label for="edit-course-description">Description</Label>
                <textarea
                    id="edit-course-description"
                    bind:value={$form.description}
                    rows={4}
                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    disabled={$form.processing || !!selectedCourse?.deleted_at}
                    data-testid="admin-courses-edit-description"
                ></textarea>
                <ErrorFeedback message={$form.errors.description} />
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <Label for="edit-course-available-from"
                        >Available from</Label
                    >
                    <Input
                        id="edit-course-available-from"
                        type="date"
                        bind:value={$form.available_from}
                        disabled={$form.processing ||
                            !!selectedCourse?.deleted_at}
                        data-testid="admin-courses-edit-available-from"
                    />
                    <ErrorFeedback message={$form.errors.available_from} />
                </div>

                <div class="flex flex-col gap-2">
                    <Label for="edit-course-available-until"
                        >Available until</Label
                    >
                    <Input
                        id="edit-course-available-until"
                        type="date"
                        bind:value={$form.available_until}
                        disabled={$form.processing ||
                            !!selectedCourse?.deleted_at}
                        data-testid="admin-courses-edit-available-until"
                    />
                    <ErrorFeedback message={$form.errors.available_until} />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <input
                        id="edit-course-is-draft"
                        type="checkbox"
                        bind:checked={$form.is_draft}
                        class="h-4 w-4"
                        disabled={$form.processing ||
                            !!selectedCourse?.deleted_at}
                        data-testid="admin-courses-edit-is-draft"
                    />
                    <Label for="edit-course-is-draft">Draft</Label>
                </div>
                <ErrorFeedback message={$form.errors.is_draft} />
            </div>

            <Dialog.Footer>
                <Button
                    type="button"
                    variant="outline"
                    onclick={close}
                    disabled={$form.processing}
                    data-testid="admin-courses-edit-cancel"
                >
                    Cancel
                </Button>
                <Button
                    type="submit"
                    disabled={$form.processing || !!selectedCourse?.deleted_at}
                    data-testid="admin-courses-edit-submit"
                >
                    {$form.processing ? 'Saving...' : 'Save changes'}
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
