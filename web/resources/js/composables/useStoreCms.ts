import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type {
    StoreShowcase,
    Testimonial,
    ThemeColors,
    ThemeInfo,
} from '@/types/store';

export const DEFAULT_COLORS: ThemeColors = {
    primary: '#3F9AAE',
    secondary: '#79C9C5',
    accent: '#FFE2AF',
    strong: '#F96E5B',
};

export function hexToRgba(hex: string, alpha: number): string {
    const clean = hex.replace('#', '');

    if (clean.length !== 6) {
        return 'rgba(224, 124, 40, 0.15)';
    }

    const n = parseInt(clean, 16);

    return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${alpha})`;
}

export const COLOR_TOKENS: { token: keyof ThemeColors; label: string }[] = [
    { token: 'primary', label: 'Utama' },
    { token: 'secondary', label: 'Sekunder' },
    { token: 'accent', label: 'Aksen' },
    { token: 'strong', label: 'CTA' },
];

export function useStoreCms(props: {
    theme?: ThemeInfo | null;
    showcase?: StoreShowcase | null;
    store?: any;
    themes?: Record<string, ThemeInfo>;
}) {
    const selectedTheme = ref(props.theme?.key || 'teal');
    const hero = ref({
        title: props.showcase?.hero?.title ?? '',
        subtitle: props.showcase?.hero?.subtitle ?? '',
        cta_label: props.showcase?.hero?.cta_label ?? '',
        image: props.showcase?.hero?.image ?? '',
    });
    const about = ref({ ...(props.showcase?.about ?? {}) });
    const contactShow = ref(props.showcase?.contact?.show ?? true);
    const featuredIds = ref<number[]>(
        props.store?.showcase?.featured_product_ids ?? [],
    );
    const testimonials = ref<Testimonial[]>(
        (props.showcase?.testimonials ?? []).map((t) => ({
            ...t,
            rating: t.rating || 5,
        })),
    );

    const previewColors = ref<ThemeColors>(
        props.theme?.colors ?? { ...DEFAULT_COLORS },
    );

    const isCustom = ref(props.theme?.key === 'custom');

    const previewStyle = ref({
        '--brand': previewColors.value.primary,
        '--brand-secondary': previewColors.value.secondary,
        '--brand-accent': previewColors.value.accent,
        '--brand-strong': previewColors.value.strong,
    });

    function selectTheme(key: string) {
        selectedTheme.value = key;
        isCustom.value = key === 'custom';
        const cfg = props.themes?.[key];

        if (cfg) {
            previewColors.value = { ...cfg.colors };
            refreshPreviewStyle();
        }
    }

    function onColorInput(token: keyof ThemeColors, e: Event) {
        const target = e.target as HTMLInputElement;

        if (!isCustom.value) {
            isCustom.value = true;
            selectedTheme.value = 'custom';
        }

        previewColors.value = { ...previewColors.value, [token]: target.value };
        refreshPreviewStyle();
    }

    function refreshPreviewStyle() {
        previewStyle.value = {
            '--brand': previewColors.value.primary,
            '--brand-secondary': previewColors.value.secondary,
            '--brand-accent': previewColors.value.accent,
            '--brand-strong': previewColors.value.strong,
        };

        if (typeof document !== 'undefined') {
            const root = document.documentElement;
            root.style.setProperty('--brand', previewColors.value.primary);
            root.style.setProperty(
                '--brand-secondary',
                previewColors.value.secondary,
            );
            root.style.setProperty(
                '--brand-accent',
                previewColors.value.accent,
            );
            root.style.setProperty(
                '--brand-strong',
                previewColors.value.strong,
            );
            root.style.setProperty(
                '--brand-soft',
                hexToRgba(previewColors.value.primary, 0.15),
            );
            root.style.setProperty(
                '--sidebar-primary',
                previewColors.value.primary,
            );
            root.style.setProperty(
                '--sidebar-accent',
                hexToRgba(previewColors.value.primary, 0.15),
            );
            root.style.setProperty(
                '--sidebar-accent-foreground',
                previewColors.value.primary,
            );
            root.style.setProperty('--sidebar-ring', previewColors.value.primary);
        }
    }

    function toggleFeatured(id: number) {
        featuredIds.value = featuredIds.value.includes(id)
            ? featuredIds.value.filter((x) => x !== id)
            : [...featuredIds.value, id];
    }

    function addTestimonial() {
        testimonials.value.push({ name: '', role: '', text: '', rating: 5 });
    }

    function removeTestimonial(i: number) {
        testimonials.value.splice(i, 1);
    }

    function submit() {
        router.put(
            '/store-cms',
            {
                theme: selectedTheme.value,
                theme_colors: { ...previewColors.value },
                showcase: {
                    hero: hero.value,
                    about: about.value,
                    contact: { show: contactShow.value },
                    featured_product_ids: featuredIds.value,
                    testimonials: testimonials.value.filter((t) =>
                        t.text?.trim(),
                    ),
                },
            },
            {
                preserveScroll: true,
                onSuccess: () => toast.success('Tampilan & konten disimpan!'),
                onError: () =>
                    toast.error('Gagal menyimpan. Periksa kembali isian Anda.'),
            },
        );
    }

    return {
        selectedTheme,
        hero,
        about,
        contactShow,
        featuredIds,
        testimonials,
        previewColors,
        isCustom,
        previewStyle,
        selectTheme,
        onColorInput,
        refreshPreviewStyle,
        toggleFeatured,
        addTestimonial,
        removeTestimonial,
        submit,
    };
}