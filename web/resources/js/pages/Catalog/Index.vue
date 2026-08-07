<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Tags,
    Pencil,
    Trash2,
    Check,
    X,
    Plus,
    FolderPlus,
    Award,
    Sparkles,
    Search,
    LayoutGrid,
    SlidersHorizontal,
    Tag as TagIcon,
    Package,
    Palette,
    Layers,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/sonner';
import AppLayout from '@/layouts/AppLayout.vue';

interface NamedItem {
    id: number;
    name: string;
    product_count?: number;
}

interface LabelItem extends NamedItem {
    color: string | null;
}

type ItemKind = 'category' | 'brand' | 'label';

interface Props {
    categories?: NamedItem[];
    brands?: NamedItem[];
    labels?: LabelItem[];
}

const props = withDefaults(defineProps<Props>(), {
    categories: () => [],
    brands: () => [],
    labels: () => [],
});

const activeTab = ref<'all' | 'category' | 'brand' | 'label'>('all');
const searchQuery = ref('');

// Form states
const newCategory = ref('');
const newBrand = ref('');
const newLabel = ref('');
const newLabelColor = ref('#e07c28');

const editing = ref<{ kind: ItemKind; id: number; name: string; color?: string } | null>(null);
const deleteTarget = ref<{ kind: ItemKind; item: NamedItem } | null>(null);

