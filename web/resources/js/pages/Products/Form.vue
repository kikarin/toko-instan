<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Save, Package, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/sonner';
import AppLayout from '@/layouts/AppLayout.vue';

interface Product {
    id: number;
    name: string;
    category: string;
    price: number;
    stock: number;
    sold: number;
    tag: string | null;
    img: string | null;
}

interface Props {
    product?: Product;
    categories?: string[];
}

const props = defineProps<Props>();

const isEdit = ref(!!props.product);
const name = ref(props.product?.name ?? '');
const category = ref(
    props.product?.category ?? props.categories?.[0] ?? 'Fashion',
);
const price = ref(props.product ? String(props.product.price) : '');
const stock = ref(props.product ? String(props.product.stock) : '0');
const tag = ref(props.product?.tag ?? '');
const img = ref(props.product?.img ?? '');
const isLoading = ref(false);
const errors = ref<Record<string, string>>({});

const tags = ['', 'Bestseller', 'Hot', 'Baru'];

function back() {
    router.visit('/products');
}

function submit() {
    const payload = {
        name: name.value,
        category: category.value,
        price: price.value,
        stock: stock.value,
        tag: tag.value,
        img: img.value,
    };

    const options = {
        onStart: () => {
            isLoading.value = true;
            errors.value = {};
        },
        onSuccess: () => {
            toast.success(
                isEdit.value ? 'Produk diperbarui!' : 'Produk ditambahkan!',
            );
        },
        onError: (errs: Record<string, string>) => {
            errors.value = errs;
            toast.error('Periksa kembali data produk.');
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
    <Head
        :title="
            isEdit ? 'Edit Produk - Toko Instan' : 'Tambah Produk - Toko Instan'
        "
    />

    <AppLayout activePage="Produk">
        <main class="mx-auto flex w-full max-w-3xl flex-col gap-5 p-4 sm:p-6">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="sm" class="text-xs" @click="back">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Kembali
                </Button>
                <div>
                    <p
                        class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                    >
                        Seller · Katalog
                    </p>
                    <h1 class="text-2xl font-extrabold text-[#1c1c22]">
                        {{ isEdit ? 'Edit Produk' : 'Tambah Produk' }}
                    </h1>
                </div>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-5">
                <Card class="p-6">
                    <CardHeader class="mb-4 p-0">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Package class="h-4 w-4 text-[#e07c28]" />
                            Informasi Produk
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-4 p-0">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold text-[#1c1c22]"
                                >Nama Produk *</label
                            >
                            <Input
                                v-model="name"
                                placeholder="Contoh: Kemeja Batik Premium"
                                required
                            />
                            <p
                                v-if="errors.name"
                                class="text-[11px] text-red-500"
                            >
                                {{ errors.name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold text-[#1c1c22]"
                                    >Kategori *</label
                                >
                                <select
                                    v-model="category"
                                    class="w-full rounded-2xl border border-black/12 bg-white px-3.5 py-2.5 text-xs text-[#1c1c22] transition-all outline-none focus:border-[#e07c28] focus:ring-2 focus:ring-[#e07c28]/20"
                                >
                                    <option
                                        v-for="c in categories"
                                        :key="c"
                                        :value="c"
                                    >
                                        {{ c }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold text-[#1c1c22]"
                                    >Label / Tag</label
                                >
                                <select
                                    v-model="tag"
                                    class="w-full rounded-2xl border border-black/12 bg-white px-3.5 py-2.5 text-xs text-[#1c1c22] transition-all outline-none focus:border-[#e07c28] focus:ring-2 focus:ring-[#e07c28]/20"
                                >
                                    <option
                                        v-for="t in tags"
                                        :key="t"
                                        :value="t"
                                    >
                                        {{ t || 'Tanpa Label' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold text-[#1c1c22]"
                                    >Harga (Rp) *</label
                                >
                                <Input
                                    v-model="price"
                                    type="number"
                                    min="0"
                                    placeholder="285000"
                                    required
                                />
                                <p
                                    v-if="errors.price"
                                    class="text-[11px] text-red-500"
                                >
                                    {{ errors.price }}
                                </p>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold text-[#1c1c22]"
                                    >Stok *</label
                                >
                                <Input
                                    v-model="stock"
                                    type="number"
                                    min="0"
                                    placeholder="100"
                                    required
                                />
                                <p
                                    v-if="errors.stock"
                                    class="text-[11px] text-red-500"
                                >
                                    {{ errors.stock }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold text-[#1c1c22]"
                                >URL Gambar (opsional)</label
                            >
                            <Input
                                v-model="img"
                                placeholder="https://images.unsplash.com/..."
                            />
                            <p
                                v-if="errors.img"
                                class="text-[11px] text-red-500"
                            >
                                {{ errors.img }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex gap-3">
                    <Button
                        variant="outline"
                        size="lg"
                        class="font-bold"
                        @click="back"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        variant="amber"
                        size="lg"
                        class="flex-1 text-sm font-bold shadow-md"
                        :disabled="isLoading"
                    >
                        <Loader2
                            v-if="isLoading"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <Save v-else class="mr-2 h-4 w-4" />
                        {{ isEdit ? 'Simpan Perubahan' : 'Simpan Produk' }}
                    </Button>
                </div>
            </form>
        </main>
    </AppLayout>
</template>
