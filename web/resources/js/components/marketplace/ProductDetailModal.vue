<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
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
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { useIsMobile } from '@/composables/useIsMobile';
import type { ProductDetail } from '@/types/product';

interface Props {
    product: ProductDetail | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'add-to-cart', product: ProductDetail, qty: number): void;
}>();

const page = usePage();
const storeName = computed(() => {
    const store = page.props.store as { name?: string } | undefined;

    return store?.name ?? 'Toko';
});

const qty = ref(1);
const isLiked = ref(false);
const activeTab = ref<'detail' | 'ulasan'>('detail');

// Reactive mobile detection to prevent double-rendering Sheet + Dialog
const { isMobile } = useIsMobile();

const selectedOptions = ref<Record<string, string>>({});

watch(
    () => props.product,
    (newProd) => {
        qty.value = 1;
        isLiked.value = false;
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
    if (!props.product?.variants?.length || !props.product?.variant_options?.length) {
return null;
}
    
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

    return props.product?.img;
});

const displayPrice = computed(() => {
    if (selectedVariant.value && selectedVariant.value.price) {
        return selectedVariant.value.price;
    }

    return props.product?.price;
});

const isOpen = computed(() => !!props.product);

function handleOpenChange(open: boolean) {
    if (!open) {
        emit('close');
    }
}

function handleAddToCart() {
    if (props.product) {
        const rawPrice =
            selectedVariant.value?.priceNum ||
            props.product.priceNum ||
            parseInt(props.product.price.replace(/[^\d]/g, ''), 10) ||
            100000;

        const productToAdd: ProductDetail = {
            ...props.product,
            variant_id: selectedVariant.value?.id,
            name: selectedVariant.value
                ? `${props.product.name} - ${selectedVariant.value.name}`
                : props.product.name,
            price: String(displayPrice.value ?? props.product.price),
            priceNum: rawPrice,
            img: displayImg.value ?? props.product.img,
        };
        emit('add-to-cart', productToAdd, qty.value);
        emit('close');
    }
}

function formatSold(n: number): string {
    if (n >= 1_000_000) {
        return `${(n / 1_000_000).toFixed(1)}jt+`;
    }

    if (n >= 1_000) {
        return `${(n / 1_000).toFixed(n >= 10_000 ? 0 : 1)}rb+`;
    }

    return String(n);
}

const mockReviews: any[] = [];

const ratingBreakdown: any[] = [];
</script>

