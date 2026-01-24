<script lang="ts">
    import { Select as SelectPrimitive } from 'bits-ui';

    type Props = SelectPrimitive.ValueProps;

    let { ref = $bindable(null), placeholder, ...restProps }: Props = $props();
</script>

<SelectPrimitive.Value bind:ref data-slot="select-value" {placeholder} {...restProps} />
