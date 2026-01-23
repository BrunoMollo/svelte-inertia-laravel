import { addMessages, init } from 'svelte-i18n';
import en from './locales/en.json';

addMessages('en', en);

/**
 * Initialize i18n with the given locale.
 * Must be called BEFORE rendering the app to prevent flickering on initial load.
 */
export function initI18n(initialLocale: string, fallbackLocale: string = 'es') {
    init({
        fallbackLocale,
        initialLocale,
        loadingDelay: 0, // Critical for SSR - prevents flickering
    });
}

export { _, locale as i18nLocale } from 'svelte-i18n';
