import { router, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { Address } from '@/types/address';

const BLANK_FORM = {
    label: '',
    recipient_name: '',
    phone: '',
    address: '',
    district: '',
    city: '',
    province: '',
    postal_code: '',
    is_default: false,
};

export function useAddresses() {
    const showForm = ref(false);
    const editingId = ref<number | null>(null);
    const deleteTarget = ref<Address | null>(null);

    const form = reactive({ ...BLANK_FORM });

    function getBaseUrl() {
        const storeSlug = (usePage().props.store as any)?.slug ?? '';

        return storeSlug ? `/${storeSlug}` : '';
    }

    function openNew() {
        editingId.value = null;
        Object.assign(form, BLANK_FORM);
        showForm.value = true;
    }

    function openEdit(a: Address) {
        editingId.value = a.id;
        Object.assign(form, {
            label: a.label ?? '',
            recipient_name: a.recipient_name,
            phone: a.phone,
            address: a.address,
            district: a.district || '',
            city: a.city,
            province: a.province,
            postal_code: a.postal_code,
            is_default: a.is_default,
        });
        showForm.value = true;
    }

    function payload() {
        return {
            label: form.label || undefined,
            recipient_name: form.recipient_name,
            phone: form.phone,
            address: form.address,
            district: form.district,
            city: form.city,
            province: form.province,
            postal_code: form.postal_code,
            is_default: form.is_default,
        };
    }

    function save() {
        const options = {
            onSuccess: () => {
                showForm.value = false;
                editingId.value = null;
                toast.success('Alamat tersimpan.');
            },
            onError: () =>
                toast.error(
                    'Gagal menyimpan alamat. Periksa kembali formulir.',
                ),
        };

        if (editingId.value === null) {
            router.post(`${getBaseUrl()}/addresses`, payload(), options);
        } else {
            router.put(`${getBaseUrl()}/addresses/${editingId.value}`, payload(), options);
        }
    }

    function makeDefault(a: Address) {
        router.patch(`${getBaseUrl()}/addresses/${a.id}/default`, {}, {
            onError: () => toast.error('Gagal mengubah alamat utama.'),
        });
    }

    function confirmDelete() {
        if (!deleteTarget.value) {
            return;
        }

        router.delete(`${getBaseUrl()}/addresses/${deleteTarget.value.id}`, {
            onSuccess: () => toast.success('Alamat dihapus.'),
            onError: () => toast.error('Gagal menghapus alamat.'),
        });
    }

    return {
        showForm,
        editingId,
        deleteTarget,
        form,
        openNew,
        openEdit,
        save,
        makeDefault,
        confirmDelete,
    };
}