<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { Command } from '@lucide/svelte';
    import { _ } from '$lib/i18n';
    import { Toaster } from '$lib/components/ui/sonner';
    import { ModeWatcher } from 'mode-watcher';
    import type { Snippet } from 'svelte';

    type Props = {
        children?: Snippet;
    };

    let { children }: Props = $props();
</script>

<ModeWatcher />
<div class="grid min-h-svh">
    <div class="flex flex-col gap-4 bg-background p-6 md:p-10">
        <div class="flex justify-center gap-2 md:justify-start">
            <Link href="/" class="flex items-center gap-2 font-medium">
                <div
                    class="flex h-6 w-6 items-center justify-center rounded-md bg-primary text-primary-foreground"
                >
                    <Command class="size-4" />
                </div>
            </Link>
        </div>
        <div class="flex flex-1 items-center justify-center">
            <div class="w-full max-w-xs">
                {@render children?.()}
            </div>
        </div>
    </div>
</div>
<Toaster position="top-right" />