<template>
    <!-- ─── MOBILE: Sheet slide-up from bottom ─── -->
    <Sheet v-if="isMobile" :open="isOpen" @update:open="handleOpenChange">
        <SheetContent
            side="bottom"
            class="flex max-h-[92dvh] flex-col rounded-t-3xl p-0"
        >
            <div class="flex justify-center pt-3 pb-1">
                <div class="h-1 w-10 rounded-full bg-black/15" />
            </div>
            <div class="flex flex-1 flex-col overflow-y-auto">
                <!-- Image -->
                <div
                    class="relative aspect-[4/3] w-full shrink-0 overflow-hidden bg-muted"
                >
                    <img
                        v-if="displayImg"
                        :src="displayImg"
                        :alt="product?.name"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-if="product?.discount"
                        class="absolute top-0 left-0 rounded-br-xl bg-brand-strong px-2.5 py-1 text-xs font-bold text-white"
                    >
                        {{ product.discount }}% OFF
                    </div>
                    <div
                        v-else-if="product?.tag"
                        class="absolute top-0 left-0 rounded-br-xl px-2.5 py-1 text-xs font-bold text-white"
                        :class="{
                            'bg-brand': product.tag === 'Bestseller',
                            'bg-brand-strong': product.tag === 'Hot',
                            'bg-brand-accent': product.tag === 'Baru',
                        }"
                    >
                        {{ product.tag }}
                    </div>
                    <button
                        @click="isLiked = !isLiked"
                        class="absolute top-2.5 right-2.5 flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-card/90 shadow-md backdrop-blur-sm"
                    >
                        <Heart
                            class="h-4 w-4 transition-colors"
                            :class="
                                isLiked
                                    ? 'fill-brand-strong text-brand-strong'
                                    : 'text-muted-foreground'
                            "
                        />
                    </button>
                </div>

                <div v-if="product" class="flex flex-col gap-0">
                    <div class="px-4 pt-3 pb-2">
                        <div class="flex items-baseline gap-2">
                            <span
                                class="text-2xl font-extrabold text-brand-strong"
                                >{{ displayPrice }}</span
                            >
                            <span
                                v-if="product.originalPrice"
                                class="text-sm text-muted-foreground line-through"
                                >{{ product.originalPrice }}</span
                            >
                        </div>
                        <h2
                            class="mt-1.5 text-sm leading-snug font-semibold text-foreground"
                        >
                            {{ product.name }}
                        </h2>
                        <div
                            class="mt-2 flex flex-wrap items-center gap-2 text-xs text-muted-foreground"
                        >
                            <div class="flex items-center gap-1">
                                <Star
                                    class="h-3.5 w-3.5 fill-accent stroke-accent"
                                /><span class="font-bold text-muted-foreground">{{
                                    product.rating
                                }}</span>
                            </div>
                            <span>·</span
                            ><span>{{ formatSold(product.sold) }} terjual</span>
                            <span>·</span
                            ><button
                                @click="activeTab = 'ulasan'"
                                class="text-brand"
                            >
                                {{ mockReviews.length }} ulasan
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Variant Selector -->
                    <div v-if="product?.variant_options && product.variant_options.length > 0" class="px-4 pb-4 flex flex-col gap-3">
                        <div v-for="opt in product.variant_options" :key="opt.name">
                            <p class="mb-2 text-xs font-bold text-foreground">Pilih {{ opt.name }}</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="val in opt.values"
                                    :key="val"
                                    type="button"
                                    @click="selectedOptions[opt.name] = val"
                                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all cursor-pointer"
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
                    <Separator />
                    <div
                        class="flex items-center gap-2 bg-brand-soft px-4 py-2.5"
                    >
                        <Truck
                            class="h-4 w-4 shrink-0 text-brand-secondary"
                        />
                        <span class="text-xs font-medium text-muted-foreground"
                            >Gratis Ongkir · Estimasi 2-3 hari</span
                        >
                    </div>
                    <Separator />
                    <div class="flex items-center gap-3 px-4 py-3">
                        <Avatar
                            :fallback="
                                product.store.substring(0, 2).toUpperCase()
                            "
                            :hue="200"
                            size="sm"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <span
                                    class="truncate text-xs font-bold text-foreground"
                                    >{{ product.store }}</span
                                ><BadgeCheck
                                    class="h-3.5 w-3.5 shrink-0 text-brand-accent"
                                />
                            </div>
                            <p class="text-[10px] text-muted-foreground">
                                Official Store · Respons cepat
                            </p>
                        </div>
                    </div>
                    <Separator />
                    <div class="flex border-b border-border">
                        <button
                            v-for="tab in ['detail', 'ulasan']"
                            :key="tab"
                            @click="activeTab = tab as 'detail' | 'ulasan'"
                            class="flex-1 py-3 text-xs font-bold capitalize transition-colors cursor-pointer"
                            :class="
                                activeTab === tab
                                    ? 'border-b-2 border-brand text-brand'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ tab === 'detail' ? 'Deskripsi' : 'Ulasan'
                            }}<span
                                v-if="tab === 'ulasan'"
                                class="ml-1 text-[10px]"
                                >({{ mockReviews.length }})</span
                            >
                        </button>
                    </div>
                    <div v-if="activeTab === 'detail'" class="px-4 py-4">
                        <p class="mb-3 text-xs leading-relaxed text-muted-foreground whitespace-pre-wrap">
                            {{ product.description || 'Tidak ada deskripsi.' }}
                        </p>
                        <div class="rounded-xl bg-muted p-3 text-xs">
                            <p class="mb-2 font-bold text-foreground">
                                Spesifikasi
                            </p>
                            <div class="flex flex-col gap-1.5">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Merk / Brand</span>
                                    <span class="font-medium">{{ product.brand || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Kode SKU</span>
                                    <span class="font-mono font-medium">{{ product.sku || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Kategori</span>
                                    <span class="font-medium">{{ product.category || product.cat || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Kondisi</span>
                                    <span class="font-medium">Baru</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Berat Produk</span>
                                    <span class="font-medium">{{ product.weightGram ? product.weightGram + ' gram' : '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Terjual</span>
                                    <span class="font-medium">{{ formatSold(product.sold) }} unit</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-4 py-4">
                        <div
                            class="mb-4 flex items-center gap-4 rounded-xl bg-muted p-4"
                        >
                            <div class="flex flex-col items-center">
                                <span
                                    class="text-4xl font-extrabold text-foreground"
                                    >{{ product.rating }}</span
                                >
                                <div class="my-1 flex gap-0.5">
                                    <Star
                                        v-for="i in 5"
                                        :key="i"
                                        class="h-3 w-3"
                                        :class="
                                            i <= Math.round(product.rating)
                                                ? 'fill-accent stroke-accent'
                                                : 'fill-none stroke-border'
                                        "
                                    />
                                </div>
                                <span class="text-[10px] text-muted-foreground"
                                    >{{ mockReviews.length }} ulasan</span
                                >
                            </div>
                            <div class="flex flex-1 flex-col gap-1">
                                <div
                                    v-for="row in ratingBreakdown"
                                    :key="row.stars"
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="w-4 text-right text-[10px] text-muted-foreground"
                                        >{{ row.stars }}</span
                                    ><Star
                                        class="h-2.5 w-2.5 fill-accent stroke-accent"
                                    />
                                    <div
                                        class="h-1.5 flex-1 overflow-hidden rounded-full bg-border"
                                    >
                                        <div
                                            class="h-full rounded-full bg-accent"
                                            :style="{ width: `${row.pct}%` }"
                                        />
                                    </div>
                                    <span
                                        class="w-6 text-[10px] text-muted-foreground"
                                        >{{ row.count }}</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div
                                v-if="!mockReviews.length"
                                class="flex flex-col items-center gap-2 py-8 text-center"
                            >
                                <Star class="h-8 w-8 stroke-border" />
                                <p
                                    class="text-xs font-bold text-foreground"
                                >
                                    Belum ada ulasan
                                </p>
                                <p class="text-[11px] text-muted-foreground">
                                    Jadi pembeli pertama yang memberi ulasan.
                                </p>
                            </div>
                            <div
                                v-for="(review, idx) in mockReviews"
                                :key="idx"
                                class="flex flex-col gap-2"
                            >
                                <div class="flex items-center gap-2.5">
                                    <Avatar
                                        :fallback="review.avatar"
                                        :hue="review.hue"
                                        size="sm"
                                    />
                                    <div>
                                        <p
                                            class="text-xs font-bold text-foreground"
                                        >
                                            {{ review.name }}
                                        </p>
                                        <div class="flex items-center gap-1.5">
                                            <div class="flex gap-0.5">
                                                <Star
                                                    v-for="i in 5"
                                                    :key="i"
                                                    class="h-2.5 w-2.5"
                                                    :class="
                                                        i <= review.rating
                                                            ? 'fill-accent stroke-accent'
                                                            : 'fill-none stroke-border'
                                                    "
                                                />
                                            </div>
                                            <span
                                                class="text-[10px] text-muted-foreground"
                                                >{{ review.date }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[10px] text-muted-foreground">
                                    Varian: {{ review.variant }}
                                </p>
                                <p
                                    class="text-xs leading-relaxed text-muted-foreground"
                                >
                                    {{ review.text }}
                                </p>
                                <div
                                    class="flex items-center gap-1 text-[10px] text-muted-foreground"
                                >
                                    <ThumbsUp class="h-3 w-3" /><span
                                        >{{ review.likes }} orang terbantu</span
                                    >
                                </div>
                                <Separator
                                    v-if="idx < mockReviews.length - 1"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="h-24" />
                </div>
            </div>
            <div
                v-if="product"
                class="shrink-0 border-t border-border bg-card px-4 pt-3 pb-6"
            >
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-bold text-foreground">Jumlah</span>
                    <div
                        class="flex items-center gap-2 rounded-xl border border-border bg-muted p-1"
                    >
                        <button
                            @click="qty = Math.max(1, qty - 1)"
                            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-card shadow-xs hover:bg-muted-foreground/10"
                        >
                            <Minus class="h-3.5 w-3.5" />
                        </button>
                        <span
                            class="min-w-[24px] text-center font-mono text-xs font-bold"
                            >{{ qty }}</span
                        >
                        <button
                            @click="qty++"
                            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-card shadow-xs hover:bg-muted-foreground/10"
                        >
                            <Plus class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
                <div class="flex gap-2.5">
                    <Button
                        variant="outline"
                        size="lg"
                        class="flex h-12 flex-1 items-center justify-center gap-1.5 border-brand text-xs font-bold text-brand"
                        @click="handleAddToCart"
                        ><ShoppingCart class="h-4 w-4" />Keranjang</Button
                    >
                    <Button
                        size="lg"
                        class="flex h-12 flex-[2] items-center justify-center gap-1.5 text-xs font-bold shadow-md bg-brand text-brand-foreground border-0 hover:opacity-90"
                        @click="handleAddToCart"
                        ><ArrowRight class="h-4 w-4" />Beli Sekarang</Button
                    >
                </div>
            </div>
            <SheetTitle class="sr-only">{{
                product?.name ?? 'Detail Produk'
            }}</SheetTitle>
        </SheetContent>
    </Sheet>

    <!-- ─── DESKTOP: Dialog centered 2-column modal ─── -->
    <Dialog v-else :open="isOpen" @update:open="handleOpenChange">
        <DialogContent
            class="sm:max-w-4xl md:max-w-5xl h-[85vh] max-h-[750px] w-[94vw] overflow-hidden rounded-3xl p-0 border-0 shadow-2xl"
        >
            <div class="flex h-full w-full overflow-hidden">
                <!-- LEFT: Image column -->
                <div class="relative flex w-2/5 shrink-0 flex-col bg-muted border-r border-border overflow-hidden">
                    <div class="relative flex-1 overflow-hidden">
                        <img
                            v-if="displayImg"
                            :src="displayImg"
                            :alt="product?.name"
                            class="h-full w-full object-cover"
                        />
                        <!-- Discount / tag badge -->
                        <div
                            v-if="product?.discount"
                            class="absolute top-0 left-0 rounded-br-2xl bg-brand-strong px-3 py-1.5 text-sm font-bold text-white"
                        >
                            {{ product.discount }}% OFF
                        </div>
                        <div
                            v-else-if="product?.tag"
                            class="absolute top-0 left-0 flex flex-wrap gap-1 p-2"
                        >
                            <span
                                v-for="t in product.tag.split(',')"
                                :key="t"
                                class="rounded-lg border border-brand-accent bg-brand-accent px-2.5 py-1 text-xs font-black text-brand-foreground shadow-xs"
                            >
                                {{ t.trim() }}
                            </span>
                        </div>
                        <!-- Actions top-right -->
                        <div class="absolute top-3 right-3 flex flex-col gap-2">
                            <button
                                @click="isLiked = !isLiked"
                                class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-card/90 shadow-md backdrop-blur-sm transition-transform active:scale-90"
                            >
                                <Heart
                                    class="h-4 w-4 transition-colors"
                                    :class="
                                        isLiked
                                            ? 'fill-brand-strong text-brand-strong'
                                            : 'text-muted-foreground'
                                    "
                                />
                            </button>
                            <button
                                class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-card/90 shadow-md backdrop-blur-sm"
                            >
                                <Share2 class="h-4 w-4 text-muted-foreground" />
                            </button>
                        </div>
                    </div>

                    <!-- Store Info block below image -->
                    <div
                        v-if="product"
                        class="border-t border-border bg-card p-4"
                    >
                        <div class="flex items-center gap-3">
                            <Avatar
                                :fallback="
                                    product.store.substring(0, 2).toUpperCase()
                                "
                                :hue="200"
                                size="md"
                            />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="truncate text-sm font-bold text-foreground"
                                        >{{ product.store }}</span
                                    >
                                    <BadgeCheck
                                        class="h-4 w-4 shrink-0 text-brand-accent"
                                    />
                                </div>
                                <p class="text-[11px] text-muted-foreground">
                                    Official Store · Respons &lt; 1 jam
                                </p>
                            </div>
                        </div>
                        <!-- Trust badges -->
                        <div class="mt-3 flex flex-col gap-1.5">
                            <div
                                class="flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <ShieldCheck
                                    class="h-3.5 w-3.5 shrink-0 text-brand-strong"
                                />
                                <span>Produk 100% original bergaransi</span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <Truck
                                    class="h-3.5 w-3.5 shrink-0 text-brand"
                                />
                                <span>Gratis Ongkir · Estimasi 2-3 hari</span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <Package
                                    class="h-3.5 w-3.5 shrink-0 text-brand-secondary"
                                />
                                <span>Packing aman bubble wrap + kardus</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Detail column -->
                <div class="flex flex-1 flex-col min-w-0 overflow-hidden bg-card">
                    <!-- Scrollable content -->
                    <div class="flex flex-1 flex-col overflow-y-auto">
                        <div v-if="product" class="flex flex-col">
                            <!-- Product headline -->
                            <div class="px-6 pt-6 pb-4">
                                <!-- Category + Tag -->
                                <div
                                    class="mb-2 flex flex-wrap items-center gap-1.5"
                                >
                                    <Badge
                                        v-for="c in product.cat.split(',')"
                                        :key="c"
                                        variant="outline"
                                        class="px-2 py-0.5 text-[10px] font-bold"
                                        >{{ c.trim() }}</Badge
                                    >
                                    <template v-if="product.tag">
                                        <Badge
                                            v-for="t in product.tag.split(',')"
                                            :key="t"
                                            variant="default"
                                            class="px-2 py-0.5 text-[10px] font-extrabold"
                                            >{{ t.trim() }}</Badge
                                        >
                                    </template>
                                </div>

                                <h2
                                    class="text-xl leading-snug font-extrabold text-foreground"
                                >
                                    {{ product.name }}
                                </h2>

                                <!-- Rating row -->
                                <div
                                    class="mt-2 flex flex-wrap items-center gap-3 text-sm text-muted-foreground"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <div class="flex gap-0.5">
                                            <Star
                                                v-for="i in 5"
                                                :key="i"
                                                class="h-3.5 w-3.5"
                                                :class="
                                                    i <=
                                                    Math.round(product.rating)
                                                        ? 'fill-accent stroke-accent'
                                                        : 'fill-none stroke-border'
                                                "
                                            />
                                        </div>
                                        <span
                                            class="font-bold text-muted-foreground"
                                            >{{ product.rating }}</span
                                        >
                                    </div>
                                    <span class="text-muted-foreground/30">|</span>
                                    <span
                                        >{{
                                            formatSold(product.sold)
                                        }}
                                        terjual</span
                                    >
                                    <span class="text-muted-foreground/30">|</span>
                                    <button
                                        @click="activeTab = 'ulasan'"
                                        class="text-brand hover:underline"
                                    >
                                        {{ mockReviews.length }} ulasan
                                    </button>
                                </div>

                                <!-- Price block -->
                                <div
                                    class="mt-4 rounded-2xl bg-gradient-to-r from-brand-soft to-card p-4"
                                >
                                    <p
                                        class="mb-0.5 text-[11px] font-medium text-muted-foreground"
                                    >
                                        Harga
                                    </p>
                                    <div class="flex items-baseline gap-2.5">
                                        <span
                                            class="font-mono text-3xl font-extrabold text-brand-strong"
                                            >{{ displayPrice }}</span
                                        >
                                        <span
                                            v-if="product.originalPrice"
                                            class="text-base text-muted-foreground line-through"
                                            >{{ product.originalPrice }}</span
                                        >
                                        <Badge
                                            v-if="product.discount"
                                            class="bg-brand-strong text-xs font-bold text-white"
                                            >Hemat
                                            {{ product.discount }}%</Badge
                                        >
                                    </div>
                                </div>

                                <!-- Desktop Variant Selector -->
                                <div v-if="product?.variant_options && product.variant_options.length > 0" class="mt-5 flex flex-col gap-3">
                                    <div v-for="opt in product.variant_options" :key="opt.name">
                                        <p class="mb-2 text-xs font-bold text-foreground">Pilih {{ opt.name }}</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                v-for="val in opt.values"
                                                :key="val"
                                                type="button"
                                                @click="selectedOptions[opt.name] = val"
                                                class="px-3 py-1.5 rounded-lg border text-sm font-semibold transition-all cursor-pointer"
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
                            </div>

                            <Separator />

                            <!-- Tabs -->
                            <div class="flex border-b border-border px-6">
                                <button
                                    v-for="tab in ['detail', 'ulasan']"
                                    :key="tab"
                                    @click="
                                        activeTab = tab as 'detail' | 'ulasan'
                                    "
                                    class="mr-6 py-3 text-sm font-bold capitalize transition-colors cursor-pointer"
                                    :class="
                                        activeTab === tab
                                            ? 'border-b-2 border-brand text-brand'
                                            : 'text-muted-foreground hover:text-foreground'
                                    "
                                >
                                    {{
                                        tab === 'detail'
                                            ? 'Deskripsi'
                                            : 'Ulasan'
                                    }}
                                    <span
                                        v-if="tab === 'ulasan'"
                                        class="ml-1 text-xs"
                                        >({{ mockReviews.length }})</span
                                    >
                                </button>
                            </div>

                            <div
                                v-if="activeTab === 'detail'"
                                class="px-6 py-5"
                            >
                                <p
                                    class="mb-5 text-sm leading-relaxed text-muted-foreground whitespace-pre-wrap"
                                >
                                    {{ product.description || 'Tidak ada deskripsi.' }}
                                </p>
                                <div
                                    class="rounded-2xl border border-border bg-muted p-4"
                                >
                                    <p
                                        class="mb-3 text-sm font-bold text-foreground"
                                    >
                                        Spesifikasi & Identitas Produk
                                    </p>
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-xs sm:text-sm"
                                    >
                                        <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                            <span class="text-muted-foreground shrink-0"
                                                >Merk / Brand</span
                                            ><span
                                                class="font-bold text-foreground truncate text-right"
                                                >{{ product.brand || '-' }}</span
                                            >
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                            <span class="text-muted-foreground shrink-0"
                                                >Kode SKU</span
                                            ><span
                                                class="font-mono font-bold text-foreground truncate text-right"
                                                >{{ product.sku || '-' }}</span
                                            >
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                            <span class="text-muted-foreground shrink-0"
                                                >Kategori</span
                                            ><span
                                                class="font-medium text-foreground truncate text-right"
                                                >{{ product.cat || product.category || '-' }}</span
                                            >
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                            <span class="text-muted-foreground shrink-0"
                                                >Kondisi</span
                                            ><span
                                                class="font-medium text-foreground text-right"
                                                >Baru</span
                                            >
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                            <span class="text-muted-foreground shrink-0"
                                                >Berat Produk</span
                                            ><span
                                                class="font-medium text-foreground text-right"
                                                >{{ product.weightGram ? product.weightGram + ' gram' : '-' }}</span
                                            >
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                            <span class="text-muted-foreground shrink-0"
                                                >Terjual</span
                                            ><span
                                                class="font-medium text-foreground text-right"
                                                >{{
                                                    formatSold(product.sold)
                                                }}
                                                unit</span
                                            >
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                            <span class="text-muted-foreground shrink-0"
                                                >Rating</span
                                            ><span
                                                class="font-medium text-foreground text-right"
                                                >{{ product.rating }}/5.0</span
                                            >
                                        </div>
                                        <div class="flex items-center justify-between gap-2 border-b border-border pb-2">
                                            <span class="text-muted-foreground shrink-0"
                                                >Garansi</span
                                            ><span
                                                class="font-medium text-foreground text-right"
                                                >Garansi Retur Original</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Ulasan -->
                            <div v-else class="px-6 py-5">
                                <!-- Rating Summary -->
                                <div
                                    class="mb-5 flex items-center gap-6 rounded-2xl border border-border bg-muted p-4"
                                >
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-5xl font-extrabold text-foreground"
                                            >{{ product.rating }}</span
                                        >
                                        <div class="my-1.5 flex gap-0.5">
                                            <Star
                                                v-for="i in 5"
                                                :key="i"
                                                class="h-4 w-4"
                                                :class="
                                                    i <=
                                                    Math.round(product.rating)
                                                        ? 'fill-accent stroke-accent'
                                                        : 'fill-none stroke-border'
                                                "
                                            />
                                        </div>
                                        <span class="text-xs text-muted-foreground"
                                            >dari
                                            {{ mockReviews.length }}
                                            ulasan</span
                                        >
                                    </div>
                                    <div class="flex flex-1 flex-col gap-1.5">
                                        <div
                                            v-for="row in ratingBreakdown"
                                            :key="row.stars"
                                            class="flex items-center gap-2"
                                        >
                                            <span
                                                class="w-4 text-right text-xs text-muted-foreground"
                                                >{{ row.stars }}</span
                                            >
                                            <Star
                                                class="h-3 w-3 fill-accent stroke-accent"
                                            />
                                            <div
                                                class="h-2 flex-1 overflow-hidden rounded-full bg-border"
                                            >
                                                <div
                                                    class="h-full rounded-full bg-accent transition-all"
                                                    :style="{
                                                        width: `${row.pct}%`,
                                                    }"
                                                />
                                            </div>
                                            <span
                                                class="w-8 text-xs text-muted-foreground"
                                                >{{ row.count }}</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Reviews list -->
                                <div class="flex flex-col gap-5">
                                    <div
                                        v-if="!mockReviews.length"
                                        class="flex flex-col items-center gap-2 py-10 text-center"
                                    >
                                        <Star class="h-10 w-10 stroke-border" />
                                        <p
                                            class="text-sm font-bold text-foreground"
                                        >
                                            Belum ada ulasan
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            Jadi pembeli pertama yang memberi
                                            ulasan.
                                        </p>
                                    </div>
                                    <div
                                        v-for="(review, idx) in mockReviews"
                                        :key="idx"
                                        class="flex flex-col gap-2"
                                    >
                                        <div class="flex items-center gap-3">
                                            <Avatar
                                                :fallback="review.avatar"
                                                :hue="review.hue"
                                                size="sm"
                                            />
                                            <div>
                                                <p
                                                    class="text-sm font-bold text-foreground"
                                                >
                                                    {{ review.name }}
                                                </p>
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <div class="flex gap-0.5">
                                                        <Star
                                                            v-for="i in 5"
                                                            :key="i"
                                                            class="h-3 w-3"
                                                            :class="
                                                                i <=
                                                                review.rating
                                                                    ? 'fill-accent stroke-accent'
                                                                    : 'fill-none stroke-border'
                                                            "
                                                        />
                                                    </div>
                                                    <span
                                                        class="text-xs text-muted-foreground"
                                                        >{{ review.date }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <Badge
                                            variant="outline"
                                            class="w-fit px-2 py-0 text-[10px] text-muted-foreground"
                                            >{{ review.variant }}</Badge
                                        >
                                        <p
                                            class="text-sm leading-relaxed text-muted-foreground"
                                        >
                                            {{ review.text }}
                                        </p>
                                        <button
                                            class="flex w-fit items-center gap-1 rounded-lg border border-border px-2.5 py-1 text-xs text-muted-foreground transition-colors hover:bg-muted"
                                        >
                                            <ThumbsUp class="h-3 w-3" />
                                            Membantu ({{ review.likes }})
                                        </button>
                                        <Separator
                                            v-if="idx < mockReviews.length - 1"
                                        />
                                    </div>
                                </div>

                                <button
                                    class="mt-4 flex w-full items-center justify-center gap-1 py-3 text-sm font-bold text-brand hover:underline"
                                >
                                    Lihat Semua Ulasan
                                    <ArrowRight class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>



                    <!-- Sticky bottom action -->
                    <div
                        v-if="product"
                        class="shrink-0 border-t border-border bg-card px-6 py-4"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-foreground"
                                >Jumlah Pesanan</span
                            >
                            <div
                                class="flex items-center gap-2 rounded-xl border border-border bg-muted p-1"
                            >
                                <button
                                    @click="qty = Math.max(1, qty - 1)"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-card shadow-xs hover:bg-muted-foreground/10"
                                >
                                    <Minus class="h-4 w-4" />
                                </button>
                                <span
                                    class="min-w-[32px] text-center font-mono text-sm font-bold text-foreground"
                                    >{{ qty }}</span
                                >
                                <button
                                    @click="qty++"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-card shadow-xs hover:bg-muted-foreground/10"
                                >
                                    <Plus class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Button
                                variant="outline"
                                size="lg"
                                class="flex h-12 flex-1 items-center justify-center gap-2 border-brand font-bold text-brand hover:bg-brand/5"
                                @click="handleAddToCart"
                            >
                                <ShoppingCart class="h-5 w-5" /> + Keranjang
                            </Button>
                            <Button
                                size="lg"
                                class="flex h-12 flex-[2] items-center justify-center gap-2 font-bold shadow-lg bg-brand text-brand-foreground border-0 hover:opacity-90"
                                @click="handleAddToCart"
                            >
                                Beli Sekarang <ArrowRight class="h-5 w-5" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <DialogTitle class="sr-only">{{
                product?.name ?? 'Detail Produk'
            }}</DialogTitle>
        </DialogContent>
    </Dialog>
</template>
