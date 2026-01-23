<script lang="ts">
    import { i18nLocale as locale } from '$lib/i18n';
    import * as Select from '$lib/components/ui/select';
    import { router } from '@inertiajs/svelte';

    const locales = [
        { value: 'es', label: 'Español' },
        { value: 'en', label: 'English' },
    ];

    let selectedLocale = $state<{ value: string; label: string } | undefined>();

    $effect(() => {
        selectedLocale = locales.find(l => l.value === $locale);
    });

    function handleChange() {
        if (!selectedLocale) return;

        router.post(route('user.locale.update'), { locale: selectedLocale.value }, {
            preserveState: true,
            onSuccess: () => {
                locale.set(selectedLocale!.value);
                window.location.reload();
            }
        });
    }
</script>

<Select.Root {value: selectedLocale?.value} onValueChange={(v) => {
    selectedLocale = locales.find(l => l.value === v);
    handleChange();
}}>
    <Select.Trigger class="w-[130px]">
        <Select.Value />
    </Select.Trigger>
    <Select.Content>
        {#each locales as loc}
            <Select.Item value={loc.value}>{loc.label}</Select.Item>
        {/each}
    </Select.Content>
</Select.Root>
