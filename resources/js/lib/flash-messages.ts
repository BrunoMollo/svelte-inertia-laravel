import { router } from '@inertiajs/svelte';
import { toast } from 'svelte-sonner';

export function initFlashMessages() {
    router.on('success', (event) => {
        const flash = event.detail.page.props.flash as {
            success?: string;
            error?: string;
            info?: string;
            warning?: string;
        };

        if (flash?.success) {
            toast.success(flash.success);
        }

        if (flash?.error) {
            toast.error(flash.error);
        }

        if (flash?.info) {
            toast.info(flash.info);
        }

        if (flash?.warning) {
            toast.warning(flash.warning);
        }
    });
}
