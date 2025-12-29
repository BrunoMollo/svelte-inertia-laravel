<script lang="ts">
    import { LayoutDashboard, LifeBuoy, Send, Shell } from '@lucide/svelte';
    import type { Icon } from '@lucide/svelte';
    import NavMain from '$lib/components/ui/custom/nav-main.svelte';
    import NavSecondary from '$lib/components/ui/custom/nav-secondary.svelte';
    import NavUser from '$lib/components/ui/custom/nav-user.svelte';
    import * as Sidebar from '$lib/components/ui/sidebar';
    import ProjectSwitcher from './project-switcher.svelte';

    type Project = {
        logo: typeof Icon;
        title: string;
        subtitle: string;
    };

    const projects: Project[] = [
        {
            logo: Shell,
            title: 'Starter',
            subtitle: 'Svelte - Inertia - Laravel',
        },
    ];

    type MainNavigationItem = {
        title: string;
        url: string;
        icon: typeof Icon;
        items?: {
            title: string;
            url: string;
        }[];
    };

    const navMain: MainNavigationItem[] = [
        {
            title: 'Dashboard',
            url: '/dashboard',
            icon: LayoutDashboard,
        },
    ];

    type SecondaryNavigationItem = {
        title: string;
        url: string;
        icon: typeof Icon;
    };

    const navSecondary: SecondaryNavigationItem[] = [
        {
            title: 'Support',
            url: '/dashboard',
            icon: LifeBuoy,
        },
        {
            title: 'Feedback',
            url: '/dashboard',
            icon: Send,
        },
    ];
</script>

<Sidebar.Root variant="inset" collapsible="icon">
    <Sidebar.Header>
        <ProjectSwitcher {projects} />
    </Sidebar.Header>
    <Sidebar.Content>
        <NavMain items={navMain} />
        <NavSecondary items={navSecondary} class="mt-auto" />
    </Sidebar.Content>
    <Sidebar.Footer>
        <NavUser />
    </Sidebar.Footer>
</Sidebar.Root>
