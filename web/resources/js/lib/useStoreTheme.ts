import { usePage } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';

export interface ThemeColors {
    primary: string;
    secondary: string;
    accent: string;
    strong: string;
}

export interface ThemePayload {
    key?: string;
    colors?: Partial<ThemeColors>;
}

/**
 * Injects the active store theme colors as CSS custom properties on `:root`
 * so every page (storefront + seller sidebar + dashboard + cards) follows the store theme dynamically.
 */
export function useStoreTheme(themeOverride?: ThemePayload | null) {
    const page = usePage();

    const current = computed<ThemeColors>(() => {
        const themeData = themeOverride ?? (page.props.theme as ThemePayload | undefined);
        return computedTheme(themeData);
    });

    watchEffect(() => {
        applyThemeEffect(current.value);
    });

    return {
        theme: current,
    };
}

function computedTheme(theme?: ThemePayload | null): ThemeColors {
    const base: ThemeColors = {
        primary: '#e07c28',
        secondary: '#6d4fc2',
        accent: '#0e9f8a',
        strong: '#e0405a',
    };

    if (!theme?.colors) {
        return base;
    }

    return {
        primary: theme.colors.primary || base.primary,
        secondary: theme.colors.secondary || base.secondary,
        accent: theme.colors.accent || base.accent,
        strong: theme.colors.strong || base.strong,
    };
}

function applyThemeEffect(colors: ThemeColors): void {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;

    root.style.setProperty('--brand', colors.primary);
    root.style.setProperty('--brand-secondary', colors.secondary);
    root.style.setProperty('--brand-accent', colors.accent);
    root.style.setProperty('--brand-strong', colors.strong);
    root.style.setProperty('--brand-soft', hexToRgba(colors.primary, 0.15));

    // Dynamic sidebar accent + background (darkened brand for legibility)
    root.style.setProperty('--sidebar-primary', colors.primary);
    root.style.setProperty('--sidebar-accent', hexToRgba(colors.primary, 0.15));
    root.style.setProperty('--sidebar-accent-foreground', colors.primary);
    root.style.setProperty('--sidebar-ring', colors.primary);
    root.style.setProperty('--sidebar-bg', darken(colors.primary, 0.72));
}

function hexToRgba(hex: string, alpha: number): string {
    const clean = hex.replace('#', '');

    if (clean.length !== 6) {
        return 'rgba(224, 124, 40, 0.15)';
    }

    const n = parseInt(clean, 16);
    const r = (n >> 16) & 255;
    const g = (n >> 8) & 255;
    const b = n & 255;

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function darken(hex: string, factor: number): string {
    const clean = hex.replace('#', '');

    if (clean.length !== 6) {
        return '#18181c';
    }

    const n = parseInt(clean, 16);
    const r = Math.round(((n >> 16) & 255) * factor);
    const g = Math.round(((n >> 8) & 255) * factor);
    const b = Math.round((n & 255) * factor);

    return `#${((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1)}`;
}