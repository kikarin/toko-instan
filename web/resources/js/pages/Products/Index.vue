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
    Layers,
    LayoutGrid,
    List,
    AlertTriangle,
    Sparkles,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
    sku?: string;
}

interface Props {
    products?: Product[];
    categories?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    products: () => [],
    categories: () => [],
});

const searchQ = ref('');
const selectedCategory = ref<string>('Semua');
const statusFilter = ref<'all' | 'active' | 'inactive' | 'low_stock'>('all');
const viewMode = ref<'grid' | 'table'>('grid');

// Pagination state
const currentPage = ref(1);
const perPage = ref(6);

watch([searchQ, selectedCategory, statusFilter, perPage], () => {
    currentPage.value = 1;
});

// Computed Metrics & Stats
const totalProductsCount = computed(() => (props.products ?? []).length);
const activeProductsCount = computed(
    () => (props.products ?? []).filter((p) => p.is_active).length,
);
const lowStockCount = computed(
    () =>
        (props.products ?? []).filter((p) => p.stock <= 5 && p.is_active)
            .length,
);
const totalInventoryValuation = computed(() => {
    return (props.products ?? []).reduce(
        (acc, p) => acc + p.price * p.stock,
        0,
    );
});

function formatRupiah(val: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
}

// Filtered products list
const filtered = computed(() => {
    let result = props.products ?? [];

    // Search query
    if (searchQ.value.trim()) {
        const q = searchQ.value.toLowerCase();
        result = result.filter(
            (p) =>
                p.name.toLowerCase().includes(q) ||
                p.category.toLowerCase().includes(q) ||
                (p.sku && p.sku.toLowerCase().includes(q)),
        );
    }

    // Category filter
    if (selectedCategory.value !== 'Semua') {
        result = result.filter((p) => p.category === selectedCategory.value);
    }

    // Status filter
    if (statusFilter.value === 'active') {
        result = result.filter((p) => p.is_active);
    } else if (statusFilter.value === 'inactive') {
        result = result.filter((p) => !p.is_active);
    } else if (statusFilter.value === 'low_stock') {
        result = result.filter((p) => p.stock <= 5 && p.is_active);
    }

    return result;
});

const totalPages = computed(
    () => Math.ceil(filtered.value.length / perPage.value) || 1,
);

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;

    return filtered.value.slice(start, start + perPage.value);
});

const paginationStart = computed(() => {
    if (filtered.value.length === 0) {
        return 0;
    }

    return (currentPage.value - 1) * perPage.value + 1;
});

const paginationEnd = computed(() => {
    return Math.min(currentPage.value * perPage.value, filtered.value.length);
});

function goToPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

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
</script>

