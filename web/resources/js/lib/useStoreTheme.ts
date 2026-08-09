import { usePage } from '@inertiajs/vue3';
import { computed, watchEffect } from 'vue';
import type { ThemeColors, ThemePayload } from '@/types/store';

/**
 * Injects the active store theme colors as CSS custom properties on `:root`.
 *
 * Tokens set:
 *   --brand                    (primary)
 *   --brand-foreground         (auto-contrast: white or #1c1c22)
 *   --brand-secondary          (secondary)
 *   --brand-secondary-foreground
 *   --brand-accent             (accent)
 *   --brand-accent-foreground
 *   --brand-strong             (CTA / strong)
 *   --brand-cta-foreground     (auto-contrast text over CTA)
 *   --brand-soft               (primary @ 12% opacity – chip/card tint)
 *   --brand-surface            (primary @ 6% opacity – subtle bg)
 *   sidebar tokens
 */
export function useStoreTheme(themeOverride?: ThemePayload | null) {
    const page = usePage();

    const current = computed<ThemeColors>(() => {
        const themeData =
            themeOverride ?? (page.props.theme as ThemePayload | undefined);

        return resolveTheme(themeData);
    });

    watchEffect(() => {
        applyTheme(current.value);
    });

    return { theme: current };
}

// ---------------------------------------------------------------------------
// Defaults (fallback when no theme from DB)
// ---------------------------------------------------------------------------
const FALLBACK: ThemeColors = {
    primary: '#e07c28',
    secondary: '#6d4fc2',
    accent: '#0e9f8a',
    strong: '#e0405a',
};

function resolveTheme(theme?: ThemePayload | null): ThemeColors {
    if (!theme?.colors) return FALLBACK;

    return {
        primary:   theme.colors.primary   || FALLBACK.primary,
        secondary: theme.colors.secondary || FALLBACK.secondary,
        accent:    theme.colors.accent    || FALLBACK.accent,
        strong:    theme.colors.strong    || FALLBACK.strong,
    };
}

// ---------------------------------------------------------------------------
// Apply to :root
// ---------------------------------------------------------------------------
function applyTheme(colors: ThemeColors): void {
    if (typeof document === 'undefined') return;

    const root = document.documentElement;

    // Primary brand
    root.style.setProperty('--brand', colors.primary);
    root.style.setProperty('--brand-foreground', contrastColor(colors.primary));

    // Secondary
    root.style.setProperty('--brand-secondary', colors.secondary);
    root.style.setProperty('--brand-secondary-foreground', contrastColor(colors.secondary));

    // Accent
    root.style.setProperty('--brand-accent', colors.accent);
    root.style.setProperty('--brand-accent-foreground', contrastColor(colors.accent));

    // Strong / CTA
    root.style.setProperty('--brand-strong', colors.strong);
    root.style.setProperty('--brand-cta-foreground', contrastColor(colors.strong));

    // Derived soft / surface tokens
    root.style.setProperty('--brand-soft',    hexToRgba(colors.primary, 0.12));
    root.style.setProperty('--brand-surface', hexToRgba(colors.primary, 0.06));

    // Sidebar accent mirrors brand
    root.style.setProperty('--sidebar-primary', colors.primary);
    root.style.setProperty('--sidebar-accent', hexToRgba(colors.primary, 0.15));
    root.style.setProperty('--sidebar-accent-foreground', colors.primary);
    root.style.setProperty('--sidebar-ring', colors.primary);
    root.style.setProperty('--sidebar-bg', darken(colors.primary, 0.72));
}

// ---------------------------------------------------------------------------
// Color utilities
// ---------------------------------------------------------------------------

/** Returns white or dark foreground based on perceived luminance of `hex`. */
function contrastColor(hex: string): string {
    const rgb = hexToRgbTuple(hex);
    if (!rgb) return '#ffffff';

    // sRGB luminance (WCAG formula)
    const [r, g, b] = rgb.map((c) => {
        const n = c / 255;
        return n <= 0.03928 ? n / 12.92 : Math.pow((n + 0.055) / 1.055, 2.4);
    });

    const L = 0.2126 * r + 0.7152 * g + 0.0722 * b;

    // Use dark text on light backgrounds, white on dark
    return L > 0.35 ? '#1c1c22' : '#ffffff';
}

function hexToRgba(hex: string, alpha: number): string {
    const rgb = hexToRgbTuple(hex);
    if (!rgb) return `rgba(224,124,40,${alpha})`;
    return `rgba(${rgb[0]},${rgb[1]},${rgb[2]},${alpha})`;
}

function hexToRgbTuple(hex: string): [number, number, number] | null {
    const clean = hex.replace('#', '');
    if (clean.length !== 6) return null;
    const n = parseInt(clean, 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
}

function darken(hex: string, factor: number): string {
    const rgb = hexToRgbTuple(hex);
    if (!rgb) return '#18181c';
    const [r, g, b] = rgb.map((c) => Math.round(c * factor));
    return `#${((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1)}`;
}
