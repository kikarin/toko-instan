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
    Plus,
    X,
    Layers,
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
import type { Product, MarketplaceProduct } from '@/types/product';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import { computed } from 'vue';

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
    variantOptions,
    variants,
    generateVariants,
} = useProductForm(props);

// Create preview product for the Live Preview Card
const previewProduct = computed<MarketplaceProduct>(() => ({
    id: 0,
    name: name.value || 'Nama Produk Dagangan Anda...',
    price: formattedPricePreview.value,
    rating: 4.8,
    sold: 3,
    img: img.value || 'https://placehold.co/600x600?text=Belum+ada+gambar',
    store: 'Toko Anda',
    tag: selectedTag.value || null,
    cat: selectedCategory.value || 'Uncategorized',
    freeShipping: true,
    sku: sku.value || 'SKU-SAMPLE',
    stock: Number(stock.value) || 0,
}));

// Add empty option
function addVariantOption() {
    if (variantOptions.value.length >= 2) {
        return; // Max 2 options
    }
    variantOptions.value.push({ name: '', values: [] });
}

function removeVariantOption(index: number) {
    variantOptions.value.splice(index, 1);
    generateVariants();
}

function addOptionValue(index: number) {
    const val = variantOptions.value[index].inputValue?.trim();
    if (val && !variantOptions.value[index].values.includes(val)) {
        variantOptions.value[index].values.push(val);
        variantOptions.value[index].inputValue = '';
        generateVariants();
    }
}

