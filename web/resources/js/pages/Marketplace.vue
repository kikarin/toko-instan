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
    ShieldCheck,
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
        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-4 p-3 sm:gap-6 sm:p-6">

            <!-- ── Hero Banner ── -->
            <div class="relative flex flex-col justify-between gap-4 overflow-hidden rounded-2xl border border-[#e07c2825] bg-gradient-to-r from-[#fdf0e4] via-[#fef8f0] to-[#f5f4f0] p-5 shadow-sm sm:gap-6 sm:rounded-3xl sm:p-8 md:flex-row md:items-center">
                <!-- Decorative circles — desktop only -->
                <div class="pointer-events-none absolute -top-16 -right-16 hidden h-64 w-64 rounded-full bg-[#e07c28]/8 md:block" />
                <div class="pointer-events-none absolute top-4 -right-8 hidden h-36 w-36 rounded-full bg-[#e07c28]/6 md:block" />

                <div class="relative z-10">
                    <p class="mb-1 text-[10px] font-extrabold tracking-widest text-[#e07c28] uppercase sm:mb-1.5 sm:text-xs">
                        Platform Belanja Pembeli
                    </p>
                    <h1 class="text-xl leading-tight font-extrabold text-[#1c1c22] sm:text-3xl lg:text-4xl">
                        Temukan produk terbaik
                        <span class="text-[#e07c28]"> dari ribuan toko terpercaya</span>
                    </h1>
                    <p class="mt-1.5 text-[10px] text-[#9090a0] sm:mt-2 sm:text-xs">
                        {{ props.stats?.total_products || '8.341' }} produk ·
                        {{ props.stats?.active_stores || '1.240' }} toko aktif ·
                        54.921 pengunjung hari ini
                    </p>
                    <!-- CTA desktop -->
                    <div class="mt-4 hidden gap-2 md:flex">
                        <Badge variant="outline" class="cursor-pointer border-black/10 bg-white px-3 py-1.5 text-xs text-[#4a4a57] hover:bg-white/80">
                            <Flame class="mr-1.5 h-3.5 w-3.5 fill-rose-500 text-rose-500" /> Flash Sale
                        </Badge>
                        <Badge variant="outline" class="cursor-pointer border-black/10 bg-white px-3 py-1.5 text-xs text-[#4a4a57] hover:bg-white/80">
                            <Sparkles class="mr-1.5 h-3.5 w-3.5 text-amber-500" /> Produk Baru
                        </Badge>
                        <Badge variant="outline" class="cursor-pointer border-black/10 bg-white px-3 py-1.5 text-xs text-[#4a4a57] hover:bg-white/80">
                            <Truck class="mr-1.5 h-3.5 w-3.5 text-teal-500" /> Gratis Ongkir
                        </Badge>
                    </div>
                </div>

                <!-- Mobile filter badges -->
                <div class="relative z-10 flex flex-wrap items-center gap-2 md:hidden">
                    <Badge variant="outline" class="cursor-pointer border-black/10 bg-white text-[#4a4a57]">
                        <Flame class="mr-1 h-3 w-3 fill-rose-500 text-rose-500" /> Flash Sale
                    </Badge>
                    <Badge variant="outline" class="cursor-pointer border-black/10 bg-white text-[#4a4a57]">
                        <Sparkles class="mr-1 h-3 w-3 text-amber-500" /> Produk Baru
                    </Badge>
                    <Badge variant="outline" class="cursor-pointer border-black/10 bg-white text-[#4a4a57]">
                        <Truck class="mr-1 h-3 w-3 text-teal-500" /> Gratis Ongkir
                    </Badge>
                </div>

                <!-- Desktop hero right illustration -->
                <div class="relative z-10 hidden flex-col items-end gap-3 md:flex">
                    <div class="rounded-2xl border border-[#e07c2825] bg-white p-4 shadow-sm">
                        <p class="mb-1 text-[10px] text-[#9090a0] uppercase tracking-widest">Belanja Aman</p>
                        <div class="flex items-center gap-2 text-sm font-bold text-[#1c1c22]">
                            <ShieldCheck class="h-5 w-5 text-[#22a15a]" /> Escrow & Buyer Protection
                        </div>
                        <p class="mt-1 text-[10px] text-[#9090a0]">Uang kembali jika barang tidak sesuai</p>
                    </div>
                    <div class="flex gap-2">
                        <div class="rounded-xl border border-black/8 bg-white px-3 py-2 text-center shadow-xs">
                            <p class="font-mono text-lg font-extrabold text-[#e07c28]">{{ props.stats?.total_products || '8.3rb' }}</p>
                            <p class="text-[10px] text-[#9090a0]">Produk</p>
                        </div>
                        <div class="rounded-xl border border-black/8 bg-white px-3 py-2 text-center shadow-xs">
                            <p class="font-mono text-lg font-extrabold text-[#6d4fc2]">{{ props.stats?.active_stores || '1.2rb' }}</p>
                            <p class="text-[10px] text-[#9090a0]">Toko</p>
                        </div>
                        <div class="rounded-xl border border-black/8 bg-white px-3 py-2 text-center shadow-xs">
                            <p class="font-mono text-lg font-extrabold text-[#22a15a]">54rb+</p>
                            <p class="text-[10px] text-[#9090a0]">Pembeli</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Stats Strip ── -->
            <div class="grid grid-cols-2 gap-2 sm:gap-3 lg:grid-cols-4">
                <Card class="flex items-center gap-3 p-3 sm:gap-3.5 sm:p-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#e07c2830] bg-[#e07c281a] text-[#e07c28] sm:h-10 sm:w-10">
                        <Package class="h-4 w-4 sm:h-5 sm:w-5" />
                    </div>
                    <div>
                        <p class="font-mono text-base leading-none font-extrabold text-[#1c1c22] sm:text-xl">{{ props.stats?.total_products || '8.341' }}</p>
                        <p class="mt-1 text-[10px] text-[#9090a0] sm:text-xs">Total Produk</p>
                    </div>
                </Card>
                <Card class="flex items-center gap-3 p-3 sm:gap-3.5 sm:p-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#3b82f630] bg-[#3b82f61a] text-[#3b82f6] sm:h-10 sm:w-10">
                        <StoreIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                    </div>
                    <div>
                        <p class="font-mono text-base leading-none font-extrabold text-[#1c1c22] sm:text-xl">{{ props.stats?.active_stores || '1.240' }}</p>
                        <p class="mt-1 text-[10px] text-[#9090a0] sm:text-xs">Toko Aktif</p>
                    </div>
                </Card>
                <Card class="flex items-center gap-3 p-3 sm:gap-3.5 sm:p-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#22a15a30] bg-[#22a15a1a] text-[#22a15a] sm:h-10 sm:w-10">
                        <TrendingUp class="h-4 w-4 sm:h-5 sm:w-5" />
                    </div>
                    <div>
                        <p class="font-mono text-base leading-none font-extrabold text-[#1c1c22] sm:text-xl">312</p>
                        <p class="mt-1 text-[10px] text-[#9090a0] sm:text-xs">Terjual Hari Ini</p>
                    </div>
                </Card>
                <Card class="flex items-center gap-3 p-3 sm:gap-3.5 sm:p-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#6d4fc230] bg-[#6d4fc21a] text-[#6d4fc2] sm:h-10 sm:w-10">
                        <Users class="h-4 w-4 sm:h-5 sm:w-5" />
                    </div>
                    <div>
                        <p class="font-mono text-base leading-none font-extrabold text-[#1c1c22] sm:text-xl">54.921</p>
                        <p class="mt-1 text-[10px] text-[#9090a0] sm:text-xs">Pembeli Aktif</p>
                    </div>
                </Card>
            </div>

            <!-- ── Main content: sidebar (desktop) + product grid ── -->
            <div class="flex gap-6">

                <!-- ─ Sidebar Filter — DESKTOP ONLY ─ -->
                <aside class="hidden w-56 shrink-0 flex-col gap-4 lg:flex xl:w-64">

                    <!-- Category filter -->
                    <Card class="p-4">
                        <p class="mb-3 text-xs font-extrabold tracking-widest text-[#9090a0] uppercase">Kategori</p>
                        <div class="flex flex-col gap-1">
                            <button
                                v-for="cat in displayCategories"
                                :key="cat"
                                @click="setCategory(cat)"
                                class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm transition-all"
                                :class="selectedCat === cat ? 'bg-[#e07c28] font-bold text-white shadow-sm' : 'text-[#4a4a57] hover:bg-[#f5f4f0]'"
                            >
                                <span>{{ cat }}</span>
                                <span
                                    v-if="selectedCat === cat"
                                    class="text-[10px] font-bold text-white/80"
                                >
                                    {{ filteredProducts.length }}
                                </span>
                            </button>
                        </div>
                    </Card>

                    <!-- Price Range filter -->
                    <Card class="p-4">
                        <p class="mb-3 text-xs font-extrabold tracking-widest text-[#9090a0] uppercase">Harga</p>
                        <div class="flex flex-col gap-2">
                            <button
                                v-for="range in [
                                    { label: 'Semua Harga', val: 'all' },
                                    { label: '< Rp 100rb', val: '0-100000' },
                                    { label: 'Rp 100rb – 300rb', val: '100000-300000' },
                                    { label: 'Rp 300rb – 1jt', val: '300000-1000000' },
                                    { label: '> Rp 1jt', val: '1000000-999999999' },
                                ]"
                                :key="range.val"
                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm text-[#4a4a57] transition-all hover:bg-[#f5f4f0]"
                            >
                                <div class="h-3.5 w-3.5 rounded-full border-2 border-black/20 bg-white" />
                                {{ range.label }}
                            </button>
                        </div>
                    </Card>

                    <!-- Rating filter -->
                    <Card class="p-4">
                        <p class="mb-3 text-xs font-extrabold tracking-widest text-[#9090a0] uppercase">Rating</p>
                        <div class="flex flex-col gap-2">
                            <button
                                v-for="r in [5, 4, 3]"
                                :key="r"
                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left transition-all hover:bg-[#f5f4f0]"
                            >
                                <div class="flex gap-0.5">
                                    <Star v-for="i in 5" :key="i" class="h-3 w-3" :class="i <= r ? 'fill-amber-400 stroke-amber-400' : 'stroke-black/15 fill-none'" />
                                </div>
                                <span class="text-xs text-[#4a4a57]">ke atas</span>
                            </button>
                        </div>
                    </Card>

                    <!-- Top Stores Sidebar -->
                    <Card class="p-4">
                        <p class="mb-3 text-xs font-extrabold tracking-widest text-[#9090a0] uppercase">Toko Unggulan</p>
                        <div class="flex flex-col gap-3">
                            <div v-for="(store, i) in displayStores.slice(0, 4)" :key="i" class="flex items-center gap-2.5 cursor-pointer rounded-xl px-2 py-1.5 transition-all hover:bg-[#f5f4f0]">
                                <Avatar :fallback="store.avatar" :hue="store.hue" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-bold text-[#1c1c22]">{{ store.name }}</p>
                                    <div class="flex items-center gap-1">
                                        <Star class="h-2.5 w-2.5 fill-amber-400 stroke-amber-400" />
                                        <span class="text-[10px] text-[#9090a0]">{{ store.rating }}</span>
                                    </div>
                                </div>
                                <Badge v-if="store.badge" :variant="store.badge === 'top' ? 'amberSolid' : 'violetSolid'" class="px-1.5 py-0 text-[8px] uppercase">{{ store.badge }}</Badge>
                            </div>
                        </div>
                    </Card>
                </aside>

                <!-- ─ Right Column: category chips + grid ─ -->
                <div class="flex min-w-0 flex-1 flex-col gap-4">

                    <!-- Categories pills — horizontal scroll on mobile, shown as sidebar on desktop -->
                    <div class="w-full overflow-x-auto lg:hidden">
                        <div class="flex w-max gap-2 pb-1">
                            <Button v-for="cat in displayCategories" :key="cat" :variant="selectedCat === cat ? 'amber' : 'outline'" size="sm" class="shrink-0 rounded-full text-xs font-semibold" @click="setCategory(cat)">
                                {{ cat }}
                            </Button>
                        </div>
                    </div>

                    <!-- Product Grid header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-[#1c1c22] sm:text-base">
                                {{ selectedCat === 'Semua' ? 'Semua Produk' : selectedCat }}
                                <span class="ml-1.5 text-xs font-normal text-[#9090a0]">{{ filteredProducts.length }} produk</span>
                            </h2>
                        </div>
                        <!-- Sort desktop -->
                        <div class="hidden items-center gap-2 text-xs text-[#9090a0] lg:flex">
                            <span>Urutkan:</span>
                            <button class="rounded-lg border border-black/10 bg-white px-3 py-1.5 font-semibold text-[#1c1c22] hover:bg-[#f5f4f0]">Terpopuler</button>
                            <button class="rounded-lg border border-black/10 px-3 py-1.5 hover:bg-[#f5f4f0]">Terbaru</button>
                            <button class="rounded-lg border border-black/10 px-3 py-1.5 hover:bg-[#f5f4f0]">Harga ↑</button>
                            <button class="rounded-lg border border-black/10 px-3 py-1.5 hover:bg-[#f5f4f0]">Harga ↓</button>
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div
                        v-if="filteredProducts.length > 0"
                        class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5"
                    >
                        <ProductCard
                            v-for="p in filteredProducts"
                            :key="p.id"
                            :product="p"
                            @click="openProductDetail(p)"
                        />
                    </div>

                    <div v-else class="rounded-2xl bg-white py-16 text-center shadow-sm">
                        <Package class="mx-auto mb-2 h-10 w-10 text-[#c8c8d5]" />
                        <p class="text-base font-semibold text-[#4a4a57]">Produk tidak ditemukan</p>
                        <p class="mt-1 text-xs text-[#9090a0]">Coba kata kunci atau kategori lain</p>
                    </div>

                    <!-- Featured Stores — only shown below product grid on mobile, hidden on desktop (shown in sidebar) -->
                    <div class="lg:hidden">
                        <h2 class="mb-3 text-sm font-bold text-[#1c1c22]">Toko Rekomendasi</h2>
                        <div class="w-full overflow-x-auto">
                            <div class="flex w-max gap-3 pb-2">
                                <Card v-for="(store, i) in displayStores" :key="i" class="flex w-36 shrink-0 cursor-pointer flex-col items-center p-4 text-center transition-all hover:-translate-y-1 hover:shadow-md">
                                    <Avatar :fallback="store.avatar" :hue="store.hue" size="lg" class="mb-3" />
                                    <p class="mb-0.5 text-xs font-bold text-[#1c1c22]">{{ store.name }}</p>
                                    <p class="mb-2 text-[10px] text-[#9090a0]">{{ store.orders }} pesanan</p>
                                    <div class="flex items-center gap-1 font-mono text-xs font-semibold text-amber-500">
                                        <Star class="h-3 w-3 fill-amber-400 stroke-amber-400" />{{ store.rating }}
                                    </div>
                                    <Badge v-if="store.badge" :variant="store.badge === 'top' ? 'amberSolid' : 'violetSolid'" class="mt-3 px-2 py-0.5 text-[9px] uppercase">{{ store.badge }}</Badge>
                                </Card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Cart Drawer -->
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
