import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import type { ComputedRef } from 'vue';
import {
    CATEGORY_ICON_META,
    FALLBACK_CATEGORY_META,
} from '@/lib/marketplaceData';

export function useMarketplaceFilters(
    displayProducts: ComputedRef<any[]>,
    props: {
        categories?: string[];
        filters?: { search: string; category: string };
    },
) {
    const searchQ = ref(props.filters?.search || '');
    const selectedCat = ref(props.filters?.category || 'Semua');

    watch(
        () => props.filters?.search,
        (newVal) => {
            if (newVal !== undefined) {
                searchQ.value = newVal;
            }
        },
    );

    const displayCategories = computed(() => props.categories ?? []);

    const categoryMenu = computed(() =>
        displayCategories.value.map((cat) => {
            const meta = CATEGORY_ICON_META[cat] ?? FALLBACK_CATEGORY_META;

            return {
                label: cat,
                cat,
                icon: meta.icon,
                color: meta.color,
            };
        }),
    );

    const filteredProducts = computed(() => {
        const q = searchQ.value.trim().toLowerCase();
        const words = q.split(/\s+/).filter(Boolean);

        return displayProducts.value.filter((p) => {
            const matchesCategory =
                selectedCat.value === 'Semua' || p.cat === selectedCat.value;

            if (!matchesCategory) {
                return false;
            }

            if (!q) {
                return true;
            }

            const name = (p.name || '').toLowerCase();
            const cat = (p.cat || '').toLowerCase();
            const tag = (p.tag || '').toLowerCase();
            const store = (p.store || '').toLowerCase();

            const fullText = `${name} ${cat} ${tag} ${store}`;

            return (
                fullText.includes(q) || words.some((w) => fullText.includes(w))
            );
        });
    });

    function applySearch(q?: string) {
        if (q !== undefined) {
            searchQ.value = q;
        }

        router.get(
            window.location.pathname,
            { search: searchQ.value, category: selectedCat.value },
            { preserveState: true },
        );
    }

    function setCategory(cat: string) {
        selectedCat.value = cat;
        router.get(
            window.location.pathname,
            { search: searchQ.value, category: cat },
            { preserveState: true },
        );
    }

    return {
        searchQ,
        selectedCat,
        displayCategories,
        filteredProducts,
        categoryMenu,
        applySearch,
        setCategory,
    };
}