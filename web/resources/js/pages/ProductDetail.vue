<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Star,
    ShoppingCart,
    Plus,
    Minus,
    ShieldCheck,
    Truck,
    Heart,
    Package,
    BadgeCheck,
    ThumbsUp,
    ArrowRight,
    Share2,
    ChevronLeft
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useCart } from '@/lib/useCart';
import { toast } from '@/components/ui/sonner';
import { useActiveUser } from '@/lib/useActiveUser';

interface Props {
    store: any;
    theme: any;
    product: any;
}

const props = defineProps<Props>();

const activeUser = useActiveUser();

const qty = ref(1);
const isLiked = ref(false);
const activeTab = ref<'detail' | 'ulasan'>('detail');

const { addItem } = useCart();

function formatSold(n: number): string {
    if (!n) return '0';
    if (n >= 1_000_000) {
        return `${(n / 1_000_000).toFixed(1)}jt+`;
    }
    if (n >= 1_000) {
        return `${(n / 1_000).toFixed(n >= 10_000 ? 0 : 1)}rb+`;
    }
    return String(n);
}

function handleAddToCart() {
    if (!activeUser.value) {
        toast.error('Silakan login untuk menambahkan ke keranjang');
        const store = usePage().props.store as any;
        router.visit(store?.slug ? `/${store.slug}/login` : '/login');
        return;
    }

    const rawPrice =
        selectedVariant.value?.priceNum ||
        props.product.priceNum ||
        parseInt(props.product.price.replace(/[^\d]/g, ''), 10) ||
        100000;

    addItem(
        {
            id: props.product.id,
            variant_id: selectedVariant.value?.id,
            name: selectedVariant.value 
                ? `${props.product.name} - ${selectedVariant.value.name}` 
                : props.product.name,
            price: rawPrice,
            formattedPrice: displayPrice.value,
            img: displayImg.value,
            store: props.store.name,
            qty: qty.value,
        },
        qty.value,
    );

    toast.success(`${props.product.name} ditambahkan ke keranjang!`);
}

const selectedOptions = ref<Record<string, string>>({});

watch(
    () => props.product,
    (newProd) => {
        qty.value = 1;
        activeTab.value = 'detail';
        selectedOptions.value = {};
        
        if (newProd?.variant_options?.length) {
            newProd.variant_options.forEach((opt: any) => {
                if (opt.values?.length) {
                    selectedOptions.value[opt.name] = opt.values[0];
                }
            });
        }
    },
    { immediate: true }
);

const selectedVariant = computed(() => {
    if (!props.product.variants?.length || !props.product.variant_options?.length) return null;
    
    const expectedName = props.product.variant_options
        .map((opt: any) => selectedOptions.value[opt.name] || '')
        .filter(Boolean)
        .join(' - ');
        
    return props.product.variants.find((v: any) => v.name === expectedName) || null;
});

const displayImg = computed(() => {
    if (selectedVariant.value && selectedVariant.value.img) {
        return selectedVariant.value.img;
    }
    return props.product.img;
});

const displayPrice = computed(() => {
    if (selectedVariant.value && selectedVariant.value.price) {
        return selectedVariant.value.price;
    }
    return props.product.price;
});

const mockReviews: any[] = [];
const ratingBreakdown: any[] = [];

</script>

