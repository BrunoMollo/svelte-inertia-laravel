<script lang="ts">
    import type { Snippet } from 'svelte';

    type Props = {
        placeholder?: string;
        children?: Snippet;
        class?: string;
    };

    let { placeholder = 'Select an option', children, class: className }: Props = $props();
</script>

<span data-slot="select-value" class={className}>
    {#if children}
        {@render children()}
    {:else}
        <span data-placeholder>{placeholder}</span>
    {/if}
</span>
