<script lang="ts">
    import {
        Command,
        CommandDialog,
        CommandEmpty,
        CommandGroup,
        CommandInput,
        CommandItem,
        CommandList,
        CommandSeparator,
    } from '$lib/components/ui/command';
    import { router } from '@inertiajs/svelte';
    import {
        BadgeCheck,
        LayoutDashboard,
        Lock,
        Moon,
        Sun,
    } from '@lucide/svelte';
    import { _ } from 'svelte-i18n';
    import { onMount } from 'svelte';
    import { setMode } from 'mode-watcher';

    interface NavigationItem {
        title: string;
        href: string;
        icon: typeof Moon;
    }

    const navigationItems: NavigationItem[] = [
        {
            title: $_('app_command.dashboard'),
            href: '/dashboard',
            icon: LayoutDashboard,
        },
        {
            title: $_('app_command.profile'),
            href: '/account/profile',
            icon: BadgeCheck,
        },
        {
            title: $_('app_command.security'),
            href: '/account/security',
            icon: Lock,
        },
    ];

    let isOpen = $state(false);

    onMount(() => {
        const down = (e: KeyboardEvent) => {
            if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                isOpen = !isOpen;
            }
        };

        document.addEventListener('keydown', down);
        return () => document.removeEventListener('keydown', down);
    });

    function goToRoute(href: string) {
        isOpen = false;
        router.visit(href);
    }

    function setTheme(newMode: 'dark' | 'light') {
        isOpen = false;
        setMode(newMode);
    }
</script>

<CommandDialog bind:open={isOpen}>
    <Command>
        <CommandInput placeholder={$_('app_command.search_placeholder')} />
        <CommandList>
            <CommandEmpty>{$_('app_command.no_results')}</CommandEmpty>
            <CommandGroup heading={$_('app_command.go_to_heading')}>
                {#each navigationItems as item}
                    <CommandItem onSelect={() => goToRoute(item.href)}>
                        <item.icon />
                        <span>{item.title}</span>
                    </CommandItem>
                {/each}
            </CommandGroup>
            <CommandSeparator />
            <CommandGroup heading={$_('app_command.theme_heading')}>
                <CommandItem onSelect={() => setTheme('dark')}>
                    <Moon />
                    <span>{$_('app_command.dark_mode')}</span>
                </CommandItem>
                <CommandItem onSelect={() => setTheme('light')}>
                    <Sun />
                    <span>{$_('app_command.light_mode')}</span>
                </CommandItem>
            </CommandGroup>
        </CommandList>
    </Command>
</CommandDialog>
