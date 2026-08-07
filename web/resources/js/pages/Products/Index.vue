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
    TrendingUp,
    Sparkles,
    SlidersHorizontal,
    ExternalLink,
    Tag,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
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
const activeProductsCount = computed(() => (props.products ?? []).filter((p) => p.is_active).length);
const lowStockCount = computed(() => (props.products ?? []).filter((p) => p.stock <= 5 && p.is_active).length);
const totalInventoryValuation = computed(() => {
    return (props.products ?? []).reduce((acc, p) => acc + p.price * p.stock, 0);
});

function formatRupiah(val: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
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

const totalPages = computed(() => Math.ceil(filtered.value.length / perPage.value) || 1);

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filtered.value.slice(start, start + perPage.value);
});

const paginationStart = computed(() => {
    if (filtered.value.length === 0) return 0;
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
    if (!stockTarget.value) return;

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
        <div class="flex flex-col gap-6 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            <!-- ── Header Banner Dashboard Hub ── -->
            <div class="relative overflow-hidden rounded-3xl bg-zinc-900 p-6 sm:p-8 text-white shadow-xl">
                <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-gradient-to-br from-[#e07c28]/40 via-amber-500/20 to-transparent blur-3xl" />
                <div class="absolute -left-10 -bottom-10 h-64 w-64 rounded-full bg-gradient-to-br from-emerald-600/30 via-teal-500/10 to-transparent blur-3xl" />

                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-400/10 px-3 py-1 text-xs font-black text-amber-400 border border-amber-400/20">
                                <Sparkles class="h-3.5 w-3.5" /> SMART INVENTORY CONTROL
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            Katalog & Manajemen Produk Toko
                        </h1>
                        <p class="text-xs sm:text-sm text-zinc-400 max-w-2xl">
                            Kelola stok persediaan barang, varian SKU, harga penawaran, dan status publikasi produk secara terpusat.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto">
                        <Button variant="amber" size="lg" class="w-full sm:w-auto px-6 text-xs font-extrabold rounded-2xl shadow-lg shadow-amber-500/25 cursor-pointer" @click="goToCreate">
                            <Plus class="mr-2 h-4 w-4" /> Tambah Produk Baru
                        </Button>
                    </div>
                </div>

                <!-- Metrics Overview Cards (Mobile Responsive) -->
                <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 mt-6 pt-6 border-t border-white/10">
                    <div class="flex flex-col gap-1 p-2.5 sm:p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md min-w-0">
                        <span class="text-[9px] sm:text-[10px] font-extrabold text-zinc-400 uppercase tracking-wider truncate">Total Produk</span>
                        <span class="text-sm sm:text-2xl font-black text-white truncate">{{ totalProductsCount }} Items</span>
                    </div>

                    <div class="flex flex-col gap-1 p-2.5 sm:p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md min-w-0">
                        <span class="text-[9px] sm:text-[10px] font-extrabold text-zinc-400 uppercase tracking-wider truncate">Produk Aktif</span>
                        <span class="text-sm sm:text-2xl font-black text-emerald-400 truncate">{{ activeProductsCount }} Live</span>
                    </div>

                    <div class="flex flex-col gap-1 p-2.5 sm:p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md min-w-0">
                        <span class="text-[9px] sm:text-[10px] font-extrabold text-zinc-400 uppercase tracking-wider truncate">Stok Menipis</span>
                        <span class="text-xs sm:text-2xl font-black text-amber-400 flex items-center gap-1 truncate">
                            <AlertTriangle class="h-4 w-4 text-amber-400 shrink-0" v-if="lowStockCount > 0" />
                            <span class="truncate">{{ lowStockCount }} Restock</span>
                        </span>
                    </div>

                    <div class="flex flex-col gap-1 p-2.5 sm:p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md min-w-0">
                        <span class="text-[9px] sm:text-[10px] font-extrabold text-zinc-400 uppercase tracking-wider truncate">Estimasi Inventaris</span>
                        <span class="text-xs sm:text-xl font-black text-amber-300 truncate font-mono" :title="formatRupiah(totalInventoryValuation)">{{ formatRupiah(totalInventoryValuation) }}</span>
                    </div>
                </div>
            </div>

            <!-- ── Search & Filter Control Bar ── -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                    <div class="relative w-full sm:w-80">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                        <Input
                            v-model="searchQ"
                            placeholder="Cari nama produk, SKU, atau kategori..."
                            class="pl-10 h-10 text-xs rounded-2xl border-black/10 bg-white"
                        />
                    </div>

                    <!-- Filter Status Buttons -->
                    <div class="flex items-center gap-1 rounded-2xl bg-[#faf9f6] p-1 border border-black/8 w-full sm:w-auto overflow-x-auto">
                        <button
                            @click="statusFilter = 'all'"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap"
                            :class="statusFilter === 'all' ? 'bg-white text-[#1c1c22] shadow-xs border border-black/8' : 'text-[#9090a0] hover:text-[#1c1c22]'"
                        >
                            Semua ({{ totalProductsCount }})
                        </button>
                        <button
                            @click="statusFilter = 'active'"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap"
                            :class="statusFilter === 'active' ? 'bg-white text-[#1c1c22] shadow-xs border border-black/8' : 'text-[#9090a0] hover:text-[#1c1c22]'"
                        >
                            Aktif ({{ activeProductsCount }})
                        </button>
                        <button
                            @click="statusFilter = 'low_stock'"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap"
                            :class="statusFilter === 'low_stock' ? 'bg-white text-amber-600 shadow-xs border border-black/8' : 'text-[#9090a0] hover:text-[#1c1c22]'"
                        >
                            Restock ({{ lowStockCount }})
                        </button>
                    </div>
                </div>

                <!-- View Switcher Toggle -->
                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                    <div class="flex items-center gap-1 rounded-2xl bg-[#faf9f6] p-1 border border-black/8">
                        <button
                            @click="viewMode = 'grid'"
                            class="p-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                            :class="viewMode === 'grid' ? 'bg-white text-black shadow-xs' : 'text-zinc-400 hover:text-black'"
                            title="Tampilan Kartu Grid"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                        <button
                            @click="viewMode = 'table'"
                            class="p-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                            :class="viewMode === 'table' ? 'bg-white text-black shadow-xs' : 'text-zinc-400 hover:text-black'"
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
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
            >
                <Card
                    v-for="p in paginatedProducts"
                    :key="p.id"
                    class="group relative overflow-hidden rounded-3xl border border-black/8 bg-white transition-all duration-300 hover:shadow-xl hover:border-amber-500/30 flex flex-col justify-between"
                    :class="{ 'opacity-65 grayscale-20': !p.is_active }"
                >
                    <div>
                        <!-- Product Image Banner -->
                        <div class="relative aspect-4/3 w-full bg-[#faf9f6] overflow-hidden">
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
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                <Badge v-if="p.tag" variant="amber" class="text-[10px] font-black uppercase shadow-xs px-2.5 py-0.5">
                                    {{ p.tag }}
                                </Badge>
                                <Badge v-if="!p.is_active" variant="rose" class="text-[10px] font-black uppercase shadow-xs px-2.5 py-0.5">
                                    Nonaktif (Draft)
                                </Badge>
                            </div>

                            <!-- Interactive Stock badge top-right -->
                            <button
                                @click="openStockDialog(p)"
                                class="absolute top-3 right-3 cursor-pointer rounded-2xl px-3 py-1 text-[11px] font-extrabold shadow-md backdrop-blur-md transition-all hover:scale-105 flex items-center gap-1.5"
                                :class="
                                    !p.is_active
                                        ? 'bg-white/90 text-zinc-600'
                                        : lowStock(p.stock)
                                          ? 'bg-rose-500 text-white animate-pulse'
                                          : 'bg-emerald-600 text-white'
                                "
                                :title="'Klik untuk ubah stok persediaan ' + p.name"
                            >
                                <BoxIcon class="h-3.5 w-3.5" />
                                Stok: {{ p.stock }} pcs
                            </button>

                            <!-- Bottom Image Gradient Overlay for Price -->
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent p-4 flex items-end justify-between text-white">
                                <div>
                                    <p class="text-[10px] font-bold text-amber-300 font-mono">SKU: {{ p.sku || ('NK-00' + p.id) }}</p>
                                    <p class="font-mono text-lg font-black text-white">
                                        {{ p.formatted_price }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1 text-xs font-black text-amber-400 bg-black/40 px-2 py-1 rounded-xl backdrop-blur-xs">
                                    <Star class="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
                                    {{ p.rating ? p.rating.toFixed(1) : '4.8' }}
                                </div>
                            </div>
                        </div>

                        <!-- Card Header & Content -->
                        <div class="p-5 flex flex-col gap-2">
                            <div class="flex items-center justify-between gap-2">
                                <Badge variant="outline" class="px-2.5 py-0.5 text-[10px] font-bold text-zinc-600 border-black/10">
                                    {{ p.category }}
                                </Badge>
                                <span class="text-[11px] font-extrabold text-zinc-400">
                                    {{ p.sold }} Terjual
                                </span>
                            </div>

                            <h3 class="line-clamp-2 text-sm font-extrabold text-[#1c1c22] group-hover:text-amber-600 transition-colors leading-snug">
                                {{ p.name }}
                            </h3>
                        </div>
                    </div>

                    <!-- Card Actions Bar -->
                    <div class="p-4 pt-0 flex flex-wrap items-center gap-2 border-t border-black/5 mt-2">
                        <Button
                            variant="outline"
                            size="sm"
                            class="flex-1 text-xs font-bold rounded-xl h-9"
                            @click="editProduct(p.id)"
                        >
                            <Pencil class="mr-1.5 h-3.5 w-3.5" /> Edit
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="flex-1 text-xs font-bold rounded-xl h-9"
                            @click="router.visit(`/products/${p.id}/variants`)"
                        >
                            <Layers class="mr-1.5 h-3.5 w-3.5 text-indigo-500" /> Varian
                        </Button>
                        <Button
                            :variant="p.is_active ? 'outline' : 'amber'"
                            size="sm"
                            class="h-9 px-3 rounded-xl text-xs font-bold"
                            @click="toggleActive(p)"
                            :title="p.is_active ? 'Sembunyikan produk' : 'Aktifkan produk'"
                        >
                            <PackageX v-if="p.is_active" class="h-3.5 w-3.5 text-rose-500" />
                            <PackagePlus v-else class="h-3.5 w-3.5" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-9 w-9 p-0 rounded-xl text-zinc-400 hover:bg-rose-50 hover:text-rose-600"
                            @click="deleteTarget = p"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </Card>
            </div>

            <!-- ── VIEW MODE 2: TABLE VIEW ── -->
            <Card v-else-if="filtered.length > 0 && viewMode === 'table'" class="rounded-3xl border-black/8 shadow-xs bg-white overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-black/8 bg-[#faf9f6] text-[11px] font-black uppercase text-zinc-500 tracking-wider">
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
                                class="hover:bg-amber-50/30 transition-colors"
                                :class="{ 'opacity-60': !p.is_active }"
                            >
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 rounded-xl bg-zinc-100 border border-black/10 overflow-hidden shrink-0">
                                            <img v-if="p.img" :src="p.img" class="h-full w-full object-cover" />
                                            <div v-else class="h-full w-full flex items-center justify-center text-zinc-300">
                                                <Package class="h-5 w-5" />
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-[#1c1c22] line-clamp-1 max-w-xs">{{ p.name }}</p>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <Badge v-if="p.tag" variant="amber" class="text-[9px] px-1.5 py-0 font-extrabold">{{ p.tag }}</Badge>
                                                <span class="text-[10px] text-amber-600 font-bold flex items-center gap-0.5">
                                                    <Star class="h-3 w-3 fill-amber-400" /> {{ p.rating ? p.rating.toFixed(1) : '4.8' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-extrabold text-zinc-700 block">{{ p.category }}</span>
                                    <span class="font-mono text-[10px] text-zinc-400">SKU: {{ p.sku || ('NK-00' + p.id) }}</span>
                                </td>
                                <td class="p-4 font-mono font-extrabold text-amber-600 text-sm">
                                    {{ p.formatted_price }}
                                </td>
                                <td class="p-4">
                                    <button
                                        @click="openStockDialog(p)"
                                        class="px-2.5 py-1 rounded-xl text-xs font-extrabold border cursor-pointer transition-transform hover:scale-105"
                                        :class="lowStock(p.stock) ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                                    >
                                        {{ p.stock }} pcs
                                    </button>
                                </td>
                                <td class="p-4 font-bold text-zinc-600">
                                    {{ p.sold }} unit
                                </td>
                                <td class="p-4">
                                    <Badge :variant="p.is_active ? 'teal' : 'rose'" class="text-[10px] font-black uppercase">
                                        {{ p.is_active ? 'Publik' : 'Draft' }}
                                    </Badge>
                                </td>
                                <td class="p-4 pr-6 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click="editProduct(p.id)" title="Edit Produk">
                                            <Pencil class="h-3.5 w-3.5 text-zinc-600" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click="router.visit(`/products/${p.id}/variants`)" title="Varian SKU">
                                            <Layers class="h-3.5 w-3.5 text-indigo-600" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-rose-500 hover:bg-rose-50" @click="deleteTarget = p" title="Hapus Produk">
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
            <div v-if="filtered.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-black/8">
                <!-- Status text & per page select -->
                <div class="flex items-center gap-3 text-xs text-zinc-500 font-semibold">
                    <span>Menampilkan <strong class="text-[#1c1c22] font-black">{{ paginationStart }} - {{ paginationEnd }}</strong> dari <strong class="text-[#1c1c22] font-black">{{ filtered.length }}</strong> produk</span>
                    <div class="flex items-center gap-1.5 border border-black/10 rounded-xl px-2.5 py-1 bg-white">
                        <span>Per Halaman:</span>
                        <select
                            v-model.number="perPage"
                            class="bg-transparent font-bold text-[#1c1c22] border-none focus:outline-none cursor-pointer text-xs"
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
                        class="h-9 px-3 text-xs font-bold rounded-xl cursor-pointer"
                        :disabled="currentPage === 1"
                        @click="goToPage(currentPage - 1)"
                    >
                        <ChevronLeft class="h-4 w-4 mr-1" /> Prev
                    </Button>

                    <div class="flex items-center gap-1 px-1">
                        <button
                            v-for="p in totalPages"
                            :key="p"
                            @click="goToPage(p)"
                            class="h-9 w-9 text-xs font-black rounded-xl transition-all cursor-pointer"
                            :class="currentPage === p ? 'bg-amber-500 text-white shadow-xs' : 'bg-white border border-black/8 text-zinc-600 hover:bg-zinc-100'"
                        >
                            {{ p }}
                        </button>
                    </div>

                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 px-3 text-xs font-bold rounded-xl cursor-pointer"
                        :disabled="currentPage === totalPages"
                        @click="goToPage(currentPage + 1)"
                    >
                        Next <ChevronRight class="h-4 w-4 ml-1" />
                    </Button>
                </div>
            </div>

            <!-- ── EMPTY STATE ── -->
            <Card v-else class="py-16 text-center shadow-xs rounded-3xl bg-white border-black/8">
                <CardContent class="flex flex-col items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-amber-50 text-amber-500">
                        <Package class="h-8 w-8" />
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-[#1c1c22]">Tidak Ada Produk Ditemukan</h3>
                        <p class="mt-1 text-xs text-zinc-400 max-w-sm">
                            Tidak ada produk yang sesuai dengan kriteria pencarian atau filter Anda.
                        </p>
                    </div>
                    <Button variant="amber" size="sm" class="mt-2 text-xs font-bold rounded-xl px-5" @click="goToCreate">
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
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                >
                    <div
                        class="absolute inset-0"
                        @click="stockTarget = null"
                    />
                    <Card class="relative z-10 w-full max-w-md rounded-3xl shadow-2xl bg-white border-black/10 overflow-hidden">
                        <CardHeader class="p-6 pb-4 bg-[#faf9f6] border-b border-black/5">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold shadow-xs">
                                    <BoxIcon class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-base font-black text-[#1c1c22]">Atur Stok Persediaan</CardTitle>
                                    <CardDescription class="truncate text-xs font-semibold text-zinc-500">
                                        {{ stockTarget?.name }}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="p-6 flex flex-col gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-bold text-[#1c1c22] flex items-center justify-between">
                                    <span>Jumlah Unit Stok Tersedia</span>
                                    <span class="text-[10px] text-zinc-400 font-mono">SKU: {{ stockTarget?.sku || 'NK-DEFAULT' }}</span>
                                </label>

                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="adjustStock(-10)"
                                        class="h-10 px-3 rounded-xl border border-black/10 text-xs font-black bg-zinc-100 hover:bg-zinc-200 cursor-pointer"
                                    >-10</button>
                                    <button
                                        type="button"
                                        @click="adjustStock(-1)"
                                        class="h-10 px-3 rounded-xl border border-black/10 text-xs font-black bg-zinc-100 hover:bg-zinc-200 cursor-pointer"
                                    >-1</button>
                                    <Input
                                        v-model="stockInput"
                                        type="number"
                                        min="0"
                                        class="h-10 text-center text-sm font-black font-mono rounded-xl flex-1"
                                    />
                                    <button
                                        type="button"
                                        @click="adjustStock(1)"
                                        class="h-10 px-3 rounded-xl border border-black/10 text-xs font-black bg-zinc-100 hover:bg-zinc-200 cursor-pointer"
                                    >+1</button>
                                    <button
                                        type="button"
                                        @click="adjustStock(10)"
                                        class="h-10 px-3 rounded-xl border border-black/10 text-xs font-black bg-zinc-100 hover:bg-zinc-200 cursor-pointer"
                                    >+10</button>
                                </div>
                            </div>

                            <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center gap-2 text-xs text-amber-800 font-medium">
                                <Sparkles class="h-4 w-4 text-amber-600 shrink-0" />
                                <span>Perubahan stok akan langsung terhubung secara live ke storefront publik.</span>
                            </div>
                        </CardContent>

                        <CardFooter class="flex justify-end gap-2 p-6 pt-0">
                            <Button
                                variant="outline"
                                size="sm"
                                class="text-xs font-bold rounded-xl h-10 px-4"
                                :disabled="stockLoading"
                                @click="stockTarget = null"
                            >
                                Batal
                            </Button>
                            <Button
                                variant="amber"
                                size="sm"
                                class="text-xs font-extrabold rounded-xl h-10 px-5"
                                :disabled="stockLoading"
                                @click="saveStock"
                            >
                                <Check v-if="!stockLoading" class="mr-1.5 h-4 w-4" />
                                {{ stockLoading ? 'Menyimpan...' : 'Simpan Perubahan Stok' }}
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
