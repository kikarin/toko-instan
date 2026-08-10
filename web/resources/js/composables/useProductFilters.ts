import { computed, ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { Product } from '@/types/product';

export type ProductStatusFilter = 'all' | 'active' | 'inactive' | 'low_stock';
export type ProductViewMode = 'grid' | 'table';

export function useProductFilters(products: Ref<Product[]>) {
    const searchQ = ref('');
    const selectedCategory = ref<string>('Semua');
    const statusFilter = ref<ProductStatusFilter>('all');
    const viewMode = ref<ProductViewMode>('grid');

    // Pagination state
    const currentPage = ref(1);
    const perPage = ref(6);

    watch([searchQ, selectedCategory, statusFilter, perPage], () => {
        currentPage.value = 1;
    });

    const filtered = computed(() => {
        let result = products.value;

        // Search query
        if (searchQ.value.trim()) {
            const q = searchQ.value.toLowerCase();
            result = result.filter(
                (p) =>
                    p.name.toLowerCase().includes(q) ||
                    p.category.toLowerCase().includes(q) ||
                    (p.sku && p.sku.toLowerCase().includes(q)),
            );
        }

        // Category filter
        if (selectedCategory.value !== 'Semua') {
            result = result.filter((p) => p.category === selectedCategory.value);
        }

        // Status filter
        if (statusFilter.value === 'active') {
            result = result.filter((p) => p.is_active);
        } else if (statusFilter.value === 'inactive') {
            result = result.filter((p) => !p.is_active);
        } else if (statusFilter.value === 'low_stock') {
            result = result.filter((p) => p.stock <= 5 && p.is_active);
        }

        return result;
    });

    const totalPages = computed(
        () => Math.ceil(filtered.value.length / perPage.value) || 1,
    );

    const paginatedProducts = computed(() => {
        const start = (currentPage.value - 1) * perPage.value;

        return filtered.value.slice(start, start + perPage.value);
    });

    const paginationStart = computed(() => {
        if (filtered.value.length === 0) {
            return 0;
        }

        return (currentPage.value - 1) * perPage.value + 1;
    });

    const paginationEnd = computed(() => {
        return Math.min(
            currentPage.value * perPage.value,
            filtered.value.length,
        );
    });

    function goToPage(page: number) {
        if (page >= 1 && page <= totalPages.value) {
            currentPage.value = page;
        }
    }

    return {
        searchQ,
        selectedCategory,
        statusFilter,
        viewMode,
        currentPage,
        perPage,
        filtered,
        totalPages,
        paginatedProducts,
        paginationStart,
        paginationEnd,
        goToPage,
    };
}