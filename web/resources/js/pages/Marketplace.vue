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
    RotateCcw,
    Shirt,
    Cpu,
    Footprints,
    Watch,
    UtensilsCrossed,
    ShoppingBag,
    Tag,
    Percent,
    Loader2,
    ChevronLeft as ChevronLeftIcon,
    ChevronRight as ChevronRightIcon,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
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
import { useProductPagination } from '@/lib/useProductPagination';

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

watch(
    () => props.filters?.search,
    (newVal) => {
        if (newVal !== undefined) {
            searchQ.value = newVal;
        }
    },
);

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

const filteredProducts = computed(() => {
    const q = searchQ.value.trim().toLowerCase();
    const words = q.split(/\s+/).filter(Boolean);

    return displayProducts.value.filter((p) => {
        const matchesCategory =
            selectedCat.value === 'Semua' || p.cat === selectedCat.value;

        if (!matchesCategory) return false;
        if (!q) return true;

        const name = (p.name || '').toLowerCase();
        const cat = (p.cat || '').toLowerCase();
        const tag = (p.tag || '').toLowerCase();
        const store = (p.store || '').toLowerCase();

        const fullText = `${name} ${cat} ${tag} ${store}`;

        return fullText.includes(q) || words.some((w) => fullText.includes(w));
    });
});

const {
    isMobile,
    currentPage,
    totalPages,
    displayedProducts,
    isLoadingMore,
    hasMoreMobile,
    loadMoreTriggerRef,
    setPage,
} = useProductPagination(filteredProducts, {
    itemsPerPage: 8,
    initialMobileCount: 4,
    mobileStep: 4,
});

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

function visitStoreBySlug(store: any) {
    const slug = store.slug || store.name.toLowerCase().replace(/[^a-z0-9]+/g, '-');
    router.visit(`/store/${slug}`);
}

const categoryMenu = [
    { label: 'Semua', icon: ShoppingBag, color: 'bg-black/10 text-black', active: 'bg-black text-white', cat: 'Semua' },
    { label: 'Sneakers', icon: Footprints, color: 'bg-rose-100 text-rose-600', active: 'bg-rose-600 text-white', cat: 'Sneakers' },
    { label: 'Running', icon: Flame, color: 'bg-blue-100 text-blue-600', active: 'bg-blue-600 text-white', cat: 'Running' },
    { label: 'Apparel', icon: Shirt, color: 'bg-violet-100 text-violet-600', active: 'bg-violet-600 text-white', cat: 'Apparel' },
    { label: 'Basketball', icon: Star, color: 'bg-amber-100 text-amber-600', active: 'bg-amber-600 text-white', cat: 'Basketball' },
    { label: 'Accessories', icon: Watch, color: 'bg-emerald-100 text-emerald-600', active: 'bg-emerald-600 text-white', cat: 'Accessories' },
];
</script>

