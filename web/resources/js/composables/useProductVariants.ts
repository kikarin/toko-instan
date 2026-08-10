import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { ProductVariant } from '@/types/product';

export function useProductVariants() {
    const form = reactive({
        name: '',
        sku: '',
        price: '',
        stock: '0',
    });

    const editingId = ref<number | null>(null);
    const editingForm = reactive({
        name: '',
        sku: '',
        price: '',
        stock: '0',
        is_active: true,
    });
    const deleteTarget = ref<ProductVariant | null>(null);
    const productIdForEdit = ref<number | null>(null);

    function resetCreateForm() {
        form.name = '';
        form.sku = '';
        form.price = '';
        form.stock = '0';
    }

    function saveVariant(productId: number) {
        router.post(
            `/products/${productId}/variants`,
            {
                name: form.name,
                sku: form.sku || undefined,
                price: form.price || undefined,
                stock: form.stock || 0,
            },
            {
                onSuccess: () => {
                    resetCreateForm();
                    toast.success('Varian ditambahkan.');
                },
                onError: () => toast.error('Gagal menambah varian.'),
            },
        );
    }

    function startEdit(v: ProductVariant, productId: number) {
        editingId.value = v.id;
        editingForm.name = v.name;
        editingForm.sku = v.sku ?? '';
        editingForm.price = v.price === null ? '' : String(v.price);
        editingForm.stock = String(v.stock);
        editingForm.is_active = v.is_active;
        productIdForEdit.value = productId;
    }

    function commitEdit() {
        if (productIdForEdit.value === null || editingId.value === null) {
            return;
        }

        router.put(
            `/products/${productIdForEdit.value}/variants/${editingId.value}`,
            {
                name: editingForm.name,
                sku: editingForm.sku || undefined,
                price: editingForm.price || undefined,
                stock: editingForm.stock || 0,
                is_active: editingForm.is_active,
            },
            {
                onSuccess: () => {
                    editingId.value = null;
                    productIdForEdit.value = null;
                    toast.success('Varian diperbarui.');
                },
                onError: () => toast.error('Gagal memperbarui varian.'),
            },
        );
    }

    function cancelEdit() {
        editingId.value = null;
        productIdForEdit.value = null;
    }

    function confirmDelete(productId: number) {
        if (!deleteTarget.value) {
            return;
        }

        router.delete(`/products/${productId}/variants/${deleteTarget.value.id}`, {
            onSuccess: () => {
                deleteTarget.value = null;
                toast.success('Varian dihapus.');
            },
            onError: () => toast.error('Gagal menghapus varian.'),
        });
    }

    return {
        form,
        editingId,
        editingForm,
        deleteTarget,
        productIdForEdit,
        saveVariant,
        startEdit,
        commitEdit,
        cancelEdit,
        confirmDelete,
    };
}