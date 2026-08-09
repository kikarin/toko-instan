import { computed } from 'vue';
import type { ComputedRef } from 'vue';

export function useMarketplaceCatalog(products: ComputedRef<any[] | undefined>) {
    const displayProducts = computed(() => products.value ?? []);
    const totalProducts = computed(() => displayProducts.value.length);

    return {
        displayProducts,
        totalProducts,
    };
}