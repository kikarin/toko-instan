<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Flame,
    Sparkles,
    Truck,
    Package,
    Store as StoreIcon,
    TrendingUp,
    Users,
    Star,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import CartDrawer from '@/components/marketplace/CartDrawer.vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import ProductDetailModal from '@/components/marketplace/ProductDetailModal.vue';
import type { ProductDetail } from '@/components/marketplace/ProductDetailModal.vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { toast } from '@/components/ui/sonner';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useCart } from '@/lib/useCart';

interface Props {
    products?: any[];
    stores?: any[];
    categories?: string[];
    stats?: any;
    filters?: { search: string; category: string };
}

const props = defineProps<Props>();

const searchQ = ref(props.filters?.search || '');
const selectedCat = ref(props.filters?.category || 'Semua');

// Reactive Cart state (persisted in localStorage)
const {
    items: cartItems,
    totalCount: totalCartCount,
    addItem,
    updateQty,
    removeItem,
} = useCart();
const isCartOpen = ref(false);

// Selected product detail modal state
const activeProductModal = ref<ProductDetail | null>(null);

const defaultProducts = [
    {
        id: 1,
        name: 'Kemeja Batik Tenun Premium',
        price: 'Rp 285.000',
        priceNum: 285000,
        sold: 1240,
        rating: 4.9,
        store: 'NovaBatik Studio',
        img: 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=400&h=400&fit=crop&auto=format',
        tag: 'Bestseller',
        cat: 'Fashion',
    },
    {
        id: 2,
        name: 'Sneakers Casual Kulit Asli',
        price: 'Rp 599.000',
        priceNum: 599000,
        sold: 847,
        rating: 4.8,
        store: 'Mode Nusantara',
        img: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&auto=format',
        tag: 'Baru',
        cat: 'Sepatu',
    },
    {
        id: 3,
        name: 'Mechanical Keyboard TKL 75%',
        price: 'Rp 890.000',
        priceNum: 890000,
        sold: 632,
        rating: 4.7,
        store: 'Jaya Elektronik',
        img: 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=400&h=400&fit=crop&auto=format',
        tag: 'Hot',
        cat: 'Elektronik',
    },
    {
        id: 4,
        name: 'Tas Kulit Selempang Minimalis',
        price: 'Rp 420.000',
        priceNum: 420000,
        sold: 921,
        rating: 4.8,
        store: 'KuliKain Official',
        img: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=400&fit=crop&auto=format',
        tag: 'Bestseller',
        cat: 'Aksesoris',
    },
    {
        id: 5,
        name: 'Matcha Latte Premium 200gr',
        price: 'Rp 145.000',
        priceNum: 145000,
        sold: 2103,
        rating: 4.9,
        store: 'Warung Digital ID',
        img: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=400&fit=crop&auto=format',
        tag: 'Hot',
        cat: 'Kuliner',
    },
    {
        id: 6,
        name: 'Kacamata Frame Titanium',
        price: 'Rp 760.000',
        priceNum: 760000,
        sold: 438,
        rating: 4.7,
        store: 'Mode Nusantara',
        img: 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=400&fit=crop&auto=format',
        tag: 'Baru',
        cat: 'Aksesoris',
    },
    {
        id: 7,
        name: 'Headphone Over-ear Wireless',
        price: 'Rp 1.250.000',
        priceNum: 1250000,
        sold: 512,
        rating: 4.8,
        store: 'Jaya Elektronik',
        img: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop&auto=format',
        tag: null,
        cat: 'Elektronik',
    },
    {
        id: 8,
        name: 'Celana Linen Wide Leg',
        price: 'Rp 320.000',
        priceNum: 320000,
        sold: 763,
        rating: 4.6,
        store: 'NovaBatik Studio',
        img: 'https://images.unsplash.com/photo-1594938298603-c8148c4b4b58?w=400&h=400&fit=crop&auto=format',
        tag: null,
        cat: 'Fashion',
    },
];

const displayProducts = computed(() => props.products || defaultProducts);

const defaultStores = [
    {
        name: 'NovaBatik Studio',
        orders: 412,
        rating: 4.9,
        badge: 'top' as const,
        avatar: 'NB',
        hue: 220,
    },
    {
        name: 'KuliKain Official',
        orders: 318,
        rating: 4.8,
        badge: 'pro' as const,
        avatar: 'KK',
        hue: 280,
    },
    {
        name: 'Jaya Elektronik',
        orders: 287,
        rating: 4.7,
        badge: null,
        avatar: 'JE',
        hue: 190,
    },
    {
        name: 'Warung Digital ID',
        orders: 234,
        rating: 4.6,
        badge: null,
        avatar: 'WD',
        hue: 150,
    },
    {
        name: 'Mode Nusantara',
        orders: 198,
        rating: 4.5,
        badge: null,
        avatar: 'MN',
        hue: 30,
    },
];

