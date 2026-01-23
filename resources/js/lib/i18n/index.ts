import { addMessages, init, _ as translate } from 'svelte-i18n';
import { derived } from 'svelte/store';
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

/**
 * Role translation keys mapping.
 * Maps role names (from database) to their Spanish translation keys.
 * Add new roles here as they are created.
 */
const roleTranslationKeys: Record<string, string> = {
    superadmin: 'Super Administrador',
    teacher: 'Profesor',
    student: 'Estudiante',
};

/**
 * Translates a role name using the i18n system.
 * Falls back to the original role name if no translation exists.
 */
export const translateRole = derived(translate, ($t) => {
    return (roleName: string): string => {
        const key = roleTranslationKeys[roleName];
        if (!key) return roleName;
        return $t(key) || roleName;
    };
});

export { _, locale as i18nLocale } from 'svelte-i18n';