<template>
    <Head title="Pusat Manajemen Produk — Dashboard Merchant" />

    <AppLayout activePage="Produk">
        <div
            class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- ── Header Banner Dashboard Hub ── -->
            <div
                class="relative overflow-hidden rounded-3xl bg-zinc-900 p-6 text-white shadow-xl sm:p-8"
            >
                <div
                    class="absolute -top-10 -right-10 h-64 w-64 rounded-full bg-gradient-to-br from-[#e07c28]/40 via-amber-500/20 to-transparent blur-3xl"
                />
                <div
                    class="absolute -bottom-10 -left-10 h-64 w-64 rounded-full bg-gradient-to-br from-emerald-600/30 via-teal-500/10 to-transparent blur-3xl"
                />

                <div
                    class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-xs font-black text-amber-400"
                            >
                                <Sparkles class="h-3.5 w-3.5" /> SMART INVENTORY
                                CONTROL
                            </span>
                        </div>
                        <h1
                            class="text-2xl font-black tracking-tight text-white sm:text-3xl"
                        >
                            Katalog & Manajemen Produk Toko
                        </h1>
                        <p class="max-w-2xl text-xs text-zinc-400 sm:text-sm">
                            Kelola stok persediaan barang, varian SKU, harga
                            penawaran, dan status publikasi produk secara
                            terpusat.
                        </p>
                    </div>

                    <div
                        class="flex w-full shrink-0 items-center gap-3 sm:w-auto"
                    >
                        <Button
                            variant="amber"
                            size="lg"
                            class="w-full cursor-pointer rounded-2xl px-6 text-xs font-extrabold shadow-lg shadow-amber-500/25 sm:w-auto"
                            @click="goToCreate"
                        >
                            <Plus class="mr-2 h-4 w-4" /> Tambah Produk Baru
                        </Button>
                    </div>
                </div>

                <!-- Metrics Overview Cards (Mobile Responsive) -->
                <div
                    class="relative z-10 mt-6 grid grid-cols-2 gap-2.5 border-t border-white/10 pt-6 sm:gap-3 lg:grid-cols-4"
                >
                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Total Produk</span
                        >
                        <span
                            class="truncate text-sm font-black text-white sm:text-2xl"
                            >{{ totalProductsCount }} Items</span
                        >
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Produk Aktif</span
                        >
                        <span
                            class="truncate text-sm font-black text-emerald-400 sm:text-2xl"
                            >{{ activeProductsCount }} Live</span
                        >
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Stok Menipis</span
                        >
                        <span
                            class="flex items-center gap-1 truncate text-xs font-black text-amber-400 sm:text-2xl"
                        >
                            <AlertTriangle
                                class="h-4 w-4 shrink-0 text-amber-400"
                                v-if="lowStockCount > 0"
                            />
                            <span class="truncate"
                                >{{ lowStockCount }} Restock</span
                            >
                        </span>
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Estimasi Inventaris</span
                        >
                        <span
                            class="truncate font-mono text-xs font-black text-amber-300 sm:text-xl"
                            :title="formatRupiah(totalInventoryValuation)"
                            >{{ formatRupiah(totalInventoryValuation) }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- ── Search & Filter Control Bar ── -->
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <!-- Search & Filters -->
                <div
                    class="flex flex-1 flex-col items-center gap-3 sm:flex-row"
                >
                    <div class="relative w-full sm:w-80">
                        <Search
                            class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-zinc-400"
                        />
                        <Input
                            v-model="searchQ"
                            placeholder="Cari nama produk, SKU, atau kategori..."
                            class="h-10 rounded-2xl border-black/10 bg-white pl-10 text-xs"
                        />
                    </div>

                    <!-- Filter Status Buttons -->
                    <div
                        class="flex w-full items-center gap-1 overflow-x-auto rounded-2xl border border-black/8 bg-[#faf9f6] p-1 sm:w-auto"
                    >
                        <button
                            @click="statusFilter = 'all'"
                            class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-all"
                            :class="
                                statusFilter === 'all'
                                    ? 'border border-black/8 bg-white text-[#1c1c22] shadow-xs'
                                    : 'text-[#9090a0] hover:text-[#1c1c22]'
                            "
                        >
                            Semua ({{ totalProductsCount }})
                        </button>
                        <button
                            @click="statusFilter = 'active'"
                            class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-all"
                            :class="
                                statusFilter === 'active'
                                    ? 'border border-black/8 bg-white text-[#1c1c22] shadow-xs'
                                    : 'text-[#9090a0] hover:text-[#1c1c22]'
                            "
                        >
                            Aktif ({{ activeProductsCount }})
                        </button>
                        <button
                            @click="statusFilter = 'low_stock'"
                            class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-all"
                            :class="
                                statusFilter === 'low_stock'
                                    ? 'border border-black/8 bg-white text-amber-600 shadow-xs'
                                    : 'text-[#9090a0] hover:text-[#1c1c22]'
                            "
                        >
                            Restock ({{ lowStockCount }})
                        </button>
                    </div>
                </div>

                <!-- View Switcher Toggle -->
                <div
                    class="flex shrink-0 items-center justify-between gap-3 sm:justify-end"
                >
                    <div
                        class="flex items-center gap-1 rounded-2xl border border-black/8 bg-[#faf9f6] p-1"
                    >
                        <button
                            @click="viewMode = 'grid'"
                            class="cursor-pointer rounded-xl p-2 text-xs font-bold transition-all"
                            :class="
                                viewMode === 'grid'
                                    ? 'bg-white text-black shadow-xs'
                                    : 'text-zinc-400 hover:text-black'
                            "
                            title="Tampilan Kartu Grid"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                        <button
                            @click="viewMode = 'table'"
                            class="cursor-pointer rounded-xl p-2 text-xs font-bold transition-all"
                            :class="
                                viewMode === 'table'
                                    ? 'bg-white text-black shadow-xs'
                                    : 'text-zinc-400 hover:text-black'
                            "
                            title="Tampilan Tabel Rinci"
                        >
                            <List class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── VIEW MODE 1: GRID CARDS ── -->
            <div
                v-if="filtered.length > 0 && viewMode === 'grid'"
                class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="p in paginatedProducts"
                    :key="p.id"
                    class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-black/8 bg-white transition-all duration-300 hover:border-amber-500/30 hover:shadow-xl"
                    :class="{ 'opacity-65 grayscale-20': !p.is_active }"
                >
                    <div>
                        <!-- Product Image Banner -->
                        <div
                            class="relative aspect-4/3 w-full overflow-hidden bg-[#faf9f6]"
                        >
                            <img
                                v-if="p.img"
                                :src="p.img"
                                :alt="p.name"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-[#c8c8d5]"
                            >
                                <Package class="h-12 w-12" />
                            </div>

                            <!-- Badges overlaying the image -->
                            <div
                                class="absolute top-3 left-3 flex flex-wrap gap-1.5"
                            >
                                <Badge
                                    v-if="p.tag"
                                    variant="amber"
                                    class="px-2.5 py-0.5 text-[10px] font-black uppercase shadow-xs"
                                >
                                    {{ p.tag }}
                                </Badge>
                                <Badge
                                    v-if="!p.is_active"
                                    variant="rose"
                                    class="px-2.5 py-0.5 text-[10px] font-black uppercase shadow-xs"
                                >
                                    Nonaktif (Draft)
                                </Badge>
                            </div>

                            <!-- Interactive Stock badge top-right -->
                            <button
                                @click="openStockDialog(p)"
                                class="absolute top-3 right-3 flex cursor-pointer items-center gap-1.5 rounded-2xl px-3 py-1 text-[11px] font-extrabold shadow-md backdrop-blur-md transition-all hover:scale-105"
                                :class="
                                    !p.is_active
                                        ? 'bg-white/90 text-zinc-600'
                                        : lowStock(p.stock)
                                          ? 'animate-pulse bg-rose-500 text-white'
                                          : 'bg-emerald-600 text-white'
                                "
                                :title="
                                    'Klik untuk ubah stok persediaan ' + p.name
                                "
                            >
                                <BoxIcon class="h-3.5 w-3.5" />
                                Stok: {{ p.stock }} pcs
                            </button>

                            <!-- Bottom Image Gradient Overlay for Price -->
                            <div
                                class="absolute inset-x-0 bottom-0 flex items-end justify-between bg-gradient-to-t from-black/80 via-black/30 to-transparent p-4 text-white"
                            >
                                <div>
                                    <p
                                        class="font-mono text-[10px] font-bold text-amber-300"
                                    >
                                        SKU: {{ p.sku || 'NK-00' + p.id }}
                                    </p>
                                    <p
                                        class="font-mono text-lg font-black text-white"
                                    >
                                        {{ p.formatted_price }}
                                    </p>
                                </div>
                                <div
                                    class="flex items-center gap-1 rounded-xl bg-black/40 px-2 py-1 text-xs font-black text-amber-400 backdrop-blur-xs"
                                >
                                    <Star
                                        class="h-3.5 w-3.5 fill-amber-400 text-amber-400"
                                    />
                                    {{ p.rating ? p.rating.toFixed(1) : '4.8' }}
                                </div>
                            </div>
                        </div>

                        <!-- Card Header & Content -->
                        <div class="flex flex-col gap-2 p-5">
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <Badge
                                    variant="outline"
                                    class="border-black/10 px-2.5 py-0.5 text-[10px] font-bold text-zinc-600"
                                >
                                    {{ p.category }}
                                </Badge>
                                <span
                                    class="text-[11px] font-extrabold text-zinc-400"
                                >
                                    {{ p.sold }} Terjual
                                </span>
                            </div>

                            <h3
                                class="line-clamp-2 text-sm leading-snug font-extrabold text-[#1c1c22] transition-colors group-hover:text-amber-600"
                            >
                                {{ p.name }}
                            </h3>
                        </div>
                    </div>

                    <!-- Card Actions Bar -->
                    <div
                        class="mt-2 flex flex-wrap items-center gap-2 border-t border-black/5 p-4 pt-0"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-9 flex-1 rounded-xl text-xs font-bold"
                            @click="editProduct(p.id)"
                        >
                            <Pencil class="mr-1.5 h-3.5 w-3.5" /> Edit
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-9 flex-1 rounded-xl text-xs font-bold"
                            @click="router.visit(`/products/${p.id}/variants`)"
                        >
                            <Layers
                                class="mr-1.5 h-3.5 w-3.5 text-indigo-500"
                            />
                            Varian
                        </Button>
                        <Button
                            :variant="p.is_active ? 'outline' : 'amber'"
                            size="sm"
                            class="h-9 rounded-xl px-3 text-xs font-bold"
                            @click="toggleActive(p)"
                            :title="
                                p.is_active
                                    ? 'Sembunyikan produk'
                                    : 'Aktifkan produk'
                            "
                        >
                            <PackageX
                                v-if="p.is_active"
                                class="h-3.5 w-3.5 text-rose-500"
                            />
                            <PackagePlus v-else class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-9 w-9 rounded-xl p-0 text-zinc-400 hover:bg-rose-50 hover:text-rose-600"
                            @click="deleteTarget = p"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </Card>
            </div>

            <!-- ── VIEW MODE 2: TABLE VIEW ── -->
            <Card
                v-else-if="filtered.length > 0 && viewMode === 'table'"
                class="overflow-hidden rounded-3xl border-black/8 bg-white shadow-xs"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-black/8 bg-[#faf9f6] text-[11px] font-black tracking-wider text-zinc-500 uppercase"
                            >
                                <th class="p-4 pl-6">Produk & Visual</th>
                                <th class="p-4">Kategori & SKU</th>
                                <th class="p-4">Harga Jual</th>
                                <th class="p-4">Stok Persediaan</th>
                                <th class="p-4">Terjual</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 text-xs">
                            <tr
                                v-for="p in paginatedProducts"
                                :key="p.id"
                                class="transition-colors hover:bg-amber-50/30"
                                :class="{ 'opacity-60': !p.is_active }"
                            >
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-black/10 bg-zinc-100"
                                        >
                                            <img
                                                v-if="p.img"
                                                :src="p.img"
                                                class="h-full w-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="flex h-full w-full items-center justify-center text-zinc-300"
                                            >
                                                <Package class="h-5 w-5" />
                                            </div>
                                        </div>
                                        <div>
                                            <p
                                                class="line-clamp-1 max-w-xs font-extrabold text-[#1c1c22]"
                                            >
                                                {{ p.name }}
                                            </p>
                                            <div
                                                class="mt-0.5 flex items-center gap-1.5"
                                            >
                                                <Badge
                                                    v-if="p.tag"
                                                    variant="amber"
                                                    class="px-1.5 py-0 text-[9px] font-extrabold"
                                                    >{{ p.tag }}</Badge
                                                >
                                                <span
                                                    class="flex items-center gap-0.5 text-[10px] font-bold text-amber-600"
                                                >
                                                    <Star
                                                        class="h-3 w-3 fill-amber-400"
                                                    />
                                                    {{
                                                        p.rating
                                                            ? p.rating.toFixed(
                                                                  1,
                                                              )
                                                            : '4.8'
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="block font-extrabold text-zinc-700"
                                        >{{ p.category }}</span
                                    >
                                    <span
                                        class="font-mono text-[10px] text-zinc-400"
                                        >SKU:
                                        {{ p.sku || 'NK-00' + p.id }}</span
                                    >
                                </td>
                                <td
                                    class="p-4 font-mono text-sm font-extrabold text-amber-600"
                                >
                                    {{ p.formatted_price }}
                                </td>
                                <td class="p-4">
                                    <button
                                        @click="openStockDialog(p)"
                                        class="cursor-pointer rounded-xl border px-2.5 py-1 text-xs font-extrabold transition-transform hover:scale-105"
                                        :class="
                                            lowStock(p.stock)
                                                ? 'border-rose-200 bg-rose-50 text-rose-600'
                                                : 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        "
                                    >
                                        {{ p.stock }} pcs
                                    </button>
                                </td>
                                <td class="p-4 font-bold text-zinc-600">
                                    {{ p.sold }} unit
                                </td>
                                <td class="p-4">
                                    <Badge
                                        :variant="p.is_active ? 'teal' : 'rose'"
                                        class="text-[10px] font-black uppercase"
                                    >
                                        {{ p.is_active ? 'Publik' : 'Draft' }}
                                    </Badge>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0"
                                            @click="editProduct(p.id)"
                                            title="Edit Produk"
                                        >
                                            <Pencil
                                                class="h-3.5 w-3.5 text-zinc-600"
                                            />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0"
                                            @click="
                                                router.visit(
                                                    `/products/${p.id}/variants`,
                                                )
                                            "
                                            title="Varian SKU"
                                        >
                                            <Layers
                                                class="h-3.5 w-3.5 text-indigo-600"
                                            />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0 text-rose-500 hover:bg-rose-50"
                                            @click="deleteTarget = p"
                                            title="Hapus Produk"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <!-- ── Pagination Controls Bar ── -->
            <div
                v-if="filtered.length > 0"
                class="flex flex-col items-center justify-between gap-4 border-t border-black/8 pt-4 sm:flex-row"
            >
                <!-- Status text & per page select -->
                <div
                    class="flex items-center gap-3 text-xs font-semibold text-zinc-500"
                >
                    <span
                        >Menampilkan
                        <strong class="font-black text-[#1c1c22]"
                            >{{ paginationStart }} - {{ paginationEnd }}</strong
                        >
                        dari
                        <strong class="font-black text-[#1c1c22]">{{
                            filtered.length
                        }}</strong>
                        produk</span
                    >
                    <div
                        class="flex items-center gap-1.5 rounded-xl border border-black/10 bg-white px-2.5 py-1"
                    >
                        <span>Per Halaman:</span>
                        <select
                            v-model.number="perPage"
                            class="cursor-pointer border-none bg-transparent text-xs font-bold text-[#1c1c22] focus:outline-none"
                        >
                            <option :value="6">6</option>
                            <option :value="12">12</option>
                            <option :value="24">24</option>
                            <option :value="48">48</option>
                        </select>
                    </div>
                </div>

                <!-- Page Navigation Buttons -->
                <div class="flex items-center gap-1">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 cursor-pointer rounded-xl px-3 text-xs font-bold"
                        :disabled="currentPage === 1"
                        @click="goToPage(currentPage - 1)"
                    >
                        <ChevronLeft class="mr-1 h-4 w-4" /> Prev
                    </Button>

                    <div class="flex items-center gap-1 px-1">
                        <button
                            v-for="p in totalPages"
                            :key="p"
                            @click="goToPage(p)"
                            class="h-9 w-9 cursor-pointer rounded-xl text-xs font-black transition-all"
                            :class="
                                currentPage === p
                                    ? 'bg-amber-500 text-white shadow-xs'
                                    : 'border border-black/8 bg-white text-zinc-600 hover:bg-zinc-100'
                            "
                        >
                            {{ p }}
                        </button>
                    </div>

                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 cursor-pointer rounded-xl px-3 text-xs font-bold"
                        :disabled="currentPage === totalPages"
                        @click="goToPage(currentPage + 1)"
                    >
                        Next <ChevronRight class="ml-1 h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- ── EMPTY STATE ── -->
            <Card
                v-else
                class="rounded-3xl border-black/8 bg-white py-16 text-center shadow-xs"
            >
                <CardContent class="flex flex-col items-center gap-3">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-3xl bg-amber-50 text-amber-500"
                    >
                        <Package class="h-8 w-8" />
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-[#1c1c22]">
                            Tidak Ada Produk Ditemukan
                        </h3>
                        <p class="mt-1 max-w-sm text-xs text-zinc-400">
                            Tidak ada produk yang sesuai dengan kriteria
                            pencarian atau filter Anda.
                        </p>
                    </div>
                    <Button
                        variant="amber"
                        size="sm"
                        class="mt-2 rounded-xl px-5 text-xs font-bold"
                        @click="goToCreate"
                    >
                        <Plus class="mr-1.5 h-3.5 w-3.5" /> Tambah Produk Baru
                    </Button>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirm Dialog -->
        <ConfirmDialog
            :open="deleteTarget !== null"
            :title="`Hapus produk ${deleteTarget?.name ?? ''}?`"
            description="Produk akan dinonaktifkan dari katalog toko. Anda dapat mengaktifkannya kembali sewaktu-waktu."
            confirm-label="Hapus Produk"
            tone="danger"
            @confirm="deleteTarget && deleteProduct(deleteTarget)"
            @cancel="deleteTarget = null"
        />

        <!-- Advanced Stock Adjuster Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div
                    v-if="stockTarget"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                >
                    <div class="absolute inset-0" @click="stockTarget = null" />
                    <Card
                        class="relative z-10 w-full max-w-md overflow-hidden rounded-3xl border-black/10 bg-white shadow-2xl"
                    >
                        <CardHeader
                            class="border-b border-black/5 bg-[#faf9f6] p-6 pb-4"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500 font-bold text-white shadow-xs"
                                >
                                    <BoxIcon class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle
                                        class="text-base font-black text-[#1c1c22]"
                                        >Atur Stok Persediaan</CardTitle
                                    >
                                    <CardDescription
                                        class="truncate text-xs font-semibold text-zinc-500"
                                    >
                                        {{ stockTarget?.name }}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="flex flex-col gap-4 p-6">
                            <div class="flex flex-col gap-2">
                                <label
                                    class="flex items-center justify-between text-xs font-bold text-[#1c1c22]"
                                >
                                    <span>Jumlah Unit Stok Tersedia</span>
                                    <span
                                        class="font-mono text-[10px] text-zinc-400"
                                        >SKU:
                                        {{
                                            stockTarget?.sku || 'NK-DEFAULT'
                                        }}</span
                                    >
                                </label>

                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="adjustStock(-10)"
                                        class="h-10 cursor-pointer rounded-xl border border-black/10 bg-zinc-100 px-3 text-xs font-black hover:bg-zinc-200"
                                    >
                                        -10
                                    </button>
                                    <button
                                        type="button"
                                        @click="adjustStock(-1)"
                                        class="h-10 cursor-pointer rounded-xl border border-black/10 bg-zinc-100 px-3 text-xs font-black hover:bg-zinc-200"
                                    >
                                        -1
                                    </button>
                                    <Input
                                        v-model="stockInput"
                                        type="number"
                                        min="0"
                                        class="h-10 flex-1 rounded-xl text-center font-mono text-sm font-black"
                                    />
                                    <button
                                        type="button"
                                        @click="adjustStock(1)"
                                        class="h-10 cursor-pointer rounded-xl border border-black/10 bg-zinc-100 px-3 text-xs font-black hover:bg-zinc-200"
                                    >
                                        +1
                                    </button>
                                    <button
                                        type="button"
                                        @click="adjustStock(10)"
                                        class="h-10 cursor-pointer rounded-xl border border-black/10 bg-zinc-100 px-3 text-xs font-black hover:bg-zinc-200"
                                    >
                                        +10
                                    </button>
                                </div>
                            </div>

                            <div
                                class="flex items-center gap-2 rounded-2xl border border-amber-200/60 bg-amber-50 p-3 text-xs font-medium text-amber-800"
                            >
                                <Sparkles
                                    class="h-4 w-4 shrink-0 text-amber-600"
                                />
                                <span
                                    >Perubahan stok akan langsung terhubung
                                    secara live ke storefront publik.</span
                                >
                            </div>
                        </CardContent>

                        <CardFooter class="flex justify-end gap-2 p-6 pt-0">
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-10 rounded-xl px-4 text-xs font-bold"
                                :disabled="stockLoading"
                                @click="stockTarget = null"
                            >
                                Batal
                            </Button>
                            <Button
                                variant="amber"
                                size="sm"
                                class="h-10 rounded-xl px-5 text-xs font-extrabold"
                                :disabled="stockLoading"
                                @click="saveStock"
                            >
                                <Check
                                    v-if="!stockLoading"
                                    class="mr-1.5 h-4 w-4"
                                />
                                {{
                                    stockLoading
                                        ? 'Menyimpan...'
                                        : 'Simpan Perubahan Stok'
                                }}
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
