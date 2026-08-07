<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
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
import { ref, computed } from 'vue';
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
import { toast } from '@/components/ui/sonner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

interface Product {
    id: number;
    name: string;
    category: string;
    price: number;
    stock: number;
    sold: number;
    is_active: boolean;
    tag: string | null;
    img: string | null;
    description?: string | null;
    sku?: string | null;
    brand?: string | null;
    weight_gram?: number;
}

interface Props {
    product?: Product;
    categories?: string[];
    labels?: string[];
    brands?: string[];
}

const props = defineProps<Props>();

const isEdit = ref(!!props.product);
const name = ref(props.product?.name ?? '');

const categoryOptions = computed(() => {
    if (props.categories && props.categories.length > 0) return props.categories;
    return ['Sneakers', 'Apparel', 'Accessories', 'Sportswear', 'Running'];
});

const labelOptions = computed(() => {
    if (props.labels && props.labels.length > 0) return props.labels;
    return ['BESTSELLER', 'NEW ARRIVAL', 'PROMO 8.8', 'GARANSI RESMI', 'LIMITED EDITION'];
});

const brandOptions = computed(() => {
    if (props.brands && props.brands.length > 0) return props.brands;
    return ['Nike', 'Jordan', 'Adidas', 'Puma', 'Converse'];
});

// Single category from catalog dropdown
const selectedCategory = ref<string>(
    props.product?.category
        ? props.product.category.split(',')[0].trim()
        : (categoryOptions.value[0] ?? 'Sneakers'),
);

const selectedTag = ref<string>(
    props.product?.tag
        ? props.product.tag.split(',')[0].trim()
        : '',
);

const selectedBrand = ref<string>(
    props.product?.brand
        ? props.product.brand
        : (brandOptions.value[0] ?? 'Nike'),
);

const price = ref(props.product ? String(props.product.price) : '');
const stock = ref(props.product ? String(props.product.stock) : '10');
const isActive = ref(props.product?.is_active ?? true);
const img = ref(props.product?.img ?? '');
const description = ref(
    props.product?.description ??
        'Produk original berkualitas tinggi dengan jaminan garansi keaslian 100%, material daya tahan maksimal, dan kenyamanan optimal.',
);
const sku = ref(props.product?.sku ?? '');
const weightGram = ref(props.product?.weight_gram ? String(props.product.weight_gram) : '500');
const isLoading = ref(false);
const uploading = ref(false);
const errors = ref<Record<string, string>>({});
const fileInput = ref<HTMLInputElement | null>(null);

// Formatted Price Computed for Live Preview
const formattedPricePreview = computed(() => {
    const num = Number(price.value);

    if (!num || Number.isNaN(num)) {
return 'Rp 0';
}

    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
});

async function uploadImage(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
return;
}

    uploading.value = true;

    try {
        const formData = new FormData();
        formData.append('file', file);

        const xsrfToken = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        const response = await fetch('/uploads', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': xsrfToken ? decodeURIComponent(xsrfToken) : '',
            },
            body: formData,
        });

        const result = await response.json();

        if (!response.ok || !result.url) {
            throw new Error(result.message ?? 'Upload gambar gagal.');
        }

        img.value = result.url;
        toast.success('Gambar produk berhasil diunggah!');
    } catch {
        toast.error('Gagal mengunggah gambar produk.');
    } finally {
        uploading.value = false;
        target.value = '';
    }
}

function back() {
    router.visit('/products');
}

