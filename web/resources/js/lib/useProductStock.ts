import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { Product } from '@/types/product';

export function useProductStock() {
    const stockTarget = ref<Product | null>(null);
    const stockInput = ref('0');
    const stockLoading = ref(false);

    function openStockDialog(p: Product) {
        stockTarget.value = p;
        stockInput.value = String(p.stock);
    }

    function closeStockDialog() {
        stockTarget.value = null;
    }

    function adjustStock(delta: number) {
        const current = Number(stockInput.value) || 0;
        const updated = Math.max(0, current + delta);
        stockInput.value = String(updated);
    }

    function saveStock() {
        if (!stockTarget.value) {
            return;
        }

        const id = stockTarget.value.id;
        const stock = Number(stockInput.value);

        if (Number.isNaN(stock) || stock < 0) {
            toast.error('Stok harus berupa angka positif.');

            return;
        }

        stockLoading.value = true;
        router.patch(
            `/products/${id}/stock`,
            { stock },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Jumlah stok produk berhasil diperbarui!');
                    closeStockDialog();
                },
                onError: () => toast.error('Gagal memperbarui stok.'),
                onFinish: () => {
                    stockLoading.value = false;
                },
            },
        );
    }

    return {
        stockTarget,
        stockInput,
        stockLoading,
        openStockDialog,
        closeStockDialog,
        adjustStock,
        saveStock,
    };
}