function removeOptionValue(optionIndex: number, valueIndex: number) {
    variantOptions.value[optionIndex].values.splice(valueIndex, 1);
    generateVariants();
}
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
                                class="inline-flex items-center gap-1 rounded-full border border-primary/20 bg-primary/10 px-2.5 py-0.5 text-[10px] font-black text-primary"
                            >
                                <Sparkles class="h-3 w-3" /> PRODUCT STUDIO
                            </span>
                        </div>
                        <h1
                            class="text-xl font-black text-foreground sm:text-2xl"
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
                        variant="default"
                        size="sm"
                        class="h-10 cursor-pointer rounded-xl px-6 text-xs font-extrabold shadow-lg shadow-primary/20"
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
                        class="rounded-3xl border-border bg-card p-6 shadow-xs"
                    >
                        <CardHeader class="mb-5 p-0">
                            <div
                                class="flex items-center gap-2 text-base font-black text-foreground"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-primary/10 font-bold text-primary"
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
                                    class="text-xs font-bold text-foreground"
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
                                    class="text-[11px] font-semibold text-destructive"
                                >
                                    {{ errors.name }}
                                </p>
                            </div>

                            <!-- Kategori & Tag Promo -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Kategori Selector -->
                                <div class="flex flex-col gap-1.5">
                                    <Label
                                        class="text-xs font-bold text-foreground"
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
                                        class="text-xs font-bold text-foreground"
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
                                    class="text-xs font-bold text-foreground"
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
                        class="rounded-3xl border-border bg-card p-6 shadow-xs"
                    >
                        <CardHeader class="mb-5 p-0">
                            <div
                                class="flex items-center gap-2 text-base font-black text-foreground"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-secondary/10 font-bold text-secondary"
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
                                        class="text-xs font-bold text-foreground"
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
                                        class="text-xs font-bold text-foreground"
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
                                        class="text-xs font-bold text-foreground"
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
                                        class="text-xs font-bold text-foreground"
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
                                        class="text-xs font-bold text-foreground"
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
                        class="rounded-3xl border-border bg-card p-6 shadow-xs"
                    >
                        <CardHeader class="mb-5 p-0">
                            <div
                                class="flex items-center gap-2 text-base font-black text-foreground"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-accent/10 font-bold text-accent"
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
                                class="group relative flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-border bg-muted p-6 text-center transition-all hover:border-primary"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-border bg-background text-muted-foreground shadow-xs transition-transform group-hover:scale-110 group-hover:text-primary"
                                >
                                    <UploadCloud class="h-6 w-6" />
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-extrabold text-foreground"
                                    >
                                        Klik untuk upload gambar dari perangkat
                                    </p>
                                    <p class="mt-0.5 text-[10px] text-muted-foreground/80">
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
                                class="my-1 flex items-center justify-center gap-2 text-center text-xs font-bold text-muted-foreground/80"
                            >
                                <span class="h-px flex-1 bg-border" />
                                <span>ATAU METODE URL GAMBAR</span>
                                <span class="h-px flex-1 bg-border" />
                            </div>

                            <!-- URL Input -->
                            <div class="flex flex-col gap-1.5">
                                <Label
                                    for="product-img"
                                    class="text-xs font-bold text-foreground"
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
                        class="rounded-3xl border-border bg-card p-6 shadow-xs"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl font-bold text-white shadow-xs"
                                    :class="
                                        isActive
                                            ? 'bg-emerald-600'
                                            : 'bg-destructive'
                                    "
                                >
                                    <CheckCircle2 class="h-5 w-5" />
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-black text-foreground"
                                    >
                                        Status Publikasi Produk
                                    </h4>
                                    <p class="mt-0.5 text-xs text-muted-foreground">
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
                    <!-- SECTION 5: Varian Produk (Dinamis) -->
                    <Card
                        class="rounded-3xl border-border bg-card p-6 shadow-xs"
                    >
                        <CardHeader class="mb-5 p-0">
                            <div
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2 text-base font-black text-foreground">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-primary/10 font-bold text-primary"
                                    >
                                        <Layers class="h-4.5 w-4.5" />
                                    </div>
                                    <span>Varian Produk</span>
                                </div>
                                <Button
                                    type="button"
                                    v-if="variantOptions.length < 2"
                                    @click="addVariantOption"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 rounded-lg text-xs font-bold"
                                >
                                    <Plus class="mr-1 h-3.5 w-3.5" /> Tambah Opsi
                                </Button>
                            </div>
                        </CardHeader>

                        <CardContent class="flex flex-col gap-6 p-0">
                            <div v-if="variantOptions.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border py-8 text-center">
                                <Layers class="mb-2 h-8 w-8 text-muted-foreground/50" />
                                <p class="text-xs font-bold text-foreground">Tidak ada varian</p>
                                <p class="mt-1 text-[10px] text-muted-foreground">Tambah opsi jika produk memiliki pilihan warna, ukuran, dsb.</p>
                            </div>

                            <div v-else class="flex flex-col gap-4">
                                <div v-for="(option, index) in variantOptions" :key="index" class="relative rounded-2xl border border-border bg-muted/30 p-4">
                                    <button type="button" @click="removeVariantOption(index)" class="absolute right-3 top-3 rounded-md text-muted-foreground hover:text-destructive">
                                        <X class="h-4 w-4" />
                                    </button>
                                    
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div class="flex flex-col gap-1.5">
                                            <Label class="text-xs font-bold text-foreground">Nama Opsi</Label>
                                            <Input v-model="option.name" placeholder="Contoh: Warna atau Ukuran" class="h-9 rounded-xl text-xs" @change="generateVariants" />
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <Label class="text-xs font-bold text-foreground">Daftar Pilihan</Label>
                                            <div v-if="option.values.length > 0" class="flex flex-wrap gap-2 mb-1">
                                                <Badge v-for="(val, vIdx) in option.values" :key="vIdx" variant="secondary" class="gap-1 px-2 py-1 text-xs font-semibold">
                                                    {{ val }}
                                                    <button type="button" @click="removeOptionValue(index, vIdx)" class="text-muted-foreground hover:text-foreground ml-1">
                                                        <X class="h-3 w-3" />
                                                    </button>
                                                </Badge>
                                            </div>
                                            <div class="flex gap-2">
                                                <Input v-model="option.inputValue" @keydown.enter.prevent="addOptionValue(index)" placeholder="Ketik pilihan & tekan Enter" class="h-9 rounded-xl text-xs flex-1" />
                                                <Button type="button" @click="addOptionValue(index)" variant="outline" size="sm" class="h-9 px-3 rounded-xl font-bold">Tambah</Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Varian Table -->
                            <div v-if="variants.length > 0" class="overflow-x-auto rounded-2xl border border-border">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-muted text-muted-foreground">
                                        <tr>
                                            <th class="px-4 py-3 font-bold">Varian</th>
                                            <th class="px-4 py-3 font-bold">Harga (Rp)</th>
                                            <th class="px-4 py-3 font-bold">Stok</th>
                                            <th class="px-4 py-3 font-bold">SKU</th>
                                            <th class="px-4 py-3 font-bold">URL Foto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border bg-card">
                                        <tr v-for="(variant, vIndex) in variants" :key="vIndex">
                                            <td class="px-4 py-3 font-bold text-foreground">{{ variant.name }}</td>
                                            <td class="px-4 py-3">
                                                <Input v-model="variant.price" type="number" min="0" placeholder="Harga" class="h-8 w-24 rounded-lg text-xs" />
                                            </td>
                                            <td class="px-4 py-3">
                                                <Input v-model="variant.stock" type="number" min="0" placeholder="Stok" class="h-8 w-20 rounded-lg text-xs" />
                                            </td>
                                            <td class="px-4 py-3">
                                                <Input v-model="variant.sku" placeholder="SKU" class="h-8 w-24 rounded-lg text-xs" />
                                            </td>
                                            <td class="px-4 py-3">
                                                <Input v-model="variant.img" placeholder="https://..." class="h-8 w-full min-w-32 rounded-lg text-xs" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                </form>

                <!-- ── RIGHT COLUMN: STICKY LIVE STOREFRONT PREVIEW (4 Cols) ── -->
                <div class="sticky top-20 flex flex-col gap-4 lg:col-span-4">
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-extrabold text-foreground"
                        >
                            <Eye class="h-4 w-4 text-primary" /> Live Preview
                            Card
                        </span>
                        <Badge variant="teal" class="text-[9px] font-bold"
                            >Real-time Storefront</Badge
                        >
                    </div>

                    <!-- Live Product Card Preview -->
                    <div class="relative w-full max-w-sm mx-auto pointer-events-none sm:pointer-events-auto">
                        <ProductCard :product="previewProduct" />
                        
                        <!-- Draft Overlay -->
                        <div v-if="!isActive" class="absolute inset-0 z-10 flex items-center justify-center bg-card/60 backdrop-blur-[2px] rounded-2xl border-2 border-dashed border-rose-500/50">
                            <Badge variant="rose" class="px-3 py-1 text-xs font-black uppercase shadow-lg shadow-rose-500/20">
                                Nonaktif (Draft)
                            </Badge>
                        </div>
                    </div>

                    <!-- Guidance Info Box -->
                    <div
                        class="flex items-start gap-2.5 rounded-2xl border border-primary/20 bg-primary/10 p-4 text-xs text-primary"
                    >
                        <Info class="mt-0.5 h-4 w-4 shrink-0 text-primary" />
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