function submit() {
    if (!selectedCategory.value) {
        toast.error('Pilih kategori produk terlebih dahulu.');

        return;
    }

    const payload = {
        name: name.value,
        category: selectedCategory.value,
        price: price.value,
        stock: stock.value,
        is_active: isActive.value,
        tag: selectedTag.value && selectedTag.value !== 'none' ? selectedTag.value : null,
        img: img.value,
        description: description.value,
        sku: sku.value,
        brand: selectedBrand.value || null,
        weight_gram: weightGram.value,
    };

    const options = {
        onStart: () => {
            isLoading.value = true;
            errors.value = {};
        },
        onSuccess: () => {
            toast.success(isEdit.value ? 'Produk berhasil diperbarui!' : 'Produk baru berhasil ditambahkan!');
        },
        onError: (errs: Record<string, string>) => {
            errors.value = errs;
            toast.error('Periksa kembali data produk yang diisi.');
        },
        onFinish: () => {
            isLoading.value = false;
        },
    };

    if (isEdit.value && props.product) {
        router.put(`/products/${props.product.id}`, payload, options);
    } else {
        router.post('/products', payload, options);
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Produk Studio — Dashboard Merchant' : 'Tambah Produk Baru — Dashboard Merchant'" />

    <AppLayout activePage="Produk">
        <div class="flex flex-col gap-6 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            <!-- ── Top Action Header ── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button variant="outline" size="sm" class="h-9 px-3 text-xs font-bold rounded-xl border-black/10" @click="back">
                        <ArrowLeft class="mr-1.5 h-4 w-4" /> Kembali
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-black text-amber-600 border border-amber-500/20">
                                <Sparkles class="h-3 w-3" /> PRODUCT STUDIO
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-[#1c1c22]">
                            {{ isEdit ? 'Edit Rincian Produk' : 'Tambah Produk Baru ke Toko' }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-10 px-4 text-xs font-bold rounded-xl" @click="back">
                        Batal
                    </Button>
                    <Button
                        variant="amber"
                        size="sm"
                        class="h-10 px-6 text-xs font-extrabold rounded-xl shadow-lg shadow-amber-500/20 cursor-pointer"
                        :disabled="isLoading"
                        @click="submit"
                    >
                        <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        {{ isEdit ? 'Simpan Perubahan' : 'Terbitkan Produk Baru' }}
                    </Button>
                </div>
            </div>

            <!-- ── Main Studio Grid (Form vs Live Preview Side-by-Side) ── -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- ── LEFT COLUMN: FORM EDITORS (8 Cols) ── -->
                <form @submit.prevent="submit" class="lg:col-span-8 flex flex-col gap-6">
                    <!-- SECTION 1: Informasi Dasar Produk -->
                    <Card class="rounded-3xl border-black/8 shadow-xs bg-white p-6">
                        <CardHeader class="p-0 mb-5">
                            <div class="flex items-center gap-2 text-base font-black text-[#1c1c22]">
                                <div class="h-8 w-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                    <Package class="h-4.5 w-4.5" />
                                </div>
                                <span>Informasi & Identitas Produk</span>
                            </div>
                        </CardHeader>

                        <CardContent class="p-0 flex flex-col gap-5">
                            <!-- Nama Produk -->
                            <div class="flex flex-col gap-1.5">
                                <Label for="product-name" class="text-xs font-bold text-[#1c1c22]">
                                    Nama Produk Dagangan *
                                </Label>
                                <Input
                                    id="product-name"
                                    v-model="name"
                                    placeholder="Contoh: Nike Air Force 1 '07 Triple White Edition"
                                    required
                                    class="h-11 text-xs rounded-xl"
                                />
                                <p v-if="errors.name" class="text-[11px] text-rose-500 font-semibold">
                                    {{ errors.name }}
                                </p>
                            </div>

                            <!-- Kategori & Tag Promo -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Kategori Selector -->
                                <div class="flex flex-col gap-1.5">
                                    <Label class="text-xs font-bold text-[#1c1c22]">
                                        Kategori Produk *
                                    </Label>
                                    <Select v-model="selectedCategory">
                                        <SelectTrigger class="h-11 text-xs rounded-xl">
                                            <SelectValue placeholder="Pilih Kategori">
                                                {{ selectedCategory || '—' }}
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="c in categoryOptions" :key="c" :value="c">
                                                {{ c }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Tag Promo Selector -->
                                <div class="flex flex-col gap-1.5">
                                    <Label class="text-xs font-bold text-[#1c1c22]">
                                        Label / Tag Promo Storefront
                                    </Label>
                                    <Select v-model="selectedTag">
                                        <SelectTrigger class="h-11 text-xs rounded-xl">
                                            <SelectValue placeholder="Pilih Label (Opsional)">
                                                {{ selectedTag && selectedTag !== 'none' ? selectedTag : 'Tanpa Label Promo' }}
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">Tanpa Label Promo</SelectItem>
                                            <SelectItem v-for="t in labelOptions" :key="t" :value="t">
                                                {{ t }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>

                                    
                                </div>
                            </div>

                            <!-- Deskripsi Produk -->
                            <div class="flex flex-col gap-1.5">
                                <Label for="product-description" class="text-xs font-bold text-[#1c1c22]">
                                    Deskripsi & Keunggulan Produk
                                </Label>
                                <Textarea
                                    id="product-description"
                                    v-model="description"
                                    rows="4"
                                    placeholder="Tuliskan spesifikasi lengkap, keunggulan material, garansi toko, dan panduan ukuran..."
                                    class="text-xs rounded-xl"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECTION 2: Harga, Stok, SKU & Brand -->
                    <Card class="rounded-3xl border-black/8 shadow-xs bg-white p-6">
                        <CardHeader class="p-0 mb-5">
                            <div class="flex items-center gap-2 text-base font-black text-[#1c1c22]">
                                <div class="h-8 w-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                    <DollarSign class="h-4.5 w-4.5" />
                                </div>
                                <span>Harga & Inventaris Persediaan</span>
                            </div>
                        </CardHeader>

                        <CardContent class="p-0 flex flex-col gap-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Harga -->
                                <div class="flex flex-col gap-1.5">
                                    <Label for="product-price" class="text-xs font-bold text-[#1c1c22]">
                                        Harga Jual Pembeli (Rp) *
                                    </Label>
                                    <Input
                                        id="product-price"
                                        v-model="price"
                                        type="number"
                                        min="0"
                                        placeholder="1549000"
                                        required
                                        class="h-11 text-xs font-mono font-bold rounded-xl"
                                    />
                                </div>

                                <!-- Stok -->
                                <div class="flex flex-col gap-1.5">
                                    <Label for="product-stock" class="text-xs font-bold text-[#1c1c22]">
                                        Jumlah Stok Unit Tersedia *
                                    </Label>
                                    <Input
                                        id="product-stock"
                                        v-model="stock"
                                        type="number"
                                        min="0"
                                        placeholder="50"
                                        required
                                        class="h-11 text-xs font-mono font-bold rounded-xl"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <!-- SKU -->
                                <div class="flex flex-col gap-1.5">
                                    <Label for="product-sku" class="text-xs font-bold text-[#1c1c22]">
                                        Kode SKU Produk
                                    </Label>
                                    <Input
                                        id="product-sku"
                                        v-model="sku"
                                        placeholder="NK-AF1-WHITE"
                                        class="h-10 text-xs font-mono rounded-xl"
                                    />
                                </div>

                                <!-- Brand -->
                                <div class="flex flex-col gap-1.5">
                                    <Label for="product-brand" class="text-xs font-bold text-[#1c1c22]">
                                        Brand / Merek Produk
                                    </Label>
                                    <Select v-model="selectedBrand">
                                        <SelectTrigger id="product-brand" class="h-10 text-xs rounded-xl font-semibold">
                                            <SelectValue placeholder="Pilih Brand">
                                                {{ selectedBrand || 'Pilih Brand' }}
                                            </SelectValue>
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="b in brandOptions" :key="b" :value="b">
                                                {{ b }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>

                                </div>

                                <!-- Berat -->
                                <div class="flex flex-col gap-1.5">
                                    <Label for="product-weight" class="text-xs font-bold text-[#1c1c22]">
                                        Berat Paket (Gram)
                                    </Label>
                                    <Input
                                        id="product-weight"
                                        v-model="weightGram"
                                        type="number"
                                        min="1"
                                        placeholder="500"
                                        class="h-10 text-xs font-mono rounded-xl"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECTION 3: Gambar & Media Produk -->
                    <Card class="rounded-3xl border-black/8 shadow-xs bg-white p-6">
                        <CardHeader class="p-0 mb-5">
                            <div class="flex items-center gap-2 text-base font-black text-[#1c1c22]">
                                <div class="h-8 w-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                    <ImagePlus class="h-4.5 w-4.5" />
                                </div>
                                <span>Media Gambar Produk</span>
                            </div>
                        </CardHeader>

                        <CardContent class="p-0 flex flex-col gap-4">
                            <!-- Drag & Drop Upload Zone -->
                            <div
                                @click="fileInput!.click()"
                                class="relative border-2 border-dashed border-black/15 hover:border-amber-500 bg-[#faf9f6] rounded-2xl p-6 text-center cursor-pointer transition-all flex flex-col items-center justify-center gap-2 group"
                            >
                                <div class="h-12 w-12 rounded-2xl bg-white text-zinc-500 group-hover:text-amber-600 flex items-center justify-center shadow-xs border border-black/5 transition-transform group-hover:scale-110">
                                    <UploadCloud class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-extrabold text-[#1c1c22]">Klik untuk upload gambar dari perangkat</p>
                                    <p class="text-[10px] text-zinc-400 mt-0.5">Format PNG, JPG, WEBP hingga 5MB</p>
                                </div>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="uploadImage"
                                />
                            </div>

                            <div class="flex items-center gap-2 text-xs font-bold text-zinc-400 text-center justify-center my-1">
                                <span class="h-px bg-black/10 flex-1" />
                                <span>ATAU METODE URL GAMBAR</span>
                                <span class="h-px bg-black/10 flex-1" />
                            </div>

                            <!-- URL Input -->
                            <div class="flex flex-col gap-1.5">
                                <Label for="product-img" class="text-xs font-bold text-[#1c1c22]">
                                    Paste Link URL Gambar (Opsional)
                                </Label>
                                <Input
                                    id="product-img"
                                    v-model="img"
                                    placeholder="https://images.unsplash.com/photo-1542291026-7eec264c27ff"
                                    class="h-10 text-xs font-mono rounded-xl"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SECTION 4: Status Publikasi Switch -->
                    <Card class="rounded-3xl border-black/8 shadow-xs bg-white p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-2xl flex items-center justify-center font-bold text-white shadow-xs"
                                    :class="isActive ? 'bg-emerald-600' : 'bg-rose-500'"
                                >
                                    <CheckCircle2 class="h-5 w-5" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-[#1c1c22]">Status Publikasi Produk</h4>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        {{ isActive ? 'Produk aktif & dapat langsung dibeli oleh calon pelanggan di storefront.' : 'Produk disimpan sebagai draft & disembunyikan dari publik.' }}
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
                <div class="lg:col-span-4 sticky top-20 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-[#1c1c22]">
                            <Eye class="h-4 w-4 text-amber-500" /> Live Preview Card
                        </span>
                        <Badge variant="teal" class="text-[9px] font-bold">Real-time Storefront</Badge>
                    </div>

                    <!-- Live Product Card Preview -->
                    <div class="overflow-hidden rounded-3xl border border-black/10 bg-white shadow-xl">
                        <!-- Image Container -->
                        <div class="relative aspect-4/3 w-full bg-[#faf9f6] overflow-hidden">
                            <img
                                v-if="img"
                                :src="img"
                                :alt="name || 'Pratinjau Produk'"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-zinc-300 flex-col gap-2"
                            >
                                <Package class="h-12 w-12" />
                                <span class="text-[10px] font-bold text-zinc-400">Belum ada gambar</span>
                            </div>

                            <!-- Badges Overlay -->
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                <Badge v-if="selectedTag" variant="amber" class="text-[9px] font-black uppercase shadow-xs px-2.5 py-0.5">
                                    {{ selectedTag }}
                                </Badge>
                                <Badge v-if="!isActive" variant="rose" class="text-[9px] font-black uppercase shadow-xs px-2.5 py-0.5">
                                    Nonaktif (Draft)
                                </Badge>
                            </div>

                            <!-- Rating Pill -->
                            <div class="absolute bottom-3 right-3 flex items-center gap-1 text-[11px] font-black text-amber-400 bg-black/60 px-2.5 py-1 rounded-xl backdrop-blur-xs text-white">
                                <Star class="h-3.5 w-3.5 fill-amber-400 text-amber-400" /> 4.9
                            </div>
                        </div>

                        <!-- Content Container -->
                        <div class="p-5 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <Badge variant="outline" class="text-[10px] font-bold text-zinc-600 border-black/10">
                                    {{ selectedCategory || 'Uncategorized' }}
                                </Badge>
                                <span class="text-[10px] font-bold text-zinc-400 font-mono">SKU: {{ sku || 'SKU-SAMPLE' }}</span>
                            </div>

                            <h3 class="text-sm font-black text-[#1c1c22] line-clamp-2 leading-snug">
                                {{ name || 'Nama Produk Dagangan Anda...' }}
                            </h3>

                            <div class="flex items-end justify-between pt-2 border-t border-black/5">
                                <div>
                                    <span class="text-[10px] font-bold text-zinc-400 block uppercase">Harga Jual</span>
                                    <span class="font-mono text-base font-black text-amber-600">
                                        {{ formattedPricePreview }}
                                    </span>
                                </div>

                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-zinc-400 block uppercase">Stok Unit</span>
                                    <span class="text-xs font-black text-zinc-700 font-mono">{{ stock || '0' }} pcs</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guidance Info Box -->
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-2.5 text-xs text-amber-900">
                        <Info class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" />
                        <p class="text-[11px] font-medium leading-relaxed">
                            Pastikan data harga dan gambar sudah sesuai sebelum diterbitkan. Pembeli di toko Anda akan langsung melihat kartu produk ini pada katalog webstore.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