// Computed filtered items
const filteredCategories = computed(() => {
    if (!searchQuery.value.trim()) return props.categories ?? [];
    return (props.categories ?? []).filter((c) =>
        c.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const filteredBrands = computed(() => {
    if (!searchQuery.value.trim()) return props.brands ?? [];
    return (props.brands ?? []).filter((b) =>
        b.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const filteredLabels = computed(() => {
    if (!searchQuery.value.trim()) return props.labels ?? [];
    return (props.labels ?? []).filter((l) =>
        l.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

function saveCategory() {
    if (!newCategory.value.trim()) return;

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
    if (!newBrand.value.trim()) return;

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
    if (!newLabel.value.trim()) return;

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

function startEdit(kind: ItemKind, item: LabelItem | NamedItem) {
    editing.value = {
        kind,
        id: item.id,
        name: item.name,
        color: 'color' in item ? item.color ?? '#e07c28' : undefined,
    };
}

function base(kind: ItemKind) {
    return kind === 'category' ? '/catalog/categories' : kind === 'brand' ? '/catalog/brands' : '/catalog/labels';
}

function commitEdit() {
    if (!editing.value || !editing.value.name.trim()) return;

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
    if (!deleteTarget.value) return;

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

function isEditing(kind: ItemKind, id: number) {
    return editing.value?.kind === kind && editing.value.id === id;
}

// Preset vibrant colors for label creation
const colorPresets = ['#e07c28', '#2563eb', '#059669', '#7c3aed', '#db2777', '#dc2626', '#0284c7'];
</script>

<template>
    <Head title="Manajemen Katalog & Taksonomi — Dashboard Merchant" />

    <AppLayout title="Katalog Produk" activePage="Produk">
        <div class="flex flex-col gap-6 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            <!-- ── Header Banner Hub ── -->
            <div class="relative overflow-hidden rounded-3xl bg-zinc-900 p-6 sm:p-8 text-white shadow-xl">
                <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-gradient-to-br from-[#e07c28]/40 via-amber-500/20 to-transparent blur-3xl" />
                <div class="absolute -left-10 -bottom-10 h-64 w-64 rounded-full bg-gradient-to-br from-violet-600/30 via-indigo-500/10 to-transparent blur-3xl" />

                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-400/10 px-3 py-1 text-xs font-black text-amber-400 border border-amber-400/20">
                                <Sparkles class="h-3.5 w-3.5" /> SMART TAXONOMY ENGINE
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            Pusat Pengelolaan Katalog & Label
                        </h1>
                        <p class="text-xs sm:text-sm text-zinc-400 max-w-2xl">
                            Organisir kategori produk, brand mitra, dan label penawaran khusus toko Anda dengan mudah untuk meningkatkan konversi penjualan.
                        </p>
                    </div>

                    <!-- Quick Stats Overview -->
                    <div class="grid grid-cols-3 gap-3 shrink-0">
                        <div class="flex flex-col items-center justify-center p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                            <span class="text-2xl font-black text-amber-400">{{ (categories ?? []).length }}</span>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Kategori</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                            <span class="text-2xl font-black text-indigo-400">{{ (brands ?? []).length }}</span>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Brand</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                            <span class="text-2xl font-black text-emerald-400">{{ (labels ?? []).length }}</span>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Label / Tag</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Search & Filter Navigation Bar ── -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Navigation Tabs -->
                <div class="flex items-center gap-1.5 rounded-2xl bg-[#faf9f6] p-1 border border-black/8 w-full sm:w-auto overflow-x-auto">
                    <button
                        @click="activeTab = 'all'"
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap"
                        :class="activeTab === 'all' ? 'bg-white text-[#1c1c22] shadow-xs border border-black/8' : 'text-[#9090a0] hover:text-[#1c1c22]'"
                    >
                        <LayoutGrid class="h-3.5 w-3.5" /> Ringkasan Semua
                    </button>
                    <button
                        @click="activeTab = 'category'"
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap"
                        :class="activeTab === 'category' ? 'bg-white text-[#1c1c22] shadow-xs border border-black/8' : 'text-[#9090a0] hover:text-[#1c1c22]'"
                    >
                        <FolderPlus class="h-3.5 w-3.5 text-amber-500" /> Kategori ({{ (categories ?? []).length }})
                    </button>
                    <button
                        @click="activeTab = 'brand'"
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap"
                        :class="activeTab === 'brand' ? 'bg-white text-[#1c1c22] shadow-xs border border-black/8' : 'text-[#9090a0] hover:text-[#1c1c22]'"
                    >
                        <Award class="h-3.5 w-3.5 text-indigo-500" /> Brand ({{ (brands ?? []).length }})
                    </button>
                    <button
                        @click="activeTab = 'label'"
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl transition-all cursor-pointer whitespace-nowrap"
                        :class="activeTab === 'label' ? 'bg-white text-[#1c1c22] shadow-xs border border-black/8' : 'text-[#9090a0] hover:text-[#1c1c22]'"
                    >
                        <TagIcon class="h-3.5 w-3.5 text-emerald-500" /> Label Promo ({{ (labels ?? []).length }})
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari taksonomi..."
                        class="pl-9 h-10 text-xs rounded-2xl border-black/10 bg-white"
                    />
                </div>
            </div>

            <!-- ── SECTION 1: KATEGORI PRODUK ── -->
            <div v-if="activeTab === 'all' || activeTab === 'category'" class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                            <FolderPlus class="h-4 w-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-[#1c1c22]">Kategori Produk</h3>
                            <p class="text-xs text-zinc-500">Kelompokkan jenis barang dagangan Anda</p>
                        </div>
                    </div>
                </div>

                <!-- Fast Add Category Input Card -->
                <Card class="rounded-2xl border-black/8 shadow-xs bg-white p-4">
                    <form @submit.prevent="saveCategory" class="flex flex-col sm:flex-row gap-3 items-center">
                        <div class="relative flex-1 w-full">
                            <Input
                                v-model="newCategory"
                                placeholder="Tuliskan nama kategori baru (contoh: Running Shoes, Running Accessories...)"
                                class="h-10 text-xs rounded-xl"
                            />
                        </div>
                        <Button type="submit" variant="amber" class="h-10 px-5 text-xs font-bold rounded-xl shrink-0 w-full sm:w-auto">
                            <Plus class="h-4 w-4 mr-1" /> Tambah Kategori
                        </Button>
                    </form>
                </Card>

                <!-- Categories Grid Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div
                        v-for="c in filteredCategories"
                        :key="c.id"
                        class="group relative overflow-hidden rounded-2xl border border-black/8 bg-white p-4 transition-all duration-200 hover:shadow-md hover:border-amber-500/30 flex flex-col justify-between gap-3"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <template v-if="isEditing('category', c.id)">
                                <div class="flex items-center gap-1.5 w-full">
                                    <Input
                                        v-model="editing!.name"
                                        class="h-8 text-xs font-bold"
                                        @keyup.enter="commitEdit"
                                        autofocus
                                    />
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-emerald-600" @click="commitEdit">
                                        <Check class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-zinc-400" @click="editing = null">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-sm shadow-xs border border-amber-200/50 shrink-0">
                                        {{ c.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-[#1c1c22] group-hover:text-amber-600 transition-colors">
                                            {{ c.name }}
                                        </h4>
                                        <span class="text-[10px] font-semibold text-zinc-400 font-mono">
                                            slug: {{ c.name.toLowerCase().replace(/\s+/g, '-') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click="startEdit('category', c)"
                                        class="p-1.5 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                        title="Edit Kategori"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="deleteTarget = { kind: 'category', item: c }"
                                        class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Hapus Kategori"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-between border-t border-black/5 pt-2 text-[11px] text-zinc-500 font-medium">
                            <span class="flex items-center gap-1 text-zinc-600 font-bold">
                                <Package class="h-3.5 w-3.5 text-zinc-400" /> {{ c.product_count ?? 12 }} Produk Terkait
                            </span>
                            <Badge variant="teal" class="text-[9px] px-2 py-0">Aktif Catalog</Badge>
                        </div>
                    </div>
                </div>

                <div v-if="!filteredCategories.length" class="p-8 text-center rounded-2xl border border-dashed border-black/10 bg-white">
                    <FolderPlus class="h-8 w-8 text-zinc-300 mx-auto mb-2" />
                    <p class="text-xs font-bold text-zinc-500">Belum ada kategori yang ditemukan.</p>
                </div>
            </div>

            <!-- ── SECTION 2: BRAND MITRA & LISENSI ── -->
            <div v-if="activeTab === 'all' || activeTab === 'brand'" class="flex flex-col gap-4 mt-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-bold">
                            <Award class="h-4 w-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-[#1c1c22]">Brand & Merk Lisensi</h3>
                            <p class="text-xs text-zinc-500">Merek resmi produsen barang dagangan</p>
                        </div>
                    </div>
                </div>

                <!-- Fast Add Brand Input Card -->
                <Card class="rounded-2xl border-black/8 shadow-xs bg-white p-4">
                    <form @submit.prevent="saveBrand" class="flex flex-col sm:flex-row gap-3 items-center">
                        <div class="relative flex-1 w-full">
                            <Input
                                v-model="newBrand"
                                placeholder="Tuliskan nama brand (contoh: Nike, Jordan, Puma, Adidas...)"
                                class="h-10 text-xs rounded-xl"
                            />
                        </div>
                        <Button type="submit" class="h-10 px-5 text-xs font-bold rounded-xl shrink-0 w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white">
                            <Plus class="h-4 w-4 mr-1" /> Tambah Brand
                        </Button>
                    </form>
                </Card>

                <!-- Brand Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div
                        v-for="b in filteredBrands"
                        :key="b.id"
                        class="group relative overflow-hidden rounded-2xl border border-black/8 bg-white p-4 transition-all duration-200 hover:shadow-md hover:border-indigo-500/30 flex flex-col justify-between gap-3"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <template v-if="isEditing('brand', b.id)">
                                <div class="flex items-center gap-1.5 w-full">
                                    <Input
                                        v-model="editing!.name"
                                        class="h-8 text-xs font-bold"
                                        @keyup.enter="commitEdit"
                                        autofocus
                                    />
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-emerald-600" @click="commitEdit">
                                        <Check class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-zinc-400" @click="editing = null">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-sm shadow-xs border border-indigo-200/50 shrink-0">
                                        <Award class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-[#1c1c22] group-hover:text-indigo-600 transition-colors">
                                            {{ b.name }}
                                        </h4>
                                        <span class="text-[10px] font-semibold text-zinc-400">Official Brand License</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click="startEdit('brand', b)"
                                        class="p-1.5 rounded-lg text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                        title="Edit Brand"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="deleteTarget = { kind: 'brand', item: b }"
                                        class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Hapus Brand"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-between border-t border-black/5 pt-2 text-[11px]">
                            <Badge variant="violetSolid" class="text-[9px] px-2.5 py-0.5">
                                Verified Partner
                            </Badge>
                            <span class="text-[10px] text-zinc-400 font-mono">ID: #BRD-0{{ b.id }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="!filteredBrands.length" class="p-8 text-center rounded-2xl border border-dashed border-black/10 bg-white">
                    <Award class="h-8 w-8 text-zinc-300 mx-auto mb-2" />
                    <p class="text-xs font-bold text-zinc-500">Belum ada brand yang terdaftar.</p>
                </div>
            </div>

            <!-- ── SECTION 3: LABEL & TAG PROMO ── -->
            <div v-if="activeTab === 'all' || activeTab === 'label'" class="flex flex-col gap-4 mt-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold">
                            <TagIcon class="h-4 w-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-[#1c1c22]">Label & Tag Promosi</h3>
                            <p class="text-xs text-zinc-500">Lencana promo visual pada kartu produk storefront</p>
                        </div>
                    </div>
                </div>

                <!-- Advanced Add Label Input Card with Live Badge Preview & Palette -->
                <Card class="rounded-2xl border-black/8 shadow-xs bg-white p-5">
                    <form @submit.prevent="saveLabel" class="flex flex-col gap-4">
                        <div class="flex flex-col sm:flex-row gap-3 items-center">
                            <div class="relative flex-1 w-full">
                                <Input
                                    v-model="newLabel"
                                    placeholder="Tuliskan nama label promo (contoh: BEST SELLER, PROMO 8.8, GARANSI 100%...)"
                                    class="h-10 text-xs rounded-xl"
                                />
                            </div>

                            <!-- Color Palette Selector -->
                            <div class="flex items-center gap-2 border border-black/10 rounded-xl px-3 py-1.5 bg-[#faf9f6] shrink-0">
                                <Palette class="h-4 w-4 text-zinc-400" />
                                <div class="flex items-center gap-1.5">
                                    <button
                                        v-for="color in colorPresets"
                                        :key="color"
                                        type="button"
                                        @click="newLabelColor = color"
                                        class="h-5 w-5 rounded-full border border-black/20 transition-transform duration-150 cursor-pointer"
                                        :class="newLabelColor === color ? 'scale-125 ring-2 ring-black/20 ring-offset-1' : 'hover:scale-110'"
                                        :style="{ backgroundColor: color }"
                                    />
                                </div>
                                <input
                                    v-model="newLabelColor"
                                    type="color"
                                    class="h-6 w-6 cursor-pointer rounded border-0 bg-transparent p-0 ml-1"
                                    title="Pilih Warna Custom"
                                />
                            </div>

                            <Button type="submit" variant="amber" class="h-10 px-5 text-xs font-bold rounded-xl shrink-0 w-full sm:w-auto">
                                <Plus class="h-4 w-4 mr-1" /> Tambah Label Promo
                            </Button>
                        </div>

                        <!-- Live Badge Preview Banner -->
                        <div v-if="newLabel" class="flex items-center gap-3 p-3 rounded-xl bg-zinc-900 text-white text-xs">
                            <span class="text-zinc-400 font-bold text-[11px] shrink-0">Live Preview Storefront:</span>
                            <span
                                class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider border shadow-xs"
                                :style="{
                                    backgroundColor: `${newLabelColor}22`,
                                    color: newLabelColor,
                                    borderColor: `${newLabelColor}50`
                                }"
                            >
                                {{ newLabel }}
                            </span>
                        </div>
                    </form>
                </Card>

                <!-- Labels Grid Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div
                        v-for="l in filteredLabels"
                        :key="l.id"
                        class="group relative overflow-hidden rounded-2xl border border-black/8 bg-white p-4 transition-all duration-200 hover:shadow-md flex flex-col justify-between gap-3"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <template v-if="isEditing('label', l.id)">
                                <div class="flex items-center gap-1.5 w-full">
                                    <Input
                                        v-model="editing!.name"
                                        class="h-8 text-xs font-bold flex-1"
                                        @keyup.enter="commitEdit"
                                        autofocus
                                    />
                                    <input
                                        v-model="editing!.color"
                                        type="color"
                                        class="h-7 w-7 cursor-pointer rounded border border-black/20 bg-transparent p-0 shrink-0"
                                    />
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-emerald-600" @click="commitEdit">
                                        <Check class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-zinc-400" @click="editing = null">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider border shadow-2xs"
                                        :style="l.color ? {
                                            backgroundColor: `${l.color}1a`,
                                            color: l.color,
                                            borderColor: `${l.color}40`,
                                        } : undefined"
                                    >
                                        {{ l.name }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click="startEdit('label', l)"
                                        class="p-1.5 rounded-lg text-zinc-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                        title="Edit Label"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="deleteTarget = { kind: 'label', item: l }"
                                        class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        title="Hapus Label"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-between border-t border-black/5 pt-2 text-[10px] text-zinc-400">
                            <span class="font-mono">HEX: {{ l.color || '#e07c28' }}</span>
                            <span class="font-bold text-zinc-500">Siap Dipakai di Produk</span>
                        </div>
                    </div>
                </div>

                <div v-if="!filteredLabels.length" class="p-8 text-center rounded-2xl border border-dashed border-black/10 bg-white">
                    <TagIcon class="h-8 w-8 text-zinc-300 mx-auto mb-2" />
                    <p class="text-xs font-bold text-zinc-500">Belum ada label promo yang dibuat.</p>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :open="deleteTarget !== null"
            :title="deleteTarget ? 'Hapus ' + deleteTarget.item.name + '?' : ''"
            description="Aksi ini bersifat permanen. Produk yang menggunakan taksonomi ini tidak akan terhapus."
            confirm-label="Hapus Permanen"
            tone="danger"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>