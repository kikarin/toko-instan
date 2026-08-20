<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import emblaCarouselVue from 'embla-carousel-vue';
import Autoplay from 'embla-carousel-autoplay';
import Fade from 'embla-carousel-fade';
import emblaCarouselVue from 'embla-carousel-vue';
import {
    Flame,
    Sparkles,
    Truck,
    Package,
    TrendingUp,
    Users,
    Star,
    ShieldCheck,
    RotateCcw,
    Loader2,
    ChevronLeft as ChevronLeftIcon,
    ChevronRight as ChevronRightIcon,
    ShoppingCart,
    LayoutGrid,
    ArrowUp,
    ArrowDown,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import ProductDetailModal from '@/components/marketplace/ProductDetailModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { useMarketplaceCart } from '@/composables/useMarketplaceCart';
import { useMarketplaceCatalog } from '@/composables/useMarketplaceCatalog';
import { useMarketplaceFilters } from '@/composables/useMarketplaceFilters';
import { useProductPagination } from '@/composables/useProductPagination';
import type { ProductDetail } from '@/types/product';
import type { StorefrontInfo } from '@/types/store';

const page = usePage();
const seo = computed(() => (page.props.seo as SeoMeta | undefined) ?? null);
const storefront = computed<StorefrontInfo | null>(
    () => (page.props.store as StorefrontInfo | undefined) ?? null,
);

const activeBanners = computed(() => {
    if (storefront.value?.banner_urls && storefront.value.banner_urls.length > 0) {
        return storefront.value.banner_urls;
    }

    if (storefront.value?.banner_url) {
        return [storefront.value.banner_url];
    }

    return [];
});

const [emblaRef, emblaApi] = emblaCarouselVue(
    { loop: true, duration: 30 },
    [
        Autoplay({ delay: 5000, stopOnInteraction: false }),
        Fade()
    ]
);

const selectedIndex = ref(0);
const scrollSnaps = ref<number[]>([]);

watch(emblaApi, (newApi) => {
    if (newApi) {
        scrollSnaps.value = newApi.scrollSnapList();
        newApi.on('select', () => {
            selectedIndex.value = newApi.selectedScrollSnap();
        });
        selectedIndex.value = newApi.selectedScrollSnap();
    }
});

function scrollTo(index: number) {
    if (emblaApi.value) {
        emblaApi.value.scrollTo(index);
    }
}

interface Props {
    products?: any[];
    categories?: string[];
    stats?: any;
    filters?: { search: string; category: string };
    store?: any;
    theme?: any;
    showcase?: any;
    featured?: any;
}

const props = defineProps<Props>();

const productsRef = computed(() =>
    props.products !== undefined ? props.products : undefined,
);

const { displayProducts } = useMarketplaceCatalog(productsRef);

const {
    searchQ,
    selectedCat,
    displayCategories,
    filteredProducts,
    categoryMenu,
    applySearch,
    setCategory,
} = useMarketplaceFilters(displayProducts, props);

const sortOption = ref<'popular' | 'newest' | 'price_asc' | 'price_desc'>('popular');
const priceRangeFilter = ref<string>('all');
const ratingFilter = ref<number>(0);

const processedProducts = computed(() => {
    let result = [...filteredProducts.value];

    if (priceRangeFilter.value !== 'all') {
        const [minStr, maxStr] = priceRangeFilter.value.split('-');
        const min = Number(minStr);
        const max = Number(maxStr);
        result = result.filter((p) => {
            const num = p.priceNum ?? Number(String(p.price || '').replace(/[^0-9]/g, ''));

            return num >= min && num <= max;
        });
    }

    if (ratingFilter.value > 0) {
        result = result.filter((p) => (p.rating ?? 0) >= ratingFilter.value);
    }

    if (sortOption.value === 'newest') {
        result.sort((a, b) => b.id - a.id);
    } else if (sortOption.value === 'price_asc') {
        result.sort((a, b) => {
            const numA = a.priceNum ?? Number(String(a.price || '').replace(/[^0-9]/g, ''));
            const numB = b.priceNum ?? Number(String(b.price || '').replace(/[^0-9]/g, ''));

            return numA - numB;
        });
    } else if (sortOption.value === 'price_desc') {
        result.sort((a, b) => {
            const numA = a.priceNum ?? Number(String(a.price || '').replace(/[^0-9]/g, ''));
            const numB = b.priceNum ?? Number(String(b.price || '').replace(/[^0-9]/g, ''));

            return numB - numA;
        });
    } else {
        result.sort((a, b) => (b.sold ?? 0) - (a.sold ?? 0));
    }

    return result;
});

const {
    currentPage,
    totalPages,
    displayedProducts,
    isLoadingMore,
    hasMoreMobile,
    loadMoreTriggerRef,
    setPage,
} = useProductPagination(processedProducts, {
    itemsPerPage: 8,
    initialMobileCount: 4,
    mobileStep: 4,
});

const {
    cartItems,
    totalCartCount,
    isCartOpen,
    openCart,
    addToCart,
    updateCartQty,
    removeFromCart,
    goCheckout,
} = useMarketplaceCart();

const activeProductModal = ref<ProductDetail | null>(null);

function openProductDetail(product: any) {
    activeProductModal.value = product;
}
</script>

<template>

    <SeoHead :seo="seo" :fallback-title="`${storefront?.name ?? 'Toko Resmi'} — Marketplace`" />

    <StorefrontLayout :cartCount="totalCartCount" :searchQuery="searchQ" @open-cart="isCartOpen = true" @search="applySearch">
        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-4 px-3 pt-3 pb-28 sm:gap-6 sm:p-6">
            <!-- ── Dynamic 4-Hex Theme Hero Banner ── -->
            <div v-if="activeBanners.length > 0"
                class="relative overflow-hidden rounded-2xl border border-border bg-card shadow-xl sm:rounded-3xl"
            >
                <!-- Embla Carousel Background -->
                <div class="absolute inset-0 z-0 overflow-hidden" ref="emblaRef">
                    <div class="flex h-full w-full">
                        <div v-for="banner in activeBanners" :key="banner" class="flex-[0_0_100%] min-w-0 relative h-full">
                            <div
                                class="absolute inset-0"
                                :style="{
                                    backgroundImage: `url(${banner})`,
                                    backgroundSize: 'cover',
                                    backgroundPosition: 'center',
                                }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Gradient Overlay -->
                <div class="absolute inset-0 z-0 bg-black/60"></div>

                <!-- Content Container -->
                <div class="relative z-10 flex min-h-[260px] sm:min-h-[320px] md:min-h-[380px] lg:min-h-[650px] flex-col justify-center gap-4 p-5 text-white sm:gap-6 sm:p-10 md:flex-row md:items-center md:justify-between">
                    <!-- Background glows (using theme colors) -->
                <div class="pointer-events-none absolute -top-20 -right-20 h-72 w-72 rounded-full opacity-40 blur-3xl bg-primary" />
                <div class="pointer-events-none absolute -bottom-20 -left-20 h-72 w-72 rounded-full opacity-35 blur-3xl bg-secondary" />

                <div class="relative z-10">  
                    <p class="mb-1 flex items-center gap-1.5 text-[10px] font-black tracking-widest uppercase sm:mb-1.5 sm:text-xs text-accent">
                        <ShieldCheck class="h-4 w-4 text-secondary" />
                        {{ storefront?.name ?? 'Toko Resmi' }} · {{ storefront?.badge ?? 'Official Store' }}
                    </p>
                    <h1 class="text-2xl leading-tight font-black tracking-tight uppercase sm:text-4xl lg:text-5xl">
                        {{ storefront?.headline || 'Belanja Produk Favoritmu' }}
                        <span v-if="storefront?.description"
                            class="mt-1 block text-lg font-bold normal-case sm:text-2xl text-accent">
                            {{ storefront?.description }}
                        </span>
                    </h1>
                    <p class="mt-2 max-w-xl text-xs text-muted-foreground">
                        {{ storefront?.category ? `Kategori unggulan: ${storefront.category}.` : '' }}
                        {{ storefront?.hero_config?.about_text || 'Produk berkualitas dengan garansi keaslian dan layanan bebas ongkir.' }}
                    </p>
                    <!-- CTA desktop -->
                    <div v-if="storefront?.highlights && storefront.highlights.length > 0" class="mt-4 hidden gap-2 md:flex">
                        <Badge 
                            v-for="(hl, idx) in storefront.highlights"
                            :key="idx"
                            variant="outline"
                            class="border-primary/20 bg-primary/10 text-primary-foreground"
                        >
                            <Sparkles v-if="idx === 0" class="mr-1.5 h-3.5 w-3.5 text-accent" />
                            <Flame v-else-if="idx === 1" class="mr-1.5 h-3.5 w-3.5 text-destructive" />
                            <Truck v-else class="mr-1.5 h-3.5 w-3.5 text-secondary" />
                            {{ hl }}
                        </Badge>
                    </div>
                </div>

                <!-- Mobile filter badges -->
                <div v-if="storefront?.highlights && storefront.highlights.length > 0" class="relative z-10 flex flex-wrap items-center gap-2 md:hidden">
                    <Badge 
                        v-for="(hl, idx) in storefront.highlights"
                        :key="idx"
                        variant="outline" 
                        class="border-border bg-card text-muted-foreground"
                    >
                        <Sparkles v-if="idx === 0" class="mr-1 h-3 w-3 text-accent" />
                        <Flame v-else-if="idx === 1" class="mr-1 h-3 w-3 fill-primary text-primary" />
                        <Truck v-else class="mr-1 h-3 w-3 text-primary" />
                        {{ hl }}
                    </Badge>
                </div>

                <!-- Desktop hero right illustration -->
                <div class="relative z-10 hidden flex-col items-end gap-3 md:flex">
                    <div
                        class="rounded-2xl border border-primary/10 bg-background/80 p-4 text-foreground shadow-md backdrop-blur-md">
                        <p class="mb-1 text-[10px] font-extrabold tracking-widest text-muted-foreground uppercase">
                            {{ storefront?.hero_config?.widget_title || 'Belanja Aman' }}
                        </p>
                        <div class="flex items-center gap-2 text-sm font-black text-foreground">
                            <ShieldCheck class="h-5 w-5 text-accent" />
                            {{ storefront?.hero_config?.widget_subtitle || 'Escrow & Buyer Protection' }}
                        </div>
                        <p class="mt-1 text-[10px] text-muted-foreground">
                            {{ storefront?.hero_config?.widget_description || 'Uang kembali jika barang tidak sesuai' }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <div
                            class="rounded-xl border border-primary/10 bg-background/80 px-3.5 py-2 text-center shadow-xs backdrop-blur-md">
                            <p class="font-mono text-lg font-black text-primary">
                                {{ props.stats?.total_products || '0' }}
                            </p>
                            <p class="text-[10px] font-bold text-muted-foreground">
                                Produk
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-primary/10 bg-background/80 px-3.5 py-2 text-center shadow-xs backdrop-blur-md">
                            <p class="font-mono text-lg font-black text-primary">
                                {{ storefront?.hero_config?.fake_buyer_count || '54rb+' }}
                            </p>
                            <p class="text-[10px] font-bold text-muted-foreground">
                                Pembeli
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Carousel Navigation Dots -->
                <div v-if="scrollSnaps.length > 1" class="absolute bottom-4 left-0 right-0 z-20 flex justify-center gap-2">
                    <button
                        v-for="(_, index) in scrollSnaps"
                        :key="index"
                        @click="scrollTo(index)"
                        class="h-1.5 rounded-full transition-all duration-300"
                        :class="index === selectedIndex ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/60'"
                    />
                </div>
            </div>
        </div>


            <!-- ── Main Grid Section ── -->
            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- ─ Left Sidebar Filters (Desktop) ─ -->
                <aside class="hidden w-64 shrink-0 flex-col gap-4 lg:flex">
                    <!-- Category filter -->
                    <Card
                        class="relative overflow-hidden rounded-2xl border-2 border-primary/20 p-4 shadow-md transition-all duration-300 bg-card"
                    >
                        <!-- Sidebar Top Accent -->
                        <div class="absolute top-0 right-0 left-0 h-1.5 bg-accent" />
                        <p class="mt-1 mb-3 text-xs font-black tracking-widest uppercase text-foreground">
                            Kategori
                        </p>
                        <div class="flex flex-col gap-1.5">
                            <button v-for="item in categoryMenu" :key="item.cat" @click="setCategory(item.cat!)"
                                class="group flex w-full cursor-pointer items-center justify-between rounded-xl px-3 py-2 text-left text-xs font-black transition-all"
                                :class="selectedCat === item.cat
                                    ? 'text-primary-foreground shadow-md bg-primary'
                                    : 'text-foreground hover:bg-primary/10 hover:text-primary'
                                    ">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-lg shadow-2xs transition-transform group-hover:scale-110"
                                        :class="selectedCat === item.cat ? 'bg-white/25 text-white' : 'bg-primary/10 text-primary'">
                                        <component :is="item.icon" class="h-3.5 w-3.5" />
                                    </div>
                                    <span>{{ item.label }}</span>
                                </div>
                                <span v-if="selectedCat === item.cat"
                                    class="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-black text-white/90">
                                    {{ filteredProducts.length }}
                                </span>
                            </button>
                        </div>
                    </Card>

                    <!-- Nike Official Advantages Sidebar -->
                    <Card class="relative overflow-hidden border-border bg-card p-4 text-card-foreground">
                        <div class="absolute -top-6 -right-6 h-32 w-32 rounded-full opacity-30 blur-2xl bg-primary" />
                        <p class="mb-3 text-xs font-black tracking-widest uppercase text-accent">
                            Jaminan Official
                        </p>
                        <div class="relative z-10 flex flex-col gap-3.5 text-xs">
                            <div class="flex items-start gap-2.5">
                                <ShieldCheck class="mt-0.5 h-5 w-5 shrink-0 text-accent" />
                                <div>
                                    <p class="font-extrabold text-foreground">
                                        100% Original
                                    </p>
                                    <p class="text-[10px] text-muted-foreground">
                                        Langsung dari {{ storefront?.name ?? 'Indonesia' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <Truck class="mt-0.5 h-5 w-5 shrink-0 text-secondary" />
                                <div>
                                    <p class="font-extrabold text-foreground">
                                        Bebas Ongkir
                                    </p>
                                    <p class="text-[10px] text-muted-foreground">
                                        Pengiriman cepat seluruh Indonesia
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <RotateCcw class="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                                <div>
                                    <p class="font-extrabold text-foreground">
                                        Retur 30 Hari
                                    </p>
                                    <p class="text-[10px] text-muted-foreground">
                                        Tukar ukuran atau garansi pengembalian
                                    </p>
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
                            <Button v-for="cat in displayCategories" :key="cat" :variant="selectedCat === cat ? 'default' : 'outline'" size="sm" class="shrink-0 rounded-full text-xs font-semibold border-none" :class="selectedCat === cat ? 'bg-primary text-primary-foreground hover:brightness-95 shadow-md' : 'bg-muted text-muted-foreground hover:bg-primary/10 hover:text-primary'"
                                @click="setCategory(cat)">
                                {{ cat }}
                            </Button>
                        </div>
                    </div>

                    <!-- Product Grid header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-foreground sm:text-base">
                                {{
                                    selectedCat === 'Semua'
                                        ? 'Semua Produk'
                                        : selectedCat
                                }}
                                <span class="ml-1.5 text-xs font-normal text-muted-foreground">{{ processedProducts.length }}
                                    produk</span>
                            </h2>
                        </div>
                        <!-- Sort desktop -->
                        <div class="hidden items-center gap-1.5 text-xs text-muted-foreground lg:flex">
                            <span>Urutkan:</span>
                            <button @click="sortOption = 'popular'"
                                class="cursor-pointer rounded-lg border px-3 py-1.5 font-bold transition-all" :class="sortOption === 'popular'
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border bg-card hover:bg-muted'
                                    ">
                                Terpopuler
                            </button>
                            <button @click="sortOption = 'newest'"
                                class="cursor-pointer rounded-lg border px-3 py-1.5 font-bold transition-all" :class="sortOption === 'newest'
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border bg-card hover:bg-muted'
                                    ">
                                Terbaru
                            </button>
                            <button @click="sortOption = 'price_asc'"
                                class="cursor-pointer rounded-lg border px-3 py-1.5 font-bold transition-all" :class="sortOption === 'price_asc'
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border bg-card hover:bg-muted'
                                    ">
                                Harga <ArrowUp class="inline h-3.5 w-3.5" />
                            </button>
                            <button @click="sortOption = 'price_desc'"
                                class="cursor-pointer rounded-lg border px-3 py-1.5 font-bold transition-all" :class="sortOption === 'price_desc'
                                    ? 'border-primary bg-primary/10 text-primary'
                                    : 'border-border bg-card hover:bg-muted'
                                    ">
                                Harga <ArrowDown class="inline h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div v-if="displayedProducts.length > 0"
                        class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                        <ProductCard v-for="p in displayedProducts" :key="p.id" :product="p"
                            @click="openProductDetail(p)" @add-to-cart="addToCart" />
                    </div>

                    <!-- ── DESKTOP PAGINATION ── -->
                    <div v-if="filteredProducts.length > 0 && totalPages > 1"
                        class="hidden items-center justify-center gap-1.5 py-4 md:flex">
                        <Button variant="outline" size="sm" class="h-8 w-8 rounded-lg p-0 text-xs"
                            :disabled="currentPage === 1" @click="setPage(currentPage - 1)">
                            <ChevronLeftIcon class="h-4 w-4" />
                        </Button>

                        <Button v-for="page in totalPages" :key="page" :variant="currentPage === page ? 'default' : 'outline'
                            " size="sm" class="h-8 w-8 rounded-lg p-0 text-xs font-bold" @click="setPage(page)">
                            {{ page }}
                        </Button>

                        <Button variant="outline" size="sm" class="h-8 w-8 rounded-lg p-0 text-xs"
                            :disabled="currentPage === totalPages" @click="setPage(currentPage + 1)">
                            <ChevronRightIcon class="h-4 w-4" />
                        </Button>
                    </div>

                    <!-- ── MOBILE AUTO-FETCH (INFINITE SCROLL) ── -->
                    <div v-if="filteredProducts.length > 0"
                        class="flex flex-col items-center justify-center py-3 md:hidden">
                        <div v-if="isLoadingMore" class="flex items-center gap-2 py-3 text-xs font-bold text-primary">
                            <Loader2 class="h-4 w-4 animate-spin text-primary" />
                            <span>Memuat produk lainnya...</span>
                        </div>
                        <div v-else-if="hasMoreMobile" ref="loadMoreTriggerRef" class="h-6 w-full" />
                        <p v-else class="py-2 text-[11px] text-muted-foreground">
                            Semua produk sudah ditampilkan
                        </p>
                    </div>

                    <div v-else class="rounded-2xl bg-primary/5 py-16 text-center shadow-sm">
                        <Package class="mx-auto mb-2 h-10 w-10 text-accent" />
                        <p class="text-base font-semibold text-foreground">
                            Produk tidak ditemukan
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Coba kata kunci atau kategori lain
                        </p>
                    </div>

                    <!-- Nike Official Special Badges — Mobile -->
                    <div class="lg:hidden">
                        <h2 class="mb-3 text-sm font-bold text-foreground">
                            Layanan {{ storefront?.name ?? 'Toko' }}
                        </h2>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div
                                class="flex items-center gap-2 rounded-xl border border-primary/20 bg-primary p-3 text-primary-foreground">
                                <ShieldCheck class="h-4 w-4 shrink-0 text-accent" />
                                <span class="font-extrabold text-primary-foreground">100% Original</span>
                            </div>
                            <div
                                class="flex items-center gap-2 rounded-xl border border-primary/20 bg-primary p-3 text-primary-foreground">
                                <Truck class="h-4 w-4 shrink-0 text-secondary" />
                                <span class="font-extrabold text-primary-foreground">Bebas Ongkir</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section v-if="(page.props.faqs as any[])?.length" class="rounded-2xl border bg-card p-4">
                <h2 class="mb-3 text-sm font-bold">FAQ</h2>
                <div v-for="(f, i) in (page.props.faqs as any[])" :key="i" class="mb-3">
                    <p class="text-xs font-bold">{{ f.question }}</p>
                    <p class="text-xs text-muted-foreground">{{ f.answer }}</p>
                </div>
            </section>
        </main>

        <!-- Floating Quick Cart Bar -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-y-12 opacity-0"
            enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-12 opacity-0">
            <div v-if="totalCartCount > 0"
                class="fixed bottom-6 right-6 z-40 flex items-center gap-3 rounded-2xl border border-primary/20 bg-primary p-3 pr-4 text-primary-foreground shadow-2xl backdrop-blur-xl transition-all hover:scale-105">
                <button @click="isCartOpen = true"
                    class="relative flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl bg-white/20 text-inherit shadow-md transition-transform active:scale-95">
                    <ShoppingCart class="h-5 w-5" />
                    <span
                        class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-accent text-[10px] font-black text-accent-foreground shadow-xs">
                        {{ totalCartCount }}
                    </span>
                </button>
                <div class="flex flex-col cursor-pointer" @click="openCart()">
                    <span class="text-[10px] font-bold tracking-wider text-primary-foreground/80 uppercase">Keranjang Belanja</span>
                    <span class="text-xs font-black text-inherit">{{ totalCartCount }} Item terpilih</span>
                </div>
                    <Button
                        class="ml-2 cursor-pointer gap-1.5 rounded-xl bg-background text-xs font-black text-foreground shadow-md hover:brightness-95 border-0"
                        @click="openCart()"
                    >Lihat Keranjang
                </Button>
            </div>
        </transition>

        <!-- Product Detail Modal -->
        <ProductDetailModal :product="activeProductModal" @close="activeProductModal = null" @add-to-cart="addToCart" />

    </StorefrontLayout>
</template>
