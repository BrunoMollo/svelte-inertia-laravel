<script lang="ts">
    import ErrorFeedback from '$lib/components/ui/custom/error-feedback.svelte';
    import { Button } from '$lib/components/ui/button';
    import * as AlertDialog from '$lib/components/ui/alert-dialog';
    import * as Dialog from '$lib/components/ui/dialog';
    import { Input } from '$lib/components/ui/input';
    import { Label } from '$lib/components/ui/label';
    import { useForm } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';

    type User = {
        id: number;
        name: string;
    };

    interface Props {
        open?: boolean;
        selectedUser: User | null;
        selectedUserId: number | null;
        onClose: () => void;
    }

    let {
        open = $bindable(false),
        selectedUser,
        selectedUserId,
        onClose,
    }: Props = $props();

    let confirmOpen = $state(false);

    const form = useForm({
        password: '',
        password_confirmation: '',
    });

    let lastSelectedUserId = $state<number | null>(null);
    $effect(() => {
        if (!open) return;
        if (selectedUserId === lastSelectedUserId) return;

        confirmOpen = false;
        $form.reset();
        $form.clearErrors();
        lastSelectedUserId = selectedUserId;
    });

    function close() {
        confirmOpen = false;
        $form.reset();
        $form.clearErrors();
        onClose();
    }

    function requestConfirmChangePassword(e: SubmitEvent) {
        e.preventDefault();
        confirmOpen = true;
    }

    function confirmChangePassword() {
        if (!selectedUserId) return;

        $form.post(`/admin/users/${selectedUserId}/change-password`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Password updated successfully');
                close();
            },
            onError: () => {
                confirmOpen = false;
                toast.error('Failed to update password');
            },
        });
    }
</script>

<Dialog.Root bind:open>
    <Dialog.Content
        onCloseAutoFocus={close}
        class="sm:max-w-[500px]"
        data-testid="admin-change-user-password-dialog"
    >
        <Dialog.Header>
            <Dialog.Title>Change user password</Dialog.Title>
        </Dialog.Header>

        <form
            onsubmit={requestConfirmChangePassword}
            class="flex flex-col gap-4"
            data-testid="admin-change-user-password-form"
        >
            <p class="text-sm text-muted-foreground">
                {#if selectedUser}
                    This will update the password for <span class="font-medium"
                        >{selectedUser.name}</span
                    >
                    and send them an email notification.
                {:else if selectedUserId}
                    This will update the password for user ID <span
                        class="font-medium">{selectedUserId}</span
                    >
                    and send them an email notification.
                {/if}
            </p>

            <div class="flex flex-col gap-2">
                <Label for="change_password">New Password</Label>
                <Input
                    id="change_password"
                    type="password"
                    bind:value={$form.password}
                    required
                    placeholder="••••••••"
                    data-testid="admin-change-user-password-input"
                />
                <ErrorFeedback message={$form.errors.password} />
            </div>

            <div class="flex flex-col gap-2">
                <Label for="change_password_confirmation"
                    >Confirm Password</Label
                >
                <Input
                    id="change_password_confirmation"
                    type="password"
                    bind:value={$form.password_confirmation}
                    required
                    placeholder="••••••••"
                    data-testid="admin-change-user-password-confirmation-input"
                />
                <ErrorFeedback message={$form.errors.password_confirmation} />
            </div>

            <Dialog.Footer>
                <Button
                    type="button"
                    variant="outline"
                    onclick={close}
                    disabled={$form.processing}
                    data-testid="admin-change-user-password-cancel"
                >
                    Cancel
                </Button>
                <Button
                    type="submit"
                    disabled={$form.processing}
                    data-testid="admin-change-user-password-submit"
                >
                    Continue
                </Button>
            </Dialog.Footer>
        </form>

        <AlertDialog.Root bind:open={confirmOpen}>
            <AlertDialog.Content
                data-testid="admin-change-user-password-confirm-dialog"
            >
                <AlertDialog.Header>
                    <AlertDialog.Title
                        >Confirm password change</AlertDialog.Title
                    >
                    <AlertDialog.Description>
                        {#if selectedUser}
                            Are you sure you want to change the password for {selectedUser.name}?
                            They will receive an email notification.
                        {:else if selectedUserId}
                            Are you sure you want to change the password for
                            user ID {selectedUserId}? They will receive an email
                            notification.
                        {/if}
                    </AlertDialog.Description>
                </AlertDialog.Header>
                <AlertDialog.Footer>
                    <AlertDialog.Cancel
                        data-testid="admin-change-user-password-confirm-cancel"
                    >
                        Cancel
                    </AlertDialog.Cancel>
                    <AlertDialog.Action
                        onclick={confirmChangePassword}
                        disabled={$form.processing}
                        data-testid="admin-change-user-password-confirm-action"
                    >
                        Confirm
                    </AlertDialog.Action>
                </AlertDialog.Footer>
            </AlertDialog.Content>
        </AlertDialog.Root>
    </Dialog.Content>
</Dialog.Root>
