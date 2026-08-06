<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Trash2,
    Package,
    Search,
    PackageX,
    PackagePlus,
    Check,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/sonner';
import AppLayout from '@/layouts/AppLayout.vue';

interface Product {
    id: number;
    name: string;
    category: string;
    price: number;
    formatted_price: string;
    stock: number;
    sold: number;
    rating: number;
    tag: string | null;
    img: string | null;
    is_active: boolean;
}

interface Props {
    products?: Product[];
    categories?: string[];
}

const props = defineProps<Props>();

const searchQ = ref('');

const filtered = computed(() => {
    const q = searchQ.value.toLowerCase();

    if (!q) {
        return props.products ?? [];
    }

    return (props.products ?? []).filter(
        (p) =>
            p.name.toLowerCase().includes(q) ||
            p.category.toLowerCase().includes(q),
    );
});

function goToCreate() {
    router.visit('/products/create');
}

function editProduct(id: number) {
    router.visit(`/products/${id}/edit`);
}

const deleteTarget = ref<Product | null>(null);

const stockTarget = ref<Product | null>(null);
const stockInput = ref('0');
const stockLoading = ref(false);

function openStockDialog(p: Product) {
    stockTarget.value = p;
    stockInput.value = String(p.stock);
}

function saveStock() {
    if (!stockTarget.value) {
        return;
    }

    const id = stockTarget.value.id;
    const stock = Number(stockInput.value);

    if (Number.isNaN(stock) || stock < 0) {
        toast.error('Stok harus berupa angka tidak negatif.');

        return;
    }

    stockLoading.value = true;
    router.patch(
        `/products/${id}/stock`,
        { stock },
        {
            onSuccess: () => {
                toast.success('Stok produk diperbarui.');
                stockTarget.value = null;
            },
            onError: () => toast.error('Gagal memperbarui stok.'),
            onFinish: () => {
                stockLoading.value = false;
            },
        },
    );
}

function toggleActive(p: Product) {
    router.post(
        `/products/${p.id}/toggle-active`,
        {},
        {
            onSuccess: () =>
                toast.success(
                    p.is_active
                        ? 'Produk dinonaktifkan.'
                        : 'Produk diaktifkan.',
                ),
            onError: () => toast.error('Gagal mengubah status produk.'),
        },
    );
}

function deleteProduct(product: Product) {
    router.delete(`/products/${product.id}`, {
        onError: () => toast.error('Gagal menghapus produk.'),
    });
    deleteTarget.value = null;
}

const lowStock = (stock: number) => stock <= 5;
</script>

