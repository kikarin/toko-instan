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
    Star,
    BoxIcon,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    // CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Input } from '@/components/ui/input';
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
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6">

            <!-- Page Header -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase">
                        Seller · Katalog
                    </p>
                    <h1 class="text-2xl font-extrabold text-[#1c1c22]">
                        Manajemen Produk
                    </h1>
                </div>
                <Button variant="amber" class="text-sm font-bold" @click="goToCreate">
                    <Plus class="mr-2 h-4 w-4" /> Tambah Produk
                </Button>
            </div>

            <!-- Search -->
            <div class="relative max-w-sm">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-[#9090a0]" />
                <Input
                    v-model="searchQ"
                    placeholder="Cari produk atau kategori..."
                    class="pl-9"
                />
            </div>

            <!-- Product Grid -->
            <div
                v-if="filtered.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="p in filtered"
                    :key="p.id"
                    class="overflow-hidden py-0 shadow-sm transition-shadow hover:shadow-md"
                    :class="{ 'opacity-60': !p.is_active }"
                >
                    <!-- Product Image -->
                    <div class="relative h-40 w-full bg-[#f5f4f0]">
                        <img
                            v-if="p.img"
                            :src="p.img"
                            :alt="p.name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-[#c8c8d5]"
                        >
                            <Package class="h-10 w-10" />
                        </div>

                        <!-- Badges overlaying the image -->
                        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5">
                            <Badge v-if="p.tag" variant="amber" class="text-[9px] font-bold shadow-sm">
                                {{ p.tag }}
                            </Badge>
                            <Badge v-if="!p.is_active" variant="rose" class="text-[9px] font-bold shadow-sm">
                                Nonaktif
                            </Badge>
                        </div>

                        <!-- Stock badge top-right -->
                        <button
                            @click="openStockDialog(p)"
                            class="absolute top-2.5 right-2.5 cursor-pointer rounded-full px-2.5 py-1 text-[10px] font-bold shadow-sm backdrop-blur-sm transition-all hover:opacity-90"
                            :class="
                                !p.is_active
                                    ? 'bg-white/80 text-[#9090a0]'
                                    : lowStock(p.stock)
                                      ? 'bg-red-100/90 text-red-600'
                                      : 'bg-green-100/90 text-green-700'
                            "
                            :title="'Klik untuk atur stok ' + p.name"
                        >
                            <BoxIcon class="mr-1 inline h-3 w-3" />
                            Stok {{ p.stock }}
                        </button>
                    </div>

                    <!-- Card Header: Name + Category -->
                    <CardHeader class="pb-2 pt-4">
                        <CardTitle class="line-clamp-2 text-sm leading-snug">
                            {{ p.name }}
                        </CardTitle>
                        <CardDescription class="flex items-center gap-1.5">
                            <Badge variant="outline" class="px-2 py-0 text-[9px]">
                                {{ p.category }}
                            </Badge>
                        </CardDescription>
                        <CardAction>
                            <div class="flex items-center gap-0.5 text-[11px] text-[#e07c28]">
                                <Star class="h-3 w-3 fill-[#e07c28]" />
                                {{ p.rating.toFixed(1) }}
                            </div>
                        </CardAction>
                    </CardHeader>

                    <!-- Card Content: Price & Sold -->
                    <CardContent class="pb-3">
                        <p class="font-mono text-base font-extrabold text-[#e07c28]">
                            {{ p.formatted_price }}
                        </p>
                        <p class="mt-0.5 text-[10px] text-[#9090a0]">
                            {{ p.sold }} terjual
                        </p>
                    </CardContent>

                    <!-- Card Footer: Actions -->
                    <CardFooter class="flex gap-2 pt-3 pb-4">
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
                            :title="p.is_active ? 'Nonaktifkan produk' : 'Aktifkan kembali'"
                        >
                            <PackageX v-if="p.is_active" class="mr-1.5 h-3.5 w-3.5" />
                            <PackagePlus v-else class="mr-1.5 h-3.5 w-3.5" />
                            {{ p.is_active ? 'Nonaktif' : 'Aktif' }}
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="px-2 text-xs text-red-500 hover:bg-red-50 hover:text-red-600"
                            @click="deleteTarget = p"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty State — also wrapped in a Card -->
            <Card v-else class="py-16 text-center shadow-sm">
                <CardContent class="flex flex-col items-center gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f5f4f0]">
                        <Package class="h-7 w-7 text-[#c8c8d5]" />
                    </div>
                    <div>
                        <p class="text-base font-semibold text-[#4a4a57]">Belum ada produk</p>
                        <p class="mt-1 text-xs text-[#9090a0]">
                            Tambahkan produk pertama untuk mulai berjualan
                        </p>
                    </div>
                    <Button variant="amber" size="sm" class="mt-2 text-xs font-bold" @click="goToCreate">
                        <Plus class="mr-1.5 h-3.5 w-3.5" /> Tambah Produk Pertama
                    </Button>
                </CardContent>
            </Card>
        </main>

        <!-- Delete Confirm Dialog -->
        <ConfirmDialog
            :open="deleteTarget !== null"
            :title="`Hapus produk ${deleteTarget?.name ?? ''}?`"
            description="Produk akan dinonaktifkan dan disembunyikan dari katalog. Tindakan ini tidak dapat dibatalkan."
            confirm-label="Hapus"
            @confirm="deleteTarget && deleteProduct(deleteTarget)"
            @cancel="deleteTarget = null"
        />

        <!-- Stock Edit Modal -->
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
                    <Card class="relative z-10 w-full max-w-sm py-0 shadow-2xl">
                        <CardHeader class="pt-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#e07c2818] text-[#e07c28]">
                                <Package class="h-5 w-5" />
                            </div>
                            <CardTitle class="text-sm">Atur Stok Produk</CardTitle>
                            <CardDescription class="truncate text-xs">
                                {{ stockTarget?.name }}
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="pb-2">
                            <label class="mb-1.5 block text-xs font-bold text-[#1c1c22]">
                                Jumlah Stok
                            </label>
                            <Input
                                v-model="stockInput"
                                type="number"
                                min="0"
                                placeholder="100"
                            />
                            <p class="mt-1.5 text-[10px] text-[#9090a0]">
                                Saat ini tersedia: {{ stockTarget?.stock }} pcs
                            </p>
                        </CardContent>

                        <CardFooter class="flex justify-end gap-2 pt-4 pb-5">
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
                                <Check v-if="!stockLoading" class="mr-1.5 h-3.5 w-3.5" />
                                {{ stockLoading ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                        </CardFooter>
                    </Card>
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
