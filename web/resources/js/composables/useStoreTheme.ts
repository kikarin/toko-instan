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
        applyThemeMeta(themeOverride ?? (page.props.theme as ThemePayload | undefined));
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
    if (!theme?.colors) {
return FALLBACK;
}

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
    if (typeof document === 'undefined') {
return;
}

    const root = document.documentElement;
    const isDarkPrimary = contrastColor(colors.primary) === '#ffffff';

    // 1. Core Semantic Tokens
    root.style.setProperty('--primary', colors.primary);
    root.style.setProperty('--primary-foreground', contrastColor(colors.primary));

    root.style.setProperty('--secondary', colors.secondary);
    root.style.setProperty('--secondary-foreground', contrastColor(colors.secondary));

    root.style.setProperty('--accent', colors.accent);
    root.style.setProperty('--accent-foreground', contrastColor(colors.accent));

    root.style.setProperty('--destructive', colors.strong);
    root.style.setProperty('--destructive-foreground', contrastColor(colors.strong));
    
    // Ring uses primary
    root.style.setProperty('--ring', colors.primary);

    // 2. Global Layout & Surface (Smartly derived based on Primary)
    // If we want the UI to feel very custom, we can tint the background with primary
    // Or we stick to neutral light mode, but the user wants the theme to affect everything.
    const bg = '#f5f4f0'; // Default light warm background
    const bgFg = contrastColor(bg);
    root.style.setProperty('--background', bg);
    root.style.setProperty('--foreground', bgFg);
    
    root.style.setProperty('--card', '#ffffff');
    root.style.setProperty('--card-foreground', contrastColor('#ffffff'));
    
    root.style.setProperty('--popover', '#ffffff');
    root.style.setProperty('--popover-foreground', contrastColor('#ffffff'));

    // 3. Status and Soft variants
    // muted / soft background using secondary or a very light gray
    root.style.setProperty('--muted', hexToRgba(colors.secondary, 0.1));
    root.style.setProperty('--muted-foreground', darken(colors.secondary, 0.4));
    
    // borders
    root.style.setProperty('--border', 'rgba(0, 0, 0, 0.1)');
    root.style.setProperty('--input', 'rgba(0, 0, 0, 0.15)');

    // 4. Specific Layouts (Sidebar, Header, Footer)
    // Let's make sidebar match the primary color but darkened for contrast
    const sidebarBg = darken(colors.primary, 0.4);
    root.style.setProperty('--sidebar', sidebarBg);
    root.style.setProperty('--sidebar-foreground', contrastColor(sidebarBg));
    root.style.setProperty('--sidebar-primary', colors.accent); // Accent on dark sidebar looks good
    root.style.setProperty('--sidebar-primary-foreground', contrastColor(colors.accent));
    root.style.setProperty('--sidebar-accent', hexToRgba(colors.accent, 0.2));
    root.style.setProperty('--sidebar-accent-foreground', colors.accent);
    root.style.setProperty('--sidebar-border', 'rgba(255, 255, 255, 0.1)');
    root.style.setProperty('--sidebar-ring', colors.accent);
    
    // Header & Footer
    root.style.setProperty('--header', colors.primary);
    root.style.setProperty('--header-foreground', contrastColor(colors.primary));
    root.style.setProperty('--footer', sidebarBg);
    root.style.setProperty('--footer-foreground', contrastColor(sidebarBg));

    // Backward compatibility for components not yet refactored
    root.style.setProperty('--brand', colors.primary);
    root.style.setProperty('--brand-foreground', contrastColor(colors.primary));
    root.style.setProperty('--brand-secondary', colors.secondary);
    root.style.setProperty('--brand-secondary-foreground', contrastColor(colors.secondary));
    root.style.setProperty('--brand-accent', colors.accent);
    root.style.setProperty('--brand-accent-foreground', contrastColor(colors.accent));
    root.style.setProperty('--brand-strong', colors.strong);
    root.style.setProperty('--brand-cta-foreground', contrastColor(colors.strong));
    root.style.setProperty('--brand-soft', hexToRgba(colors.primary, 0.12));
    root.style.setProperty('--brand-surface', hexToRgba(colors.primary, 0.06));
}

function applyThemeMeta(theme?: ThemePayload | null): void {
    if (typeof document === 'undefined') {
        return;
    }

    const root = document.documentElement;
    const variant =
        theme?.variant ||
        (theme?.key === 'fashion' || theme?.key === 'food' ? theme.key : 'modern');
    root.dataset.themeVariant = variant;

    const font = theme?.font || 'Outfit';
    root.style.setProperty('--font-sans', `'${font}', 'Outfit', ui-sans-serif, system-ui, sans-serif`);
}

// ---------------------------------------------------------------------------
// Color utilities
// ---------------------------------------------------------------------------

/** Returns white or dark foreground based on perceived luminance of `hex`. */
function contrastColor(hex: string): string {
    const rgb = hexToRgbTuple(hex);

    if (!rgb) {
return '#ffffff';
}

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

    if (!rgb) {
<<<<<<< HEAD
return `rgba(224,124,40,${alpha})`;
=======
return `rgba(0,0,0,${alpha})`;
>>>>>>> origin/main
}

    return `rgba(${rgb[0]},${rgb[1]},${rgb[2]},${alpha})`;
}

function hexToRgbTuple(hex: string): [number, number, number] | null {
    // If already rgba/rgb, try to parse
    if (hex.startsWith('rgb')) {
        const match = hex.match(/\d+/g);

        if (match && match.length >= 3) {
            return [parseInt(match[0]), parseInt(match[1]), parseInt(match[2])];
        }
    }

    const clean = hex.replace('#', '');

<<<<<<< HEAD
    if (clean.length !== 6) {
return null;
}

    const n = parseInt(clean, 16);
=======
    if (clean.length === 3) {
        const r = parseInt(clean[0] + clean[0], 16);
        const g = parseInt(clean[1] + clean[1], 16);
        const b = parseInt(clean[2] + clean[2], 16);

        return [r, g, b];
    }

    if (clean.length !== 6 && clean.length !== 8) {
return null;
}

    const n = parseInt(clean.substring(0,6), 16);
>>>>>>> origin/main

    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
}

function darken(hex: string, factor: number): string {
    const rgb = hexToRgbTuple(hex);

    if (!rgb) {
return '#18181c';
}

<<<<<<< HEAD
    const [r, g, b] = rgb.map((c) => Math.round(c * factor));
=======
    const [r, g, b] = rgb.map((c) => Math.max(0, Math.round(c * factor)));
>>>>>>> origin/main

    return `#${((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1)}`;
}
