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
        email: '',
        password: '',
        password_confirmation: '',
        role: 'teacher' as 'teacher' | 'student',
    });

    function submitCreateUser(e: SubmitEvent) {
        e.preventDefault();
        $form.post('/admin/users', {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User created successfully');
                close();
            },
            onError: () => {
                toast.error('Failed to create user');
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
        <Button data-testid="admin-users-create-user-open">
            <Plus class="mr-2 h-4 w-4" />
            New User
        </Button>
    </Dialog.Trigger>
    <Dialog.Content
        class="sm:max-w-[500px]"
        onCloseAutoFocus={close}
        data-testid="admin-users-create-user-dialog"
    >
        <Dialog.Header>
            <Dialog.Title>Create New User</Dialog.Title>
        </Dialog.Header>
        <form
            onsubmit={submitCreateUser}
            class="flex flex-col gap-4"
            data-testid="admin-users-create-user-form"
        >
            <div class="flex flex-col gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    type="text"
                    bind:value={$form.name}
                    required
                    placeholder="John Doe"
                    data-testid="admin-users-create-user-name"
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
                    data-testid="admin-users-create-user-email"
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
                    data-testid="admin-users-create-user-role"
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
                    data-testid="admin-users-create-user-password"
                />
                <ErrorFeedback message={$form.errors.password} />
            </div>

            <div class="flex flex-col gap-2">
                <Label for="password_confirmation">Confirm Password</Label>
                <Input
                    id="password_confirmation"
                    type="password"
                    bind:value={$form.password_confirmation}
                    required
                    placeholder="••••••••"
                    data-testid="admin-users-create-user-password-confirmation"
                />
                <ErrorFeedback message={$form.errors.password_confirmation} />
            </div>

            <Dialog.Footer>
                <Button
                    type="button"
                    variant="outline"
                    onclick={close}
                    disabled={$form.processing}
                    data-testid="admin-users-create-user-cancel"
                >
                    Cancel
                </Button>
                <Button
                    type="submit"
                    disabled={$form.processing}
                    data-testid="admin-users-create-user-submit"
                >
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