<template>
    <Head title="Nike Official Store — Toko Resmi Nike Indonesia" />

    <StorefrontLayout
        :cartCount="totalCartCount"
        :searchQuery="searchQ"
        @open-cart="isCartOpen = true"
        @search="applySearch"
    >
        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-4 px-3 pt-3 pb-28 sm:gap-6 sm:p-6">

            <!-- ── Hero Banner ── -->
            <div class="relative flex flex-col justify-between gap-4 overflow-hidden rounded-2xl border border-black/10 bg-gradient-to-r from-zinc-900 via-black to-zinc-800 p-5 shadow-md sm:gap-6 sm:rounded-3xl sm:p-8 text-white md:flex-row md:items-center">
                <!-- Decorative background accent -->
                <div class="pointer-events-none absolute -top-16 -right-16 hidden h-64 w-64 rounded-full bg-white/5 md:block" />

                <div class="relative z-10">
                    <p class="mb-1 text-[10px] font-black tracking-widest text-amber-400 uppercase sm:mb-1.5 sm:text-xs flex items-center gap-1.5">
                        <ShieldCheck class="h-4 w-4 text-emerald-400" />
                        Nike Official Store Indonesia · 100% Original
                    </p>
                    <h1 class="text-2xl leading-tight font-black sm:text-4xl lg:text-5xl uppercase tracking-tight">
                        JUST DO IT.
                        <span class="text-amber-400 block text-lg sm:text-2xl font-bold normal-case mt-1">Koleksi Terbaru Sepatu & Clothing Nike</span>
                    </h1>
                    <p class="mt-2 text-xs text-zinc-300 max-w-xl">
                        Dapatkan sepatu sneakers, running, apparel Dri-FIT, & aksesoris Nike original dengan garansi 100% keaslian dan layanan bebas ongkir seluruh Indonesia.
                    </p>
                    <!-- CTA desktop -->
                    <div class="mt-4 hidden gap-2 md:flex">
                        <Badge variant="outline" class="cursor-pointer border-white/20 bg-white/10 text-white hover:bg-white/20">
                            <Flame class="mr-1.5 h-3.5 w-3.5 fill-rose-400 text-rose-400" /> Hot Release
                        </Badge>
                        <Badge variant="outline" class="cursor-pointer border-white/20 bg-white/10 text-white hover:bg-white/20">
                            <Sparkles class="mr-1.5 h-3.5 w-3.5 text-amber-400" /> Garansi Retur 30 Hari
                        </Badge>
                        <Badge variant="outline" class="cursor-pointer border-white/20 bg-white/10 text-white hover:bg-white/20">
                            <Truck class="mr-1.5 h-3.5 w-3.5 text-emerald-400" /> Bebas Ongkir
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

            <!-- ── Category Quick-Menu ── -->
            <Card class="overflow-hidden rounded-2xl border-black/6 shadow-xs">
                <div class="flex items-center gap-2 border-b border-black/6 px-4 py-3">
                    <Flame class="h-4 w-4 fill-rose-500 text-rose-500" />
                    <span class="text-xs font-extrabold text-[#1c1c22]">Jelajahi Kategori</span>
                </div>
                <div class="grid grid-cols-4 divide-x divide-y divide-black/6 sm:grid-cols-8">
                    <button
                        v-for="item in categoryMenu"
                        :key="item.label"
                        @click="item.cat ? setCategory(item.cat) : null"
                        class="group flex flex-col items-center gap-2 px-2 py-4 transition-all hover:bg-[#faf9f6] sm:px-3"
                        :class="selectedCat === item.cat && item.cat ? 'bg-[#fdf0e4]' : ''"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl transition-all group-hover:scale-105"
                            :class="selectedCat === item.cat && item.cat ? item.active : item.color"
                        >
                            <component :is="item.icon" class="h-5 w-5" />
                        </div>
                        <span
                            class="text-center text-[10px] font-semibold leading-tight sm:text-xs"
                            :class="selectedCat === item.cat && item.cat ? 'text-[#e07c28]' : 'text-[#4a4a57]'"
                        >{{ item.label }}</span>
                    </button>
                </div>
            </Card>

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

                    <!-- Nike Official Advantages Sidebar -->
                    <Card class="p-4 bg-zinc-900 text-white border-black/10">
                        <p class="mb-3 text-xs font-black tracking-widest text-amber-400 uppercase">Jaminan Official</p>
                        <div class="flex flex-col gap-3.5 text-xs">
                            <div class="flex items-start gap-2.5">
                                <ShieldCheck class="h-5 w-5 text-emerald-400 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-extrabold text-white">100% Original</p>
                                    <p class="text-[10px] text-zinc-400">Langsung dari Nike Indonesia</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <Truck class="h-5 w-5 text-amber-400 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-extrabold text-white">Bebas Ongkir</p>
                                    <p class="text-[10px] text-zinc-400">Pengiriman cepat seluruh Indonesia</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <RotateCcw class="h-5 w-5 text-rose-400 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-extrabold text-white">Retur 30 Hari</p>
                                    <p class="text-[10px] text-zinc-400">Tukar ukuran atau garansi pengembalian</p>
                                </div>
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
                        v-if="displayedProducts.length > 0"
                        class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5"
                    >
                        <ProductCard
                            v-for="p in displayedProducts"
                            :key="p.id"
                            :product="p"
                            @click="openProductDetail(p)"
                            @add-to-cart="addToCart"
                        />
                    </div>

                    <!-- ── DESKTOP PAGINATION ── -->
                    <div v-if="filteredProducts.length > 0 && totalPages > 1" class="hidden md:flex items-center justify-center gap-1.5 py-4">
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 w-8 p-0 rounded-lg text-xs"
                            :disabled="currentPage === 1"
                            @click="setPage(currentPage - 1)"
                        >
                            <ChevronLeftIcon class="h-4 w-4" />
                        </Button>

                        <Button
                            v-for="page in totalPages"
                            :key="page"
                            :variant="currentPage === page ? 'amber' : 'outline'"
                            size="sm"
                            class="h-8 w-8 p-0 rounded-lg text-xs font-bold"
                            @click="setPage(page)"
                        >
                            {{ page }}
                        </Button>

                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 w-8 p-0 rounded-lg text-xs"
                            :disabled="currentPage === totalPages"
                            @click="setPage(currentPage + 1)"
                        >
                            <ChevronRightIcon class="h-4 w-4" />
                        </Button>
                    </div>

                    <!-- ── MOBILE AUTO-FETCH (INFINITE SCROLL) ── -->
                    <div v-if="filteredProducts.length > 0" class="md:hidden flex flex-col items-center justify-center py-3">
                        <div v-if="isLoadingMore" class="flex items-center gap-2 py-3 text-xs font-bold text-[#e07c28]">
                            <Loader2 class="h-4 w-4 animate-spin text-[#e07c28]" />
                            <span>Memuat produk lainnya...</span>
                        </div>
                        <div v-else-if="hasMoreMobile" ref="loadMoreTriggerRef" class="h-6 w-full" />
                        <p v-else class="text-[11px] text-[#9090a0] py-2">Semua produk sudah ditampilkan</p>
                    </div>

                    <div v-else class="rounded-2xl bg-white py-16 text-center shadow-sm">
                        <Package class="mx-auto mb-2 h-10 w-10 text-[#c8c8d5]" />
                        <p class="text-base font-semibold text-[#4a4a57]">Produk tidak ditemukan</p>
                        <p class="mt-1 text-xs text-[#9090a0]">Coba kata kunci atau kategori lain</p>
                    </div>

                    <!-- Nike Official Special Badges — Mobile -->
                    <div class="lg:hidden">
                        <h2 class="mb-3 text-sm font-bold text-[#1c1c22]">Layanan Nike Official</h2>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="flex items-center gap-2 rounded-xl bg-zinc-900 text-white p-3 border border-black/10">
                                <ShieldCheck class="h-4 w-4 text-emerald-400 shrink-0" />
                                <span class="font-extrabold">100% Original</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-xl bg-zinc-900 text-white p-3 border border-black/10">
                                <Truck class="h-4 w-4 text-amber-400 shrink-0" />
                                <span class="font-extrabold">Bebas Ongkir</span>
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