<template>
    <Head :title="`${product.name} - ${store.name}`" />

    <StorefrontLayout>
        <main class="mx-auto flex w-full max-w-[1200px] flex-col gap-4 px-3 pt-4 pb-28 sm:gap-6 sm:p-6 lg:flex-row lg:items-start">
            
            <!-- Mobile Back Button -->
            <div class="lg:hidden w-full flex items-center mb-2">
                <button @click="router.visit('/' + store.slug)" class="flex items-center gap-1 text-sm font-bold text-muted-foreground hover:text-foreground">
                    <ChevronLeft class="w-4 h-4" /> Kembali ke Toko
                </button>
            </div>

            <!-- LEFT: Image column -->
            <div class="relative flex w-full lg:w-2/5 shrink-0 flex-col bg-muted overflow-hidden rounded-3xl border border-border shadow-sm">
                <div class="relative flex-1 overflow-hidden aspect-square">
                    <img
                        v-if="displayImg"
                        :src="displayImg"
                        :alt="product.name"
                        class="h-full w-full object-cover transition-all duration-300"
                    />
                    <!-- Tag badge -->
                    <div
                        v-if="product.tag"
                        class="absolute top-0 left-0 flex flex-wrap gap-1 p-3"
                    >
                        <span
                            v-for="t in product.tag.split(',')"
                            :key="t"
                            class="rounded-lg bg-brand-accent px-3 py-1.5 text-xs font-black text-brand-foreground shadow-xs border border-white/20"
                        >
                            {{ t.trim() }}
                        </span>
                    </div>
                    <!-- Actions top-right -->
                    <div class="absolute top-3 right-3 flex flex-col gap-2">
                        <button
                            @click="isLiked = !isLiked"
                            class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-card/90 shadow-md backdrop-blur-sm transition-transform active:scale-90"
                        >
                            <Heart
                                class="h-5 w-5 transition-colors"
                                :class="
                                    isLiked
                                        ? 'fill-brand-strong text-brand-strong'
                                        : 'text-muted-foreground'
                                "
                            />
                        </button>
                        <button
                            class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-card/90 shadow-md backdrop-blur-sm"
                        >
                            <Share2 class="h-5 w-5 text-muted-foreground" />
                        </button>
                    </div>
                </div>

                <!-- Store Info block below image -->
                <div class="border-t border-border bg-card p-5 cursor-pointer hover:bg-muted transition" @click="router.visit('/' + store.slug)">
                    <div class="flex items-center gap-3">
                        <Avatar
                            :fallback="store.avatar"
                            :hue="store.avatarHue"
                            size="md"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <span class="truncate text-base font-extrabold text-foreground">{{ store.name }}</span>
                                <BadgeCheck class="h-5 w-5 shrink-0 text-brand-accent" />
                            </div>
                            <p class="text-xs font-medium text-muted-foreground">
                                {{ store.badge }} · Respons &lt; 1 jam
                            </p>
                        </div>
                        <ChevronLeft class="w-5 h-5 rotate-180 text-muted-foreground" />
                    </div>
                    <!-- Trust badges -->
                    <div class="mt-4 flex flex-col gap-2">
                        <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                            <ShieldCheck class="h-4 w-4 shrink-0 text-brand-strong" />
                            <span>Produk 100% original bergaransi</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                            <Truck class="h-4 w-4 shrink-0 text-brand" />
                            <span>Gratis Ongkir · Estimasi 2-3 hari</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
                            <Package class="h-4 w-4 shrink-0 text-brand-secondary" />
                            <span>Packing aman bubble wrap + kardus</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Detail column -->
            <div class="flex flex-1 flex-col min-w-0 bg-card rounded-3xl border border-border shadow-sm">
                <!-- Product headline -->
                <div class="px-5 pt-6 pb-4 sm:px-8 sm:pt-8">
                    <!-- Category -->
                    <div class="mb-3 flex flex-wrap items-center gap-1.5">
                        <Badge
                            v-if="product.category"
                            variant="outline"
                            class="px-2 py-0.5 text-[10px] font-bold"
                            >{{ product.category }}</Badge
                        >
                    </div>

                    <h1 class="text-2xl leading-tight font-black text-foreground sm:text-3xl">
                        {{ product.name }}
                    </h1>

                    <!-- Rating row -->
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm font-semibold text-muted-foreground">
                        <div class="flex items-center gap-1.5">
                            <div class="flex gap-0.5">
                                <Star
                                    v-for="i in 5"
                                    :key="i"
                                    class="h-4 w-4"
                                    :class="
                                        i <= Math.round(product.rating || 0)
                                            ? 'fill-accent stroke-accent'
                                            : 'fill-none stroke-border'
                                    "
                                />
                            </div>
                            <span class="font-extrabold text-muted-foreground">{{ product.rating }}</span>
                        </div>
                        <span class="text-muted-foreground/30">|</span>
                        <span>{{ formatSold(product.sold) }} terjual</span>
                        <span class="text-muted-foreground/30">|</span>
                        <button @click="activeTab = 'ulasan'" class="text-brand hover:underline cursor-pointer">
                            {{ mockReviews.length }} ulasan
                        </button>
                    </div>

                    <!-- Price block -->
                    <div class="mt-6 rounded-2xl bg-gradient-to-r from-brand-soft to-card p-5 shadow-inner">
                        <p class="mb-1 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                            Harga
                        </p>
                        <div class="flex items-baseline gap-3">
                            <span class="font-mono text-4xl font-black text-brand-strong">
                                {{ displayPrice }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Variant Selector -->
                <div v-if="product.variant_options && product.variant_options.length > 0" class="px-5 sm:px-8 py-4 border-y border-border flex flex-col gap-3">
                    <div v-for="opt in product.variant_options" :key="opt.name">
                        <p class="mb-3 text-sm font-extrabold text-foreground">Pilih {{ opt.name }}</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="val in opt.values"
                                :key="val"
                                type="button"
                                @click="selectedOptions[opt.name] = val"
                                class="px-4 py-2 rounded-xl border text-sm font-semibold transition-all cursor-pointer"
                                :class="
                                    selectedOptions[opt.name] === val
                                        ? 'border-brand-strong bg-brand-soft text-brand-strong ring-1 ring-brand-strong'
                                        : 'border-border bg-card text-foreground hover:border-brand-strong/50 hover:bg-muted'
                                "
                            >
                                {{ val }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop sticky add-to-cart bar or inline -->
                <div class="px-5 sm:px-8 py-4 border-y border-border bg-muted/30 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-extrabold text-foreground">Jumlah</span>
                        <div class="flex items-center gap-2 rounded-xl border border-border bg-card p-1 shadow-xs">
                            <button
                                @click="qty = Math.max(1, qty - 1)"
                                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-muted shadow-xs hover:bg-muted/80 transition"
                            >
                                <Minus class="h-4 w-4" />
                            </button>
                            <span class="min-w-[32px] text-center font-mono text-sm font-black">{{ qty }}</span>
                            <button
                                @click="qty++"
                                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-muted shadow-xs hover:bg-muted/80 transition"
                            >
                                <Plus class="h-4 w-4" />
                            </button>
                        </div>
                        <span class="text-xs font-medium text-muted-foreground">Sisa stok: {{ product.stock }}</span>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            size="lg"
                            class="h-12 px-8 text-sm font-black shadow-md cursor-pointer hover:-translate-y-0.5 transition-transform bg-brand text-brand-foreground border-0 hover:opacity-90"
                            @click="handleAddToCart"
                        >
                            <ShoppingCart class="mr-2 h-4 w-4" /> Beli Sekarang
                        </Button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-border px-5 sm:px-8 pt-4 bg-card">
                    <button
                        v-for="tab in ['detail', 'ulasan']"
                        :key="tab"
                        @click="activeTab = tab as 'detail' | 'ulasan'"
                        class="mr-8 py-4 text-base font-black capitalize transition-colors cursor-pointer"
                        :class="
                            activeTab === tab
                                ? 'border-b-[3px] border-brand text-brand'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                    >
                        {{ tab === 'detail' ? 'Deskripsi' : 'Ulasan' }}
                        <span v-if="tab === 'ulasan'" class="ml-1 text-sm font-bold text-muted-foreground">({{ mockReviews.length }})</span>
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="px-5 sm:px-8 py-6 bg-card rounded-b-3xl">
                    <div v-if="activeTab === 'detail'">
                        <p class="mb-6 text-sm md:text-base leading-relaxed text-muted-foreground whitespace-pre-wrap">
                            {{ product.description || 'Tidak ada deskripsi.' }}
                        </p>
                        <div class="rounded-2xl border border-border bg-muted p-5 shadow-xs">
                            <p class="mb-4 text-sm font-black text-foreground">
                                Spesifikasi & Identitas Produk
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                                <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                    <span class="text-muted-foreground shrink-0 font-medium">Merk / Brand</span>
                                    <span class="font-extrabold text-foreground text-right">{{ product.brand || '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                    <span class="text-muted-foreground shrink-0 font-medium">Kode SKU</span>
                                    <span class="font-mono font-extrabold text-foreground text-right">{{ product.sku || '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                    <span class="text-muted-foreground shrink-0 font-medium">Kategori</span>
                                    <span class="font-bold text-foreground text-right">{{ product.category || product.cat || '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                    <span class="text-muted-foreground shrink-0 font-medium">Kondisi</span>
                                    <span class="font-bold text-foreground text-right">Baru</span>
                                </div>
                                <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                    <span class="text-muted-foreground shrink-0 font-medium">Berat Produk</span>
                                    <span class="font-bold text-foreground text-right">{{ product.weightGram ? product.weightGram + ' gram' : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div v-if="!mockReviews.length" class="flex flex-col items-center gap-3 py-12 text-center">
                            <div class="h-16 w-16 rounded-full bg-muted flex items-center justify-center">
                                <Star class="h-8 w-8 stroke-muted-foreground/30" />
                            </div>
                            <p class="text-base font-extrabold text-foreground">
                                Belum ada ulasan
                            </p>
                            <p class="text-sm font-medium text-muted-foreground">
                                Jadilah pembeli pertama yang memberikan ulasan untuk produk ini.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </StorefrontLayout>
</template>
