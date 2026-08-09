import { computed  } from 'vue';
import type {Ref} from 'vue';
import type { Product } from '@/types/product';

export function useProductStats(products: Ref<Product[]>) {
    const totalProductsCount = computed(() => products.value.length);
    const activeProductsCount = computed(
        () => products.value.filter((p) => p.is_active).length,
    );
    const lowStockCount = computed(
        () =>
            products.value.filter((p) => p.stock <= 5 && p.is_active).length,
    );
    const totalInventoryValuation = computed(() => {
        return products.value.reduce((acc, p) => acc + p.price * p.stock, 0);
    });

    function formatRupiah(val: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(val);
    }

    return {
        totalProductsCount,
        activeProductsCount,
        lowStockCount,
        totalInventoryValuation,
        formatRupiah,
    };
}