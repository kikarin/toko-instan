<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Package, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
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

function deleteProduct(product: Product) {
    if (!window.confirm(`Hapus produk "${product.name}"?`)) {
        return;
    }

    router.delete(`/products/${product.id}`, {
        onError: () => toast.error('Gagal menghapus produk.'),
    });
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
                        <span
                            class="rounded-full px-2.5 py-1 text-[10px] font-bold"
                            :class="
                                lowStock(p.stock)
                                    ? 'bg-red-100 text-red-600'
                                    : 'bg-[#22a15a1a] text-[#22a15a]'
                            "
                        >
                            Stok {{ p.stock }}
                        </span>
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
                            variant="ghost"
                            size="sm"
                            class="text-xs text-red-500 hover:bg-red-50 hover:text-red-600"
                            @click="deleteProduct(p)"
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
    </AppLayout>
</template>
