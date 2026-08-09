import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { InventoryProduct, StockAction } from '@/types/inventory';

export const INVENTORY_ACTION_LABELS: Record<
    StockAction,
    { title: string; button: string }
> = {
    in: { title: 'Stok Masuk', button: 'Tambah Stok' },
    out: { title: 'Stok Keluar', button: 'Kurangi Stok' },
    adjust: { title: 'Sesuaikan Stok', button: 'Simpan Penyesuaian' },
};

export function useInventoryActions() {
    const activeAction = ref<{
        product: InventoryProduct;
        action: StockAction;
    } | null>(null);
    const qty = ref('1');
    const newStock = ref('0');
    const reason = ref('');

    function openAction(product: InventoryProduct, action: StockAction) {
        activeAction.value = { product, action };
        qty.value = '1';
        newStock.value = String(product.stock);
        reason.value = '';
    }

    function submit() {
        if (!activeAction.value) {
            return;
        }

        const { product, action } = activeAction.value;
        const payload =
            action === 'adjust'
                ? {
                      new_stock: newStock.value,
                      reason: reason.value || undefined,
                  }
                : {
                      quantity: qty.value,
                      reason: reason.value || undefined,
                  };

        const options = {
            onSuccess: () => {
                activeAction.value = null;
                toast.success('Stok berhasil dicatat.');
            },
            onError: () =>
                toast.error('Gagal mencatat stok. Periksa kembali.'),
        };

        router.post(`/inventory/${product.id}/${action}`, payload, options);
    }

    return {
        activeAction,
        qty,
        newStock,
        reason,
        actionLabels: INVENTORY_ACTION_LABELS,
        openAction,
        submit,
    };
}