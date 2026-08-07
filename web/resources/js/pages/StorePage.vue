<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Star,
    ShieldCheck,
    Truck,
    Package,
    BadgeCheck,
    MapPin,
    CalendarDays,
    ShoppingBag,
    ChevronLeft,
    MessageCircle,
    Share2,
    TrendingUp,
    Search,
    Loader2,
    ChevronLeft as ChevronLeftIcon,
    ChevronRight as ChevronRightIcon,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import CartDrawer from '@/components/marketplace/CartDrawer.vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import ProductDetailModal from '@/components/marketplace/ProductDetailModal.vue';
import type { ProductDetail } from '@/components/marketplace/ProductDetailModal.vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { toast } from '@/components/ui/sonner';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useCart } from '@/lib/useCart';
import { useProductPagination } from '@/lib/useProductPagination';

interface StoreInfo {
    id: number;
    name: string;
    slug: string;
    description: string;
    logo: string | null;
    category: string | null;
    rating: number;
    totalOrders: number;
    totalProducts: number;
    badge: string | null;
    avatar: string;
    avatarHue: number;
    memberSince: string;
    gmv: string;
}

interface Props {
    store: StoreInfo;
    products: any[];
    categories: string[];
    filters: { category: string };
}

const props = defineProps<Props>();

const selectedCat = ref(props.filters?.category || 'Semua');
const searchQuery = ref('');

// Cart
const {
    items: cartItems,
    totalCount: totalCartCount,
    addItem,
    updateQty,
    removeItem,
} = useCart();
const isCartOpen = ref(false);

// Product detail modal
const activeProductModal = ref<ProductDetail | null>(null);

const filteredProducts = computed(() => {
    let list = props.products;
    if (selectedCat.value !== 'Semua') {
        list = list.filter((p) => p.cat === selectedCat.value);
    }
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter((p) => p.name.toLowerCase().includes(q));
    }
    return list;
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
    itemsPerPage: 12,
    initialMobileCount: 6,
    mobileStep: 6,
});

function setCategory(cat: string) {
    selectedCat.value = cat;
    router.get(
        `/store/${props.store.slug}`,
        { category: cat },
        { preserveState: true, preserveScroll: true },
    );
}

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

function updateCartQty(id: number, delta: number) { updateQty(id, delta); }
function removeFromCart(id: number) { removeItem(id); }
function goCheckout() { isCartOpen.value = false; router.visit('/checkout'); }
function openProductDetail(product: any) { activeProductModal.value = product; }