const displayStores = computed(() => props.stores || defaultStores);
const displayCategories = computed(
    () =>
        props.categories || [
            'Semua',
            'Fashion',
            'Elektronik',
            'Sepatu',
            'Aksesoris',
            'Kuliner',
        ],
);

const filteredProducts = computed(() =>
    displayProducts.value.filter(
        (p) =>
            (selectedCat.value === 'Semua' || p.cat === selectedCat.value) &&
            (!searchQ.value ||
                p.name.toLowerCase().includes(searchQ.value.toLowerCase()) ||
                p.store.toLowerCase().includes(searchQ.value.toLowerCase())),
    ),
);

function applySearch(q?: string) {
    if (q !== undefined) {
        searchQ.value = q;
    }

    router.get(
        '/marketplace',
        { search: searchQ.value, category: selectedCat.value },
        { preserveState: true },
    );
}

function setCategory(cat: string) {
    selectedCat.value = cat;
    router.get(
        '/marketplace',
        { search: searchQ.value, category: cat },
        { preserveState: true },
    );
}

// Cart operations (backed by persistent composable)
function addToCart(product: ProductDetail, addQty = 1) {
    const rawPrice =
        product.priceNum ||
        parseInt(product.price.replace(/[^\d]/g, ''), 10) ||
        100000;

    addItem(
        {
            id: product.id,
            name: product.name,
            price: rawPrice,
            formattedPrice: product.price,
            img: product.img,
            store: product.store,
            qty: addQty,
        },
        addQty,
    );

    isCartOpen.value = true;
    toast.success(`${product.name} ditambahkan ke keranjang!`);
}

function updateCartQty(id: number, delta: number) {
    updateQty(id, delta);
}

function removeFromCart(id: number) {
    removeItem(id);
}

function goCheckout() {
    isCartOpen.value = false;
    router.visit('/checkout');
}

function openProductDetail(product: any) {
    activeProductModal.value = product;
}
</script>

