import '../css/app.css';
import './bootstrap';

import { initI18n } from '$lib/i18n';
import { createInertiaApp, type ResolvedComponent } from '@inertiajs/svelte';
import { mount } from 'svelte';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob<ResolvedComponent>(
            './Pages/**/*.svelte',
            { eager: true },
        );
        return pages[`./Pages/${name}.svelte`];
    },
    setup({ el, App, props }) {
        // Initialize i18n with locale from Inertia props BEFORE mounting
        const locale = (props.initialPage.props as any).locale;
        if (locale) {
            initI18n(locale);
        }

        mount(App, { target: el!, props });
    },
    progress: {
        color: '#ffffff',
    },
});
