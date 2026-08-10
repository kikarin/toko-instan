import { router } from '@inertiajs/vue3';
import { computed, ref  } from 'vue';
import type {ComputedRef} from 'vue';
import { toast } from '@/components/ui/sonner';
import type {
    CatalogItem,
    CatalogItemKind,
    CatalogLabelItem,
} from '@/types/catalog';

export type CatalogTab = 'all' | 'category' | 'brand' | 'label';

export const COLOR_PRESETS = [
    '#e07c28',
    '#2563eb',
    '#059669',
    '#7c3aed',
    '#db2777',
    '#dc2626',
    '#0284c7',
];

export interface CatalogEditing {
    kind: CatalogItemKind;
    id: number;
    name: string;
    color?: string;
}

export function useCatalogManager(
    items: ComputedRef<{
        categories?: CatalogItem[];
        brands?: CatalogItem[];
        labels?: CatalogLabelItem[];
    }>,
) {
    const activeTab = ref<CatalogTab>('all');
    const searchQuery = ref('');

    // Form states
    const newCategory = ref('');
    const newBrand = ref('');
    const newLabel = ref('');
    const newLabelColor = ref('#e07c28');

    const editing = ref<CatalogEditing | null>(null);
    const deleteTarget = ref<{
        kind: CatalogItemKind;
        item: CatalogItem | CatalogLabelItem;
    } | null>(null);

    // Computed filtered items
    const filteredCategories = computed(() => {
        if (!searchQuery.value.trim()) {
            return items.value.categories ?? [];
        }

        return (items.value.categories ?? []).filter((c) =>
            c.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    });

    const filteredBrands = computed(() => {
        if (!searchQuery.value.trim()) {
            return items.value.brands ?? [];
        }

        return (items.value.brands ?? []).filter((b) =>
            b.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    });

    const filteredLabels = computed(() => {
        if (!searchQuery.value.trim()) {
            return items.value.labels ?? [];
        }

        return (items.value.labels ?? []).filter((l) =>
            l.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    });

    function saveCategory() {
        if (!newCategory.value.trim()) {
            return;
        }

        router.post(
            '/catalog/categories',
            { name: newCategory.value },
            {
                preserveScroll: true,
                onSuccess: () => {
                    newCategory.value = '';
                    toast.success('Kategori baru berhasil ditambahkan!');
                },
                onError: () => toast.error('Gagal menambah kategori.'),
            },
        );
    }

    function saveBrand() {
        if (!newBrand.value.trim()) {
            return;
        }

        router.post(
            '/catalog/brands',
            { name: newBrand.value },
            {
                preserveScroll: true,
                onSuccess: () => {
                    newBrand.value = '';
                    toast.success('Brand baru berhasil ditambahkan!');
                },
                onError: () => toast.error('Gagal menambah brand.'),
            },
        );
    }

    function saveLabel() {
        if (!newLabel.value.trim()) {
            return;
        }

        router.post(
            '/catalog/labels',
            { name: newLabel.value, color: newLabelColor.value },
            {
                preserveScroll: true,
                onSuccess: () => {
                    newLabel.value = '';
                    toast.success('Label & Tag promo berhasil ditambahkan!');
                },
                onError: () => toast.error('Gagal menambah label.'),
            },
        );
    }

    function startEdit(kind: CatalogItemKind, item: CatalogItem | CatalogLabelItem) {
        editing.value = {
            kind,
            id: item.id,
            name: item.name,
            color: 'color' in item ? (item.color ?? '#e07c28') : undefined,
        };
    }

    function base(kind: CatalogItemKind) {
        return kind === 'category'
            ? '/catalog/categories'
            : kind === 'brand'
              ? '/catalog/brands'
              : '/catalog/labels';
    }

    function commitEdit() {
        if (!editing.value || !editing.value.name.trim()) {
            return;
        }

        router.put(
            `${base(editing.value.kind)}/${editing.value.id}`,
            {
                name: editing.value.name,
                color: editing.value.color || undefined,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    editing.value = null;
                    toast.success('Katalog berhasil diperbarui!');
                },
                onError: () => toast.error('Gagal memperbarui item.'),
            },
        );
    }

    function confirmDelete() {
        if (!deleteTarget.value) {
            return;
        }

        const { kind, item } = deleteTarget.value;

        router.delete(`${base(kind)}/${item.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                deleteTarget.value = null;
                toast.success('Item katalog berhasil dihapus.');
            },
            onError: () => toast.error('Gagal menghapus item katalog.'),
        });
    }

    function isEditing(kind: CatalogItemKind, id: number) {
        return editing.value?.kind === kind && editing.value.id === id;
    }

    return {
        activeTab,
        searchQuery,
        newCategory,
        newBrand,
        newLabel,
        newLabelColor,
        editing,
        deleteTarget,
        filteredCategories,
        filteredBrands,
        filteredLabels,
        colorPresets: COLOR_PRESETS,
        saveCategory,
        saveBrand,
        saveLabel,
        startEdit,
        commitEdit,
        confirmDelete,
        isEditing,
    };
}