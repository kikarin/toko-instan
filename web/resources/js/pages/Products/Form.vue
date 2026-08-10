<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ImagePlus,
    Loader2,
    Package,
    Save,
    Sparkles,
    Star,
    Info,
    CheckCircle2,
    DollarSign,
    Eye,
    UploadCloud,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { useProductForm } from '@/composables/useProductForm';
import type { Product } from '@/types/product';

interface Props {
    product?: Product;
    categories?: string[];
    labels?: string[];
    brands?: string[];
}

const props = defineProps<Props>();

const {
    isEdit,
    name,
    categoryOptions,
    labelOptions,
    brandOptions,
    selectedCategory,
    selectedTag,
    selectedBrand,
    price,
    stock,
    isActive,
    img,
    description,
    sku,
    weightGram,
    isLoading,
    errors,
    fileInput,
    formattedPricePreview,
    uploadImage,
    back,
    submit,
} = useProductForm(props);
</script>

<template>
    <Head
        :title="
            isEdit
                ? 'Edit Produk Studio — Dashboard Merchant'
                : 'Tambah Produk Baru — Dashboard Merchant'
        "
    />

    <AppLayout activePage="Produk">
        <div
            class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- ── Top Action Header ── -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 rounded-xl border-black/10 px-3 text-xs font-bold"
                        @click="back"
                    >
                        <ArrowLeft class="mr-1.5 h-4 w-4" /> Kembali
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-black text-amber-600"
                            >
                                <Sparkles class="h-3 w-3" /> PRODUCT STUDIO
                            </span>
                        </div>
                        <h1
                            class="text-xl font-black text-[#1c1c22] sm:text-2xl"
                        >
                            {{
                                isEdit
                                    ? 'Edit Rincian Produk'
                                    : 'Tambah Produk Baru ke Toko'
                            }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-10 rounded-xl px-4 text-xs font-bold"
                        @click="back"
                    >
                        Batal
                    </Button>
                    <Button
                        variant="amber"
                        size="sm"
                        class="h-10 cursor-pointer rounded-xl px-6 text-xs font-extrabold shadow-lg shadow-amber-500/20"
                        :disabled="isLoading"
                        @click="submit"
                    >
                        <Loader2
                            v-if="isLoading"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <Save v-else class="mr-2 h-4 w-4" />
                        {{
                            isEdit
                                ? 'Simpan Perubahan'
                                : 'Terbitkan Produk Baru'
                        }}
                    </Button>
                </div>
            </div>

            <!-- ── Main Studio Grid (Form vs Live Preview Side-by-Side) ── -->
            <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                <!-- ── LEFT COLUMN: FORM EDITORS (8 Cols) ── -->
                <form
                    @submit.prevent="submit"
                    class="flex flex-col gap-6 lg:col-span-8"
                >
                    <!-- SECTION 1: Informasi Dasar Produk -->
                    <Card
                        class="rounded-3xl border-black/8 bg-white p-6 shadow-xs"
                    >
                        <CardHeader class="mb-5 p-0">
                            <div
                                class="flex items-center gap-2 text-base font-black text-[#1c1c22]"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 font-bold text-amber-600"
                                >
                                    <Package class="h-4.5 w-4.5" />
                                </div>
                                <span>Informasi & Identitas Produk</span>
                            </div>
                        </CardHeader>

                        <CardContent class="flex flex-col gap-5 p-0">
                            <!-- Nama Produk -->
                            <div class="flex flex-col gap-1.5">
                                <Label
                                    for="product-name"
                                    class="text-xs font-bold text-[#1c1c22]"
                                >
                                    Nama Produk Dagangan *
                                </Label>
                                <Input
                                    id="product-name"
                                    v-model="name"
                                    placeholder="Contoh: Nike Air Force 1 '07 Triple White Edition"
                                    required
                                    class="h-11 rounded-xl text-xs"
                                />
                                <p
                                    v-if="errors.name"
                                    class="text-[11px] font-semibold text-rose-500"
                                >
                                    {{ errors.name }}
                                </p>
                            </div>

                            <!-- Kategori & Tag Promo -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Kategori Selector -->
                                <div class="flex flex-col gap-1.5">
                                    <Label
                                        class="text-xs font-bold text-[#1c1c22]"
                                    >
                                        Kategori Produk *
                                    </Label>
                                    <Select v-model="selectedCategory">
                                        <SelectTrigger
                                            class="h-11 rounded-xl text-xs"
                                        >
                                            <SelectValue
                                                placeholder="Pilih Kategori"
                                            >
                                                {{ selectedCategory || '—' }}
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="c in categoryOptions"
                                                :key="c"
                                                :value="c"
                                            >
                                                {{ c }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Tag Promo Selector -->
                                <div class="flex flex-col gap-1.5">
                                    <Label
                                        class="text-xs font-bold text-[#1c1c22]"
                                    >
                                        Label / Tag Promo Storefront
                                    </Label>
                                    <Select v-model="selectedTag">
                                        <SelectTrigger
                                            class="h-11 rounded-xl text-xs"
                                        >
                                            <SelectValue
                                                placeholder="Pilih Label (Opsional)"
                                            >
                                                {{
                                                    selectedTag &&
                                                    selectedTag !== 'none'
                                                        ? selectedTag
                                                        : 'Tanpa Label Promo'
                                                }}
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none"
                                                >Tanpa Label Promo</SelectItem
                                            >
                                            <SelectItem
                                                v-for="t in labelOptions"
                                                :key="t"
                                                :value="t"
                                            >
                                                {{ t }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <!-- Deskripsi Produk -->
                            <div class="flex flex-col gap-1.5">
                                <Label
                                    for="product-description"
                                    class="text-xs font-bold text-[#1c1c22]"
                                >
                                    Deskripsi & Keunggulan Produk
                                </Label>
                                <Textarea
                                    id="product-description"
                                    v-model="description"
                                    rows="4"
                                    placeholder="Tuliskan spesifikasi lengkap, keunggulan material, garansi toko, dan panduan ukuran..."
                                    class="rounded-xl text-xs"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECTION 2: Harga, Stok, SKU & Brand -->
                    <Card
                        class="rounded-3xl border-black/8 bg-white p-6 shadow-xs"
                    >
                        <CardHeader class="mb-5 p-0">
                            <div
                                class="flex items-center gap-2 text-base font-black text-[#1c1c22]"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 font-bold text-emerald-600"
                                >
                                    <DollarSign class="h-4.5 w-4.5" />
                                </div>
                                <span>Harga & Inventaris Persediaan</span>
                            </div>
                        </CardHeader>

                        <CardContent class="flex flex-col gap-5 p-0">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Harga -->
                                <div class="flex flex-col gap-1.5">
                                    <Label
                                        for="product-price"
                                        class="text-xs font-bold text-[#1c1c22]"
                                    >
                                        Harga Jual Pembeli (Rp) *
                                    </Label>
                                    <Input
                                        id="product-price"
                                        v-model="price"
                                        type="number"
                                        min="0"
                                        placeholder="1549000"
                                        required
                                        class="h-11 rounded-xl font-mono text-xs font-bold"
                                    />
                                </div>

                                <!-- Stok -->
                                <div class="flex flex-col gap-1.5">
                                    <Label
                                        for="product-stock"
                                        class="text-xs font-bold text-[#1c1c22]"
                                    >
                                        Jumlah Stok Unit Tersedia *
                                    </Label>
                                    <Input
                                        id="product-stock"
                                        v-model="stock"
                                        type="number"
                                        min="0"
                                        placeholder="50"
                                        required
                                        class="h-11 rounded-xl font-mono text-xs font-bold"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <!-- SKU -->
                                <div class="flex flex-col gap-1.5">
                                    <Label
                                        for="product-sku"
                                        class="text-xs font-bold text-[#1c1c22]"
                                    >
                                        Kode SKU Produk
                                    </Label>
                                    <Input
                                        id="product-sku"
                                        v-model="sku"
                                        placeholder="NK-AF1-WHITE"
                                        class="h-10 rounded-xl font-mono text-xs"
                                    />
                                </div>

                                <!-- Brand -->
                                <div class="flex flex-col gap-1.5">
                                    <Label
                                        for="product-brand"
                                        class="text-xs font-bold text-[#1c1c22]"
                                    >
                                        Brand / Merek Produk
                                    </Label>
                                    <Select v-model="selectedBrand">
                                        <SelectTrigger
                                            id="product-brand"
                                            class="h-10 rounded-xl text-xs font-semibold"
                                        >
                                            <SelectValue
                                                placeholder="Pilih Brand"
                                            >
                                                {{
                                                    selectedBrand ||
                                                    'Pilih Brand'
                                                }}
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="b in brandOptions"
                                                :key="b"
                                                :value="b"
                                            >
                                                {{ b }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Berat -->
                                <div class="flex flex-col gap-1.5">
                                    <Label
                                        for="product-weight"
                                        class="text-xs font-bold text-[#1c1c22]"
                                    >
                                        Berat Paket (Gram)
                                    </Label>
                                    <Input
                                        id="product-weight"
                                        v-model="weightGram"
                                        type="number"
                                        min="1"
                                        placeholder="500"
                                        class="h-10 rounded-xl font-mono text-xs"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECTION 3: Gambar & Media Produk -->
                    <Card
                        class="rounded-3xl border-black/8 bg-white p-6 shadow-xs"
                    >
                        <CardHeader class="mb-5 p-0">
                            <div
                                class="flex items-center gap-2 text-base font-black text-[#1c1c22]"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 font-bold text-indigo-600"
                                >
                                    <ImagePlus class="h-4.5 w-4.5" />
                                </div>
                                <span>Media Gambar Produk</span>
                            </div>
                        </CardHeader>

                        <CardContent class="flex flex-col gap-4 p-0">
                            <!-- Drag & Drop Upload Zone -->
                            <div
                                @click="fileInput!.click()"
                                class="group relative flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-black/15 bg-[#faf9f6] p-6 text-center transition-all hover:border-amber-500"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-black/5 bg-white text-zinc-500 shadow-xs transition-transform group-hover:scale-110 group-hover:text-amber-600"
                                >
                                    <UploadCloud class="h-6 w-6" />
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-extrabold text-[#1c1c22]"
                                    >
                                        Klik untuk upload gambar dari perangkat
                                    </p>
                                    <p class="mt-0.5 text-[10px] text-zinc-400">
                                        Format PNG, JPG, WEBP hingga 5MB
                                    </p>
                                </div>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="uploadImage"
                                />
                            </div>

                            <div
                                class="my-1 flex items-center justify-center gap-2 text-center text-xs font-bold text-zinc-400"
                            >
                                <span class="h-px flex-1 bg-black/10" />
                                <span>ATAU METODE URL GAMBAR</span>
                                <span class="h-px flex-1 bg-black/10" />
                            </div>

                            <!-- URL Input -->
                            <div class="flex flex-col gap-1.5">
                                <Label
                                    for="product-img"
                                    class="text-xs font-bold text-[#1c1c22]"
                                >
                                    Paste Link URL Gambar (Opsional)
                                </Label>
                                <Input
                                    id="product-img"
                                    v-model="img"
                                    placeholder="https://example.com/gambar-produk.jpg"
                                    class="h-10 rounded-xl font-mono text-xs"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECTION 4: Status Publikasi Switch -->
                    <Card
                        class="rounded-3xl border-black/8 bg-white p-6 shadow-xs"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl font-bold text-white shadow-xs"
                                    :class="
                                        isActive
                                            ? 'bg-emerald-600'
                                            : 'bg-rose-500'
                                    "
                                >
                                    <CheckCircle2 class="h-5 w-5" />
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-black text-[#1c1c22]"
                                    >
                                        Status Publikasi Produk
                                    </h4>
                                    <p class="mt-0.5 text-xs text-zinc-500">
                                        {{
                                            isActive
                                                ? 'Produk aktif & dapat langsung dibeli oleh calon pelanggan di storefront.'
                                                : 'Produk disimpan sebagai draft & disembunyikan dari publik.'
                                        }}
                                    </p>
                                </div>
                            </div>
                            <Switch
                                :checked="isActive"
                                @update:checked="isActive = $event"
                            />
                        </div>
                    </Card>
                </form>

                <!-- ── RIGHT COLUMN: STICKY LIVE STOREFRONT PREVIEW (4 Cols) ── -->
                <div class="sticky top-20 flex flex-col gap-4 lg:col-span-4">
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-extrabold text-[#1c1c22]"
                        >
                            <Eye class="h-4 w-4 text-amber-500" /> Live Preview
                            Card
                        </span>
                        <Badge variant="teal" class="text-[9px] font-bold"
                            >Real-time Storefront</Badge
                        >
                    </div>

                    <!-- Live Product Card Preview -->
                    <div
                        class="overflow-hidden rounded-3xl border border-black/10 bg-white shadow-xl"
                    >
                        <!-- Image Container -->
                        <div
                            class="relative aspect-4/3 w-full overflow-hidden bg-[#faf9f6]"
                        >
                            <img
                                v-if="img"
                                :src="img"
                                :alt="name || 'Pratinjau Produk'"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full flex-col items-center justify-center gap-2 text-zinc-300"
                            >
                                <Package class="h-12 w-12" />
                                <span
                                    class="text-[10px] font-bold text-zinc-400"
                                    >Belum ada gambar</span
                                >
                            </div>

                            <!-- Badges Overlay -->
                            <div
                                class="absolute top-3 left-3 flex flex-wrap gap-1.5"
                            >
                                <Badge
                                    v-if="selectedTag"
                                    variant="amber"
                                    class="px-2.5 py-0.5 text-[9px] font-black uppercase shadow-xs"
                                >
                                    {{ selectedTag }}
                                </Badge>
                                <Badge
                                    v-if="!isActive"
                                    variant="rose"
                                    class="px-2.5 py-0.5 text-[9px] font-black uppercase shadow-xs"
                                >
                                    Nonaktif (Draft)
                                </Badge>
                            </div>

                            <!-- Rating Pill -->
                            <div
                                class="absolute right-3 bottom-3 flex items-center gap-1 rounded-xl bg-black/60 px-2.5 py-1 text-[11px] font-black text-amber-400 text-white backdrop-blur-xs"
                            >
                                <Star
                                    class="h-3.5 w-3.5 fill-amber-400 text-amber-400"
                                />
                                4.9
                            </div>
                        </div>

                        <!-- Content Container -->
                        <div class="flex flex-col gap-3 p-5">
                            <div class="flex items-center justify-between">
                                <Badge
                                    variant="outline"
                                    class="border-black/10 text-[10px] font-bold text-zinc-600"
                                >
                                    {{ selectedCategory || 'Uncategorized' }}
                                </Badge>
                                <span
                                    class="font-mono text-[10px] font-bold text-zinc-400"
                                    >SKU: {{ sku || 'SKU-SAMPLE' }}</span
                                >
                            </div>

                            <h3
                                class="line-clamp-2 text-sm leading-snug font-black text-[#1c1c22]"
                            >
                                {{ name || 'Nama Produk Dagangan Anda...' }}
                            </h3>

                            <div
                                class="flex items-end justify-between border-t border-black/5 pt-2"
                            >
                                <div>
                                    <span
                                        class="block text-[10px] font-bold text-zinc-400 uppercase"
                                        >Harga Jual</span
                                    >
                                    <span
                                        class="font-mono text-base font-black text-amber-600"
                                    >
                                        {{ formattedPricePreview }}
                                    </span>
                                </div>

                                <div class="text-right">
                                    <span
                                        class="block text-[10px] font-bold text-zinc-400 uppercase"
                                        >Stok Unit</span
                                    >
                                    <span
                                        class="font-mono text-xs font-black text-zinc-700"
                                        >{{ stock || '0' }} pcs</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guidance Info Box -->
                    <div
                        class="flex items-start gap-2.5 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4 text-xs text-amber-900"
                    >
                        <Info class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                        <p class="text-[11px] leading-relaxed font-medium">
                            Pastikan data harga dan gambar sudah sesuai sebelum
                            diterbitkan. Pembeli di toko Anda akan langsung
                            melihat kartu produk ini pada katalog webstore.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