function formatCount(n: number): string {
    if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}jt`;
    if (n >= 1_000) return `${(n / 1_000).toFixed(n >= 10_000 ? 0 : 1)}rb`;
    return String(n);
}

const statCards = computed(() => [
    { label: 'Produk', value: formatCount(props.store.totalProducts), icon: Package, color: 'text-violet-400' },
    { label: 'Rating', value: String(props.store.rating), icon: Star, color: 'text-amber-400', star: true },
    { label: 'Pesanan', value: formatCount(props.store.totalOrders), icon: ShoppingBag, color: 'text-teal-400' },
    { label: 'GMV', value: props.store.gmv, icon: TrendingUp, color: 'text-[#e07c28]' },
]);

const trustItems = [
    { icon: ShieldCheck, color: 'text-[#22a15a]', label: 'Original & Bergaransi' },
    { icon: Truck, color: 'text-[#e07c28]', label: 'Gratis Ongkir' },
    { icon: Package, color: 'text-violet-500', label: 'Packing Aman' },
    { icon: ShoppingBag, color: 'text-blue-500', label: 'Escrow Aman' },
];
</script>

<template>
    <Head :title="`${store.name} — Toko Instan`" />

    <StorefrontLayout
        :cartCount="totalCartCount"
        @open-cart="isCartOpen = true"
        @search="(q: string) => router.visit('/marketplace', { data: { search: q } })"
    >
        <main class="mx-auto flex w-full max-w-[1600px] flex-col">

            <!-- ── Hero Banner ── -->
            <div class="relative isolate overflow-hidden bg-gradient-to-br from-[#16131f] via-[#231e35] to-[#16131f]">
                <!-- Glow orbs -->
                <div class="pointer-events-none absolute -top-24 -right-24 h-80 w-80 rounded-full bg-[#e07c28]/15 blur-3xl" />
                <div class="pointer-events-none absolute -bottom-16 -left-16 h-56 w-56 rounded-full bg-violet-500/10 blur-3xl" />
                <div class="pointer-events-none absolute top-1/2 left-1/2 h-96 w-96 -translate-x-1/2 -translate-y-1/2 rounded-full bg-teal-500/5 blur-3xl" />

                <div class="relative z-10 px-4 pt-5 pb-0 sm:px-8 sm:pt-8">
                    <!-- Back nav -->
                    <button
                        @click="router.visit('/marketplace')"
                        class="mb-5 flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-white/50 transition-colors hover:text-white/90"
                    >
                        <ChevronLeft class="h-3.5 w-3.5" />
                        Kembali ke Marketplace
                    </button>

                    <!-- Profile row -->
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:gap-7">
                        <!-- Avatar + badge -->
                        <div class="relative shrink-0 self-start">
                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-2xl text-2xl font-black shadow-2xl ring-[3px] ring-white/10 sm:h-[88px] sm:w-[88px] sm:text-3xl"
                                :style="{
                                    background: `linear-gradient(145deg, hsl(${store.avatarHue}, 60%, 55%), hsl(${store.avatarHue}, 60%, 38%))`,
                                    color: 'white',
                                    textShadow: '0 2px 8px rgba(0,0,0,0.3)',
                                }"
                            >
                                {{ store.avatar }}
                            </div>
                            <span class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full border-2 border-[#16131f] bg-teal-400 shadow-md">
                                <BadgeCheck class="h-3.5 w-3.5 text-[#16131f]" />
                            </span>
                        </div>

                        <!-- Store info -->
                        <div class="flex flex-1 flex-col gap-1.5">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h1 class="text-xl font-black leading-tight text-white sm:text-2xl lg:text-3xl">
                                    {{ store.name }}
                                </h1>
                                <Badge
                                    v-if="store.badge"
                                    :class="store.badge === 'top'
                                        ? 'bg-gradient-to-r from-[#e07c28] to-[#f0933c] border-transparent text-white'
                                        : 'bg-gradient-to-r from-violet-500 to-violet-400 border-transparent text-white'"
                                    class="h-5 px-2 text-[9px] font-black uppercase tracking-widest"
                                >⭐ {{ store.badge }}</Badge>
                            </div>

                            <p class="max-w-lg text-xs leading-relaxed text-white/45 sm:text-sm">
                                {{ store.description }}
                            </p>

                            <!-- Meta pills -->
                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1.5">
                                <span class="flex items-center gap-1 text-[11px] text-white/35">
                                    <MapPin class="h-3 w-3" /> Jakarta, Indonesia
                                </span>
                                <span class="flex items-center gap-1 text-[11px] text-white/35">
                                    <CalendarDays class="h-3 w-3" /> Bergabung {{ store.memberSince }}
                                </span>
                                <span class="flex items-center gap-1 text-[11px] text-white/35">
                                    <MessageCircle class="h-3 w-3" /> Respons &lt; 1 jam
                                </span>
                            </div>

                            <!-- CTA buttons -->
                            <div class="mt-3 flex gap-2">
                                <Button variant="amber" size="sm" class="gap-1.5 text-xs font-bold shadow-lg shadow-[#e07c28]/25">
                                    <MessageCircle class="h-3.5 w-3.5" /> Chat Penjual
                                </Button>
                                <Button size="sm" class="gap-1.5 border border-white/15 bg-white/8 text-xs font-semibold text-white/80 hover:bg-white/14 hover:text-white">
                                    <Share2 class="h-3.5 w-3.5" /> Bagikan
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- ── Stat Cards Strip ── -->
                    <div class="mt-7 grid grid-cols-4 divide-x divide-white/8 overflow-hidden rounded-t-2xl border border-b-0 border-white/8 bg-white/[0.03] backdrop-blur-sm">
                        <div
                            v-for="stat in statCards"
                            :key="stat.label"
                            class="flex flex-col items-center gap-1 px-3 py-4 sm:px-5"
                        >
                            <component
                                :is="stat.icon"
                                class="h-4 w-4 sm:h-5 sm:w-5"
                                :class="[stat.color, stat.star ? 'fill-amber-400' : '']"
                            />
                            <p class="font-mono text-base font-black text-white sm:text-xl" :class="stat.label === 'GMV' ? 'text-[#e07c28]' : ''">
                                {{ stat.value }}
                            </p>
                            <p class="text-[9px] font-semibold uppercase tracking-widest text-white/35 sm:text-[10px]">
                                {{ stat.label }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Trust Bar ── -->
            <div class="flex items-center gap-0 overflow-x-auto border-b border-black/8 bg-white">
                <div
                    v-for="(item, i) in trustItems"
                    :key="i"
                    class="flex shrink-0 items-center gap-2 border-r border-black/6 px-4 py-3 last:border-r-0 sm:px-5"
                >
                    <component :is="item.icon" class="h-4 w-4 shrink-0" :class="item.color" />
                    <span class="text-[11px] font-semibold whitespace-nowrap text-[#4a4a57] sm:text-xs">{{ item.label }}</span>
                </div>
            </div>

            <!-- ── Products Section ── -->
            <div class="flex flex-col gap-5 bg-[#f5f4f0] p-3 sm:p-5 lg:p-7">

                <!-- Header row -->
                <Card class="rounded-2xl border-black/6 shadow-xs">
                    <CardContent class="p-4 sm:p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle class="text-sm font-extrabold text-[#1c1c22] sm:text-base">
                                    Produk dari {{ store.name }}
                                </CardTitle>
                                <CardDescription class="mt-0.5 text-xs text-[#9090a0]">
                                    {{ filteredProducts.length }} produk ditemukan
                                </CardDescription>
                            </div>

                            <!-- Search within store -->
                            <div class="relative flex-shrink-0 sm:w-52">
                                <Search class="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-[#9090a0]" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari produk di toko..."
                                    class="h-9 w-full rounded-xl border border-black/10 bg-[#f5f4f0] pl-8 pr-3 text-xs font-medium text-[#1c1c22] outline-none placeholder:text-[#9090a0] focus:border-[#e07c28] focus:ring-1 focus:ring-[#e07c2830] transition-all"
                                />
                            </div>
                        </div>

                        <!-- Category Pills -->
                        <Separator class="my-3.5 opacity-50" />
                        <div class="w-full overflow-x-auto">
                            <div class="flex w-max gap-1.5 pb-0.5">
                                <Button
                                    v-for="cat in categories"
                                    :key="cat"
                                    :variant="selectedCat === cat ? 'amber' : 'outline'"
                                    size="sm"
                                    class="h-7 shrink-0 rounded-full px-3.5 text-[11px] font-semibold transition-all"
                                    @click="setCategory(cat)"
                                >
                                    {{ cat }}
                                    <span v-if="selectedCat === cat" class="ml-1 rounded-full bg-white/25 px-1 text-[9px]">
                                        {{ filteredProducts.length }}
                                    </span>
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Product Grid -->
                <div
                    v-if="displayedProducts.length > 0"
                    class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
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

                <!-- Empty State -->
                <Card v-else class="rounded-2xl border-black/6 shadow-xs">
                    <CardContent class="flex flex-col items-center gap-3 py-16 text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f5f4f0]">
                            <Package class="h-8 w-8 text-[#c8c8d5]" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-[#4a4a57]">Tidak ada produk</p>
                            <p class="mt-1 text-xs text-[#9090a0]">
                                {{ searchQuery ? `Produk "${searchQuery}" tidak ditemukan` : 'Toko ini belum memiliki produk di kategori ini' }}
                            </p>
                        </div>
                        <Button v-if="searchQuery" variant="outline" size="sm" class="text-xs" @click="searchQuery = ''">
                            Hapus pencarian
                        </Button>
                    </CardContent>
                </Card>
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
