<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
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
    Tag as TagIcon,
    Package,
    Palette,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Input } from '@/components/ui/input';
import { useCatalogManager } from '@/composables/useCatalogManager';
import AppLayout from '@/layouts/AppLayout.vue';
import type { CatalogItem } from '@/types/catalog';
import type { CatalogLabelItem } from '@/types/catalog';

interface Props {
    categories?: CatalogItem[];
    brands?: CatalogItem[];
    labels?: CatalogLabelItem[];
}

const props = withDefaults(defineProps<Props>(), {
    categories: () => [],
    brands: () => [],
    labels: () => [],
});

const itemsRef = computed(() => ({
    categories: props.categories,
    brands: props.brands,
    labels: props.labels,
}));

const {
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
    colorPresets,
    saveCategory,
    saveBrand,
    saveLabel,
    startEdit,
    commitEdit,
    confirmDelete,
    isEditing,
} = useCatalogManager(itemsRef);
</script>

<template>
    <Head title="Manajemen Katalog & Taksonomi — Dashboard Merchant" />

    <AppLayout title="Katalog Produk" activePage="Katalog">
        <div
            class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- ── Header Banner Hub ── -->
            <div
                class="relative overflow-hidden rounded-3xl bg-primary p-6 text-primary-foreground shadow-xl sm:p-8"
            >
                <div
                    class="absolute -top-10 -right-10 h-64 w-64 rounded-full bg-gradient-to-br from-accent/40 via-accent/20 to-transparent blur-3xl"
                />
                <div
                    class="absolute -bottom-10 -left-10 h-64 w-64 rounded-full bg-gradient-to-br from-violet-600/30 via-indigo-500/10 to-transparent blur-3xl"
                />

                <div
                    class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-accent/20 bg-accent/10 px-3 py-1 text-xs font-black text-accent"
                            >
                                <Sparkles class="h-3.5 w-3.5" /> SMART TAXONOMY
                                ENGINE
                            </span>
                        </div>
                        <h1
                            class="text-2xl font-black tracking-tight text-primary-foreground sm:text-3xl"
                        >
                            Pusat Pengelolaan Katalog & Label
                        </h1>
                        <p class="max-w-2xl text-xs text-muted-foreground sm:text-sm">
                            Organisir kategori produk, brand mitra, dan label
                            penawaran khusus toko Anda dengan mudah untuk
                            meningkatkan konversi penjualan.
                        </p>
                    </div>

                    <!-- Quick Stats Overview -->
                    <div class="grid shrink-0 grid-cols-3 gap-3">
                        <div
                            class="flex flex-col items-center justify-center rounded-2xl border border-white/10 bg-background/5 p-3 backdrop-blur-md"
                        >
                            <span class="text-2xl font-black text-accent">{{
                                (categories ?? []).length
                            }}</span>
                            <span
                                class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                                >Kategori</span
                            >
                        </div>
                        <div
                            class="flex flex-col items-center justify-center rounded-2xl border border-white/10 bg-background/5 p-3 backdrop-blur-md"
                        >
                            <span class="text-2xl font-black text-primary">{{
                                (brands ?? []).length
                            }}</span>
                            <span
                                class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                                >Brand</span
                            >
                        </div>
                        <div
                            class="flex flex-col items-center justify-center rounded-2xl border border-white/10 bg-background/5 p-3 backdrop-blur-md"
                        >
                            <span
                                class="text-2xl font-black text-emerald-500"
                                >{{ (labels ?? []).length }}</span
                            >
                            <span
                                class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                                >Label / Tag</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Search & Filter Navigation Bar ── -->
            <div
                class="flex flex-col items-center justify-between gap-4 sm:flex-row"
            >
                <!-- Navigation Tabs -->
                <div
                    class="flex w-full items-center gap-1.5 overflow-x-auto rounded-2xl border border-border bg-muted p-1 sm:w-auto"
                >
                    <button
                        @click="activeTab = 'all'"
                        class="flex cursor-pointer items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            activeTab === 'all'
                                ? 'border border-border bg-background text-foreground shadow-xs'
                                : 'text-[#9090a0] hover:text-foreground'
                        "
                    >
                        <LayoutGrid class="h-3.5 w-3.5" /> Ringkasan Semua
                    </button>
                    <button
                        @click="activeTab = 'category'"
                        class="flex cursor-pointer items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            activeTab === 'category'
                                ? 'border border-border bg-background text-foreground shadow-xs'
                                : 'text-[#9090a0] hover:text-foreground'
                        "
                    >
                        <FolderPlus class="h-3.5 w-3.5 text-accent" />
                        Kategori ({{ (categories ?? []).length }})
                    </button>
                    <button
                        @click="activeTab = 'brand'"
                        class="flex cursor-pointer items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            activeTab === 'brand'
                                ? 'border border-border bg-background text-foreground shadow-xs'
                                : 'text-[#9090a0] hover:text-foreground'
                        "
                    >
                        <Award class="h-3.5 w-3.5 text-primary" /> Brand ({{
                            (brands ?? []).length
                        }})
                    </button>
                    <button
                        @click="activeTab = 'label'"
                        class="flex cursor-pointer items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            activeTab === 'label'
                                ? 'border border-border bg-background text-foreground shadow-xs'
                                : 'text-[#9090a0] hover:text-foreground'
                        "
                    >
                        <TagIcon class="h-3.5 w-3.5 text-emerald-500" /> Label
                        Promo ({{ (labels ?? []).length }})
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari taksonomi..."
                        class="h-10 rounded-2xl border-black/10 bg-background pl-9 text-xs"
                    />
                </div>
            </div>

            <!-- ── SECTION 1: KATEGORI PRODUK ── -->
            <div
                v-if="activeTab === 'all' || activeTab === 'category'"
                class="flex flex-col gap-4"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-accent/10 font-bold text-accent"
                        >
                            <FolderPlus class="h-4 w-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-foreground">
                                Kategori Produk
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Kelompokkan jenis barang dagangan Anda
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fast Add Category Input Card -->
                <Card class="rounded-2xl border-border bg-background p-4 shadow-xs">
                    <form
                        @submit.prevent="saveCategory"
                        class="flex flex-col items-center gap-3 sm:flex-row"
                    >
                        <div class="relative w-full flex-1">
                            <Input
                                v-model="newCategory"
                                placeholder="Tuliskan nama kategori baru (contoh: Running Shoes, Running Accessories...)"
                                class="h-10 rounded-xl text-xs"
                            />
                        </div>
                        <Button
                            type="submit"
                            variant="default"
                            class="h-10 w-full shrink-0 rounded-xl px-5 text-xs font-bold sm:w-auto"
                        >
                            <Plus class="mr-1 h-4 w-4" /> Tambah Kategori
                        </Button>
                    </form>
                </Card>

                <!-- Categories Grid Cards -->
                <div
                    class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="c in filteredCategories"
                        :key="c.id"
                        class="group relative flex flex-col justify-between gap-3 overflow-hidden rounded-2xl border border-border bg-background p-4 transition-all duration-200 hover:border-accent/50 hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <template v-if="isEditing('category', c.id)">
                                <div class="flex w-full items-center gap-1.5">
                                    <Input
                                        v-model="editing!.name"
                                        class="h-8 text-xs font-bold"
                                        @keyup.enter="commitEdit"
                                        autofocus
                                    />
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 text-emerald-600"
                                        @click="commitEdit"
                                    >
                                        <Check class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 text-muted-foreground"
                                        @click="editing = null"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-accent/20 bg-accent/10 text-sm font-black text-accent shadow-xs"
                                    >
                                        {{
                                            c.name.substring(0, 2).toUpperCase()
                                        }}
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-black text-foreground transition-colors group-hover:text-accent"
                                        >
                                            {{ c.name }}
                                        </h4>
                                        <span
                                            class="font-mono text-[10px] font-semibold text-muted-foreground"
                                        >
                                            slug:
                                            {{
                                                c.name
                                                    .toLowerCase()
                                                    .replace(/\s+/g, '-')
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center gap-1 opacity-80 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="startEdit('category', c)"
                                        class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-accent/10 hover:text-accent"
                                        title="Edit Kategori"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="
                                            deleteTarget = {
                                                kind: 'category',
                                                item: c,
                                            }
                                        "
                                        class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                        title="Hapus Kategori"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-border pt-2 text-[11px] font-medium text-muted-foreground"
                        >
                            <span
                                class="flex items-center gap-1 font-bold text-muted-foreground"
                            >
                                <Package class="h-3.5 w-3.5 text-muted-foreground" />
                                {{ c.product_count ?? 12 }} Produk Terkait
                            </span>
                            <Badge variant="teal" class="px-2 py-0 text-[9px]"
                                >Aktif Catalog</Badge
                            >
                        </div>
                    </div>
                </div>

                <div
                    v-if="!filteredCategories.length"
                    class="rounded-2xl border border-dashed border-black/10 bg-background p-8 text-center"
                >
                    <FolderPlus class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
                    <p class="text-xs font-bold text-muted-foreground">
                        Belum ada kategori yang ditemukan.
                    </p>
                </div>
            </div>

            <!-- ── SECTION 2: BRAND MITRA & LISENSI ── -->
            <div
                v-if="activeTab === 'all' || activeTab === 'brand'"
                class="mt-2 flex flex-col gap-4"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-primary/10 font-bold text-primary"
                        >
                            <Award class="h-4 w-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-foreground">
                                Brand & Merk Lisensi
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Merek resmi produsen barang dagangan
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fast Add Brand Input Card -->
                <Card class="rounded-2xl border-border bg-background p-4 shadow-xs">
                    <form
                        @submit.prevent="saveBrand"
                        class="flex flex-col items-center gap-3 sm:flex-row"
                    >
                        <div class="relative w-full flex-1">
                            <Input
                                v-model="newBrand"
                                placeholder="Tuliskan nama brand (contoh: Nike, Jordan, Puma, Adidas...)"
                                class="h-10 rounded-xl text-xs"
                            />
                        </div>
                        <Button
                            type="submit"
                            class="h-10 w-full shrink-0 rounded-xl bg-primary px-5 text-xs font-bold text-primary-foreground hover:bg-primary/90 sm:w-auto"
                        >
                            <Plus class="mr-1 h-4 w-4" /> Tambah Brand
                        </Button>
                    </form>
                </Card>

                <!-- Brand Cards Grid -->
                <div
                    class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="b in filteredBrands"
                        :key="b.id"
                        class="group relative flex flex-col justify-between gap-3 overflow-hidden rounded-2xl border border-border bg-background p-4 transition-all duration-200 hover:border-primary/50 hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <template v-if="isEditing('brand', b.id)">
                                <div class="flex w-full items-center gap-1.5">
                                    <Input
                                        v-model="editing!.name"
                                        class="h-8 text-xs font-bold"
                                        @keyup.enter="commitEdit"
                                        autofocus
                                    />
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 text-emerald-600"
                                        @click="commitEdit"
                                    >
                                        <Check class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 text-muted-foreground"
                                        @click="editing = null"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-primary/20 bg-primary/10 text-sm font-black text-primary shadow-xs"
                                    >
                                        <Award class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-black text-foreground transition-colors group-hover:text-primary"
                                        >
                                            {{ b.name }}
                                        </h4>
                                        <span
                                            class="text-[10px] font-semibold text-muted-foreground"
                                            >Official Brand License</span
                                        >
                                    </div>
                                </div>

                                <div
                                    class="flex items-center gap-1 opacity-80 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="startEdit('brand', b)"
                                        class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                                        title="Edit Brand"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="
                                            deleteTarget = {
                                                kind: 'brand',
                                                item: b,
                                            }
                                        "
                                        class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                        title="Hapus Brand"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-border pt-2 text-[11px]"
                        >
                            <Badge
                                variant="violetSolid"
                                class="px-2.5 py-0.5 text-[9px]"
                            >
                                Verified Partner
                            </Badge>
                            <span class="font-mono text-[10px] text-muted-foreground"
                                >ID: #BRD-0{{ b.id }}</span
                            >
                        </div>
                    </div>
                </div>

                <div
                    v-if="!filteredBrands.length"
                    class="rounded-2xl border border-dashed border-black/10 bg-background p-8 text-center"
                >
                    <Award class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
                    <p class="text-xs font-bold text-muted-foreground">
                        Belum ada brand yang terdaftar.
                    </p>
                </div>
            </div>

            <!-- ── SECTION 3: LABEL & TAG PROMO ── -->
            <div
                v-if="activeTab === 'all' || activeTab === 'label'"
                class="mt-2 flex flex-col gap-4"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 font-bold text-emerald-600"
                        >
                            <TagIcon class="h-4 w-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-black text-foreground">
                                Label & Tag Promosi
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Lencana promo visual pada kartu produk
                                storefront
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Advanced Add Label Input Card with Live Badge Preview & Palette -->
                <Card class="rounded-2xl border-border bg-background p-5 shadow-xs">
                    <form
                        @submit.prevent="saveLabel"
                        class="flex flex-col gap-4"
                    >
                        <div
                            class="flex flex-col items-center gap-3 sm:flex-row"
                        >
                            <div class="relative w-full flex-1">
                                <Input
                                    v-model="newLabel"
                                    placeholder="Tuliskan nama label promo (contoh: BEST SELLER, PROMO 8.8, GARANSI 100%...)"
                                    class="h-10 rounded-xl text-xs"
                                />
                            </div>

                            <!-- Color Palette Selector -->
                            <div
                                class="flex shrink-0 items-center gap-2 rounded-xl border border-black/10 bg-muted px-3 py-1.5"
                            >
                                <Palette class="h-4 w-4 text-muted-foreground" />
                                <div class="flex items-center gap-1.5">
                                    <button
                                        v-for="color in colorPresets"
                                        :key="color"
                                        type="button"
                                        @click="newLabelColor = color"
                                        class="h-5 w-5 cursor-pointer rounded-full border border-black/20 transition-transform duration-150"
                                        :class="
                                            newLabelColor === color
                                                ? 'scale-125 ring-2 ring-black/20 ring-offset-1'
                                                : 'hover:scale-110'
                                        "
                                        :style="{ backgroundColor: color }"
                                    />
                                </div>
                                <input
                                    v-model="newLabelColor"
                                    type="color"
                                    class="ml-1 h-6 w-6 cursor-pointer rounded border-0 bg-transparent p-0"
                                    title="Pilih Warna Custom"
                                />
                            </div>

                            <Button
                                type="submit"
                                variant="default"
                                class="h-10 w-full shrink-0 rounded-xl px-5 text-xs font-bold sm:w-auto"
                            >
                                <Plus class="mr-1 h-4 w-4" /> Tambah Label Promo
                            </Button>
                        </div>

                        <!-- Live Badge Preview Banner -->
                        <div
                            v-if="newLabel"
                            class="flex items-center gap-3 rounded-xl bg-primary p-3 text-xs text-primary-foreground"
                        >
                            <span
                                class="shrink-0 text-[11px] font-bold text-muted-foreground"
                                >Live Preview Storefront:</span
                            >
                            <span
                                class="rounded-full border px-3 py-1 text-[11px] font-black tracking-wider uppercase shadow-xs"
                                :style="{
                                    backgroundColor: `${newLabelColor}22`,
                                    color: newLabelColor,
                                    borderColor: `${newLabelColor}50`,
                                }"
                            >
                                {{ newLabel }}
                            </span>
                        </div>
                    </form>
                </Card>

                <!-- Labels Grid Cards -->
                <div
                    class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="l in filteredLabels"
                        :key="l.id"
                        class="group relative flex flex-col justify-between gap-3 overflow-hidden rounded-2xl border border-border bg-background p-4 transition-all duration-200 hover:shadow-md"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <template v-if="isEditing('label', l.id)">
                                <div class="flex w-full items-center gap-1.5">
                                    <Input
                                        v-model="editing!.name"
                                        class="h-8 flex-1 text-xs font-bold"
                                        @keyup.enter="commitEdit"
                                        autofocus
                                    />
                                    <input
                                        v-model="editing!.color"
                                        type="color"
                                        class="h-7 w-7 shrink-0 cursor-pointer rounded border border-black/20 bg-transparent p-0"
                                    />
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 text-emerald-600"
                                        @click="commitEdit"
                                    >
                                        <Check class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 text-muted-foreground"
                                        @click="editing = null"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="rounded-xl border px-3 py-1 text-xs font-black tracking-wider uppercase shadow-2xs"
                                        :style="
                                            l.color
                                                ? {
                                                      backgroundColor: `${l.color}1a`,
                                                      color: l.color,
                                                      borderColor: `${l.color}40`,
                                                  }
                                                : undefined
                                        "
                                    >
                                        {{ l.name }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center gap-1 opacity-80 transition-opacity group-hover:opacity-100"
                                >
                                    <button
                                        @click="startEdit('label', l)"
                                        class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-emerald-500/10 hover:text-emerald-600"
                                        title="Edit Label"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="
                                            deleteTarget = {
                                                kind: 'label',
                                                item: l,
                                            }
                                        "
                                        class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                        title="Hapus Label"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-border pt-2 text-[10px] text-muted-foreground"
                        >
                            <span class="font-mono"
                                >HEX: {{ l.color || '#e07c28' }}</span
                            >
                            <span class="font-bold text-muted-foreground"
                                >Siap Dipakai di Produk</span
                            >
                        </div>
                    </div>
                </div>

                <div
                    v-if="!filteredLabels.length"
                    class="rounded-2xl border border-dashed border-black/10 bg-background p-8 text-center"
                >
                    <TagIcon class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
                    <p class="text-xs font-bold text-muted-foreground">
                        Belum ada label promo yang dibuat.
                    </p>
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