<template>
    <Head title="Marketplace Pembeli - Toko Instan" />

    <StorefrontLayout
        :cartCount="totalCartCount"
        :searchQuery="searchQ"
        @open-cart="isCartOpen = true"
        @search="applySearch"
    >
        <main
            class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-4 sm:p-6"
        >
            <!-- Hero Banner -->
            <div
                class="relative flex flex-col items-center justify-between gap-6 overflow-hidden rounded-3xl border border-[#e07c2825] bg-gradient-to-r from-[#fdf0e4] via-[#fef8f0] to-[#f5f4f0] p-8 shadow-sm md:flex-row"
            >
                <div class="relative z-10">
                    <p
                        class="mb-1.5 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                    >
                        Platform Belanja Pembeli
                    </p>
                    <h1
                        class="text-3xl leading-tight font-extrabold text-[#1c1c22]"
                    >
                        Temukan produk terbaik <br />
                        <span class="text-[#e07c28]"
                            >dari ribuan toko terpercaya</span
                        >
                    </h1>
                    <p class="mt-2 text-xs text-[#9090a0]">
                        {{ props.stats?.total_products || '8.341' }} produk ·
                        {{ props.stats?.active_stores || '1.240' }} toko aktif ·
                        54.921 pengunjung hari ini
                    </p>
                </div>

                <div
                    class="relative z-10 flex w-full flex-col items-end gap-3 md:w-auto"
                >
                    <!-- Filter tags -->
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            variant="outline"
                            class="cursor-pointer border-black/10 bg-white text-[#4a4a57] hover:bg-white/80"
                        >
                            <Flame
                                class="mr-1 h-3 w-3 fill-rose-500 text-rose-500"
                            />
                            Flash Sale
                        </Badge>
                        <Badge
                            variant="outline"
                            class="cursor-pointer border-black/10 bg-white text-[#4a4a57] hover:bg-white/80"
                        >
                            <Sparkles class="mr-1 h-3 w-3 text-amber-500" />
                            Produk Baru
                        </Badge>
                        <Badge
                            variant="outline"
                            class="cursor-pointer border-black/10 bg-white text-[#4a4a57] hover:bg-white/80"
                        >
                            <Truck class="mr-1 h-3 w-3 text-teal-500" /> Gratis
                            Ongkir
                        </Badge>
                    </div>
                </div>
            </div>

            <!-- Buyer Stats Strip -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Card class="flex items-center gap-3.5 p-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#e07c2830] bg-[#e07c281a] text-[#e07c28]"
                    >
                        <Package class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="font-mono text-xl leading-none font-extrabold text-[#1c1c22]"
                        >
                            {{ props.stats?.total_products || '8.341' }}
                        </p>
                        <p class="mt-1 text-xs text-[#9090a0]">
                            Total Produk Pilihan
                        </p>
                    </div>
                </Card>

                <Card class="flex items-center gap-3.5 p-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#3b82f630] bg-[#3b82f61a] text-[#3b82f6]"
                    >
                        <StoreIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="font-mono text-xl leading-none font-extrabold text-[#1c1c22]"
                        >
                            {{ props.stats?.active_stores || '1.240' }}
                        </p>
                        <p class="mt-1 text-xs text-[#9090a0]">
                            Toko Resmi Aktif
                        </p>
                    </div>
                </Card>

                <Card class="flex items-center gap-3.5 p-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#22a15a30] bg-[#22a15a1a] text-[#22a15a]"
                    >
                        <TrendingUp class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="font-mono text-xl leading-none font-extrabold text-[#1c1c22]"
                        >
                            312
                        </p>
                        <p class="mt-1 text-xs text-[#9090a0]">
                            Produk Terjual Hari Ini
                        </p>
                    </div>
                </Card>

                <Card class="flex items-center gap-3.5 p-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#6d4fc230] bg-[#6d4fc21a] text-[#6d4fc2]"
                    >
                        <Users class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="font-mono text-xl leading-none font-extrabold text-[#1c1c22]"
                        >
                            54.921
                        </p>
                        <p class="mt-1 text-xs text-[#9090a0]">Pembeli Aktif</p>
                    </div>
                </Card>
            </div>

            <!-- Categories pills -->
            <div class="flex flex-wrap items-center gap-2">
                <Button
                    v-for="cat in displayCategories"
                    :key="cat"
                    :variant="selectedCat === cat ? 'amber' : 'outline'"
                    size="sm"
                    class="rounded-full text-xs font-semibold"
                    @click="setCategory(cat)"
                >
                    {{ cat }}
                </Button>
            </div>

            <!-- Product Grid -->
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-base font-bold text-[#1c1c22]">
                        {{
                            selectedCat === 'Semua'
                                ? 'Katalog Produk Pembeli'
                                : selectedCat
                        }}
                        <span class="ml-2 text-xs font-normal text-[#9090a0]">
                            {{ filteredProducts.length }} produk ditemukan
                        </span>
                    </h2>
                </div>

                <div
                    v-if="filteredProducts.length > 0"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4"
                >
                    <ProductCard
                        v-for="p in filteredProducts"
                        :key="p.id"
                        :product="p"
                        @click="openProductDetail(p)"
                    />
                </div>

                <div
                    v-else
                    class="rounded-3xl border border-black/5 bg-white py-16 text-center"
                >
                    <Package class="mx-auto mb-2 h-10 w-10 text-[#c8c8d5]" />
                    <p class="text-base font-semibold text-[#4a4a57]">
                        Produk tidak ditemukan
                    </p>
                    <p class="mt-1 text-xs text-[#9090a0]">
                        Coba kata kunci atau kategori lain
                    </p>
                </div>
            </div>

            <!-- Featured Stores Grid -->
            <div>
                <h2 class="mb-3 text-base font-bold text-[#1c1c22]">
                    Toko Rekomendasi Pembeli
                </h2>
                <div
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5"
                >
                    <Card
                        v-for="(store, i) in displayStores"
                        :key="i"
                        class="flex cursor-pointer flex-col items-center p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-black/15 hover:shadow-md"
                    >
                        <Avatar
                            :fallback="store.avatar"
                            :hue="store.hue"
                            size="lg"
                            class="mb-3"
                        />
                        <p class="mb-0.5 text-xs font-bold text-[#1c1c22]">
                            {{ store.name }}
                        </p>
                        <p class="mb-2 text-[10px] text-[#9090a0]">
                            {{ store.orders }} pesanan sukses
                        </p>
                        <div
                            class="flex items-center justify-center gap-1 font-mono text-xs font-semibold text-amber-500"
                        >
                            <Star
                                class="h-3 w-3 fill-amber-400 stroke-amber-400"
                            />
                            {{ store.rating }}
                        </div>
                        <Badge
                            v-if="store.badge"
                            :variant="
                                store.badge === 'top'
                                    ? 'amberSolid'
                                    : 'violetSolid'
                            "
                            class="mt-3 px-2 py-0.5 text-[9px] uppercase"
                        >
                            {{ store.badge }}
                        </Badge>
                    </Card>
                </div>
            </div>
        </main>

        <!-- Cart Drawer Slide-over -->
        <CartDrawer
            :isOpen="isCartOpen"
            :items="cartItems"
            @close="isCartOpen = false"
            @update-qty="updateCartQty"
            @remove-item="removeFromCart"
            @checkout="goCheckout"
        />

        <!-- Product Detail Modal -->
        <ProductDetailModal
            :product="activeProductModal"
            @close="activeProductModal = null"
            @add-to-cart="addToCart"
        />
    </StorefrontLayout>
</template>
