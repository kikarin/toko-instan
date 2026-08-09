import { router } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { Product } from '@/types/product';

export function useProductActions(deleteTarget: Ref<Product | null>) {
    function goToCreate() {
        router.visit('/products/create');
    }

    function editProduct(id: number) {
        router.visit(`/products/${id}/edit`);
    }

    function toggleActive(p: Product) {
        router.post(
            `/products/${p.id}/toggle-active`,
            {},
            {
                preserveScroll: true,
                onSuccess: () =>
                    toast.success(
                        p.is_active
                            ? `Produk ${p.name} kini nonaktif.`
                            : `Produk ${p.name} berhasil diaktifkan kembali.`,
                    ),
                onError: () => toast.error('Gagal mengubah status produk.'),
            },
        );
    }

    function deleteProduct(product: Product) {
        router.delete(`/products/${product.id}`, {
            preserveScroll: true,
            onSuccess: () => toast.success('Produk berhasil dihapus.'),
            onError: () => toast.error('Gagal menghapus produk.'),
        });
        deleteTarget.value = null;
    }

    const lowStock = (stock: number) => stock <= 5;

    return {
        goToCreate,
        editProduct,
        toggleActive,
        deleteProduct,
        lowStock,
    };
}