<template>
    <Head title="Manajemen Produk - Toko Instan" />

    <AppLayout activePage="Produk">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-5 p-4 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p
                        class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                    >
                        Seller · Katalog
                    </p>
                    <h1 class="text-2xl font-extrabold text-[#1c1c22]">
                        Manajemen Produk
                    </h1>
                </div>
                <Button
                    variant="amber"
                    class="text-sm font-bold"
                    @click="goToCreate"
                >
                    <Plus class="mr-2 h-4 w-4" /> Tambah Produk
                </Button>
            </div>

            <div class="relative max-w-sm">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-[#9090a0]"
                />
                <input
                    v-model="searchQ"
                    placeholder="Cari produk..."
                    class="w-full rounded-2xl border border-black/12 bg-white py-2.5 pr-3.5 pl-9 text-xs transition-all outline-none focus:border-[#e07c28] focus:ring-2 focus:ring-[#e07c28]/20"
                />
            </div>

            <div
                v-if="filtered.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="p in filtered"
                    :key="p.id"
                    class="flex flex-col gap-3 p-4"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-black/5 bg-black/5"
                        >
                            <img
                                v-if="p.img"
                                :src="p.img"
                                :alt="p.name"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-[#f5f4f0] text-[#c8c8d5]"
                            >
                                <Package class="h-6 w-6" />
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="line-clamp-2 text-xs leading-snug font-bold text-[#1c1c22]"
                            >
                                {{ p.name }}
                            </p>
                            <div class="mt-1.5 flex items-center gap-1.5">
                                <Badge
                                    variant="outline"
                                    class="px-2 py-0 text-[9px]"
                                    >{{ p.category }}</Badge
                                >
                                <Badge
                                    v-if="p.tag"
                                    variant="amber"
                                    class="px-2 py-0 text-[9px] font-bold"
                                >
                                    {{ p.tag }}
                                </Badge>
                                <Badge
                                    v-if="!p.is_active"
                                    variant="rose"
                                    class="px-2 py-0 text-[9px] font-bold"
                                >
                                    Nonaktif
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-end justify-between">
                        <div>
                            <p
                                class="font-mono text-sm font-extrabold text-[#e07c28]"
                            >
                                {{ p.formatted_price }}
                            </p>
                            <p class="text-[10px] text-[#9090a0]">
                                Terkirim: {{ p.sold }}
                            </p>
                        </div>
                        <button
                            @click="openStockDialog(p)"
                            class="cursor-pointer rounded-full px-2.5 py-1 text-[10px] font-bold transition-all hover:opacity-80"
                            :class="
                                !p.is_active
                                    ? 'bg-[#c8c8d51a] text-[#9090a0]'
                                    : lowStock(p.stock)
                                      ? 'bg-red-100 text-red-600'
                                      : 'bg-[#22a15a1a] text-[#22a15a]'
                            "
                            :title="'Klik untuk atur stok ' + p.name"
                        >
                            Stok {{ p.stock }}
                        </button>
                    </div>

                    <div class="mt-1 flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            class="flex-1 text-xs"
                            @click="editProduct(p.id)"
                        >
                            <Pencil class="mr-1.5 h-3.5 w-3.5" /> Edit
                        </Button>
                        <Button
                            :variant="p.is_active ? 'outline' : 'amber'"
                            size="sm"
                            class="text-xs"
                            @click="toggleActive(p)"
                            :title="
                                p.is_active
                                    ? 'Nonaktifkan produk'
                                    : 'Aktifkan kembali'
                            "
                        >
                            <PackageX
                                v-if="p.is_active"
                                class="mr-1.5 h-3.5 w-3.5"
                            />
                            <PackagePlus v-else class="mr-1.5 h-3.5 w-3.5" />
                            {{ p.is_active ? 'Nonaktif' : 'Aktif' }}
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-xs text-red-500 hover:bg-red-50 hover:text-red-600"
                            @click="deleteTarget = p"
                        >
                            <Trash2 class="mr-1.5 h-3.5 w-3.5" /> Hapus
                        </Button>
                    </div>
                </Card>
            </div>

            <div
                v-else
                class="rounded-3xl border border-black/5 bg-white py-20 text-center"
            >
                <Package class="mx-auto mb-2 h-10 w-10 text-[#c8c8d5]" />
                <p class="text-base font-semibold text-[#4a4a57]">
                    Belum ada produk
                </p>
                <p class="mt-1 text-xs text-[#9090a0]">
                    Tambahkan produk pertama untuk mulai berjualan
                </p>
            </div>
        </main>

        <ConfirmDialog
            :open="deleteTarget !== null"
            :title="`Hapus produk ${deleteTarget?.name ?? ''}?`"
            description="Produk akan dinonaktifkan dan disembunyikan dari katalog. Tindakan ini tidak dapat dibatalkan."
            confirm-label="Hapus"
            @confirm="deleteTarget && deleteProduct(deleteTarget)"
            @cancel="deleteTarget = null"
        />

        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="stockTarget"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                        @click="stockTarget = null"
                    />
                    <div
                        class="relative z-10 w-full max-w-sm rounded-2xl border border-black/8 bg-white p-5 shadow-2xl"
                    >
                        <div class="flex items-start gap-3.5">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#e07c2818] text-[#e07c28]"
                            >
                                <Package class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <h2
                                    class="text-sm font-extrabold text-[#1c1c22]"
                                >
                                    Atur Stok Produk
                                </h2>
                                <p class="mt-1 truncate text-xs text-[#9090a0]">
                                    {{ stockTarget?.name }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label
                                class="mb-1.5 block text-xs font-bold text-[#1c1c22]"
                            >
                                Jumlah Stok
                            </label>
                            <Input
                                v-model="stockInput"
                                type="number"
                                min="0"
                                placeholder="100"
                            />
                            <p class="mt-1.5 text-[10px] text-[#9090a0]">
                                Saat ini tersedia:
                                {{ stockTarget?.stock }} pcs
                            </p>
                        </div>

                        <div class="mt-5 flex justify-end gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                class="text-xs font-bold"
                                :disabled="stockLoading"
                                @click="stockTarget = null"
                            >
                                Batal
                            </Button>
                            <Button
                                variant="amber"
                                size="sm"
                                class="text-xs font-bold"
                                :disabled="stockLoading"
                                @click="saveStock"
                            >
                                <Check
                                    v-if="!stockLoading"
                                    class="mr-1.5 h-3.5 w-3.5"
                                />
                                {{ stockLoading ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
