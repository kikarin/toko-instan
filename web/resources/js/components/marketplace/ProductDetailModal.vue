<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    Star,
    ShoppingCart,
    Plus,
    Minus,
    ShieldCheck,
    Truck,
    Heart,
    Store,
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
import { useIsMobile } from '@/lib/useIsMobile';

export interface ProductDetail {
    id: number;
    name: string;
    price: string;
    priceNum?: number;
    sold: number;
    rating: number;
    store: string;
    storeSlug?: string;
    img: string;
    tag: string | null;
    cat: string;
    discount?: number;
    originalPrice?: string;
    description?: string;
    sku?: string;
    brand?: string;
    weightGram?: number;
}

interface Props {
    product: ProductDetail | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'add-to-cart', product: ProductDetail, qty: number): void;
}>();

const qty = ref(1);
const isLiked = ref(false);
const activeTab = ref<'detail' | 'ulasan'>('detail');

// Reactive mobile detection to prevent double-rendering Sheet + Dialog
const { isMobile } = useIsMobile();

watch(
    () => props.product,
    () => {
        qty.value = 1;
        isLiked.value = false;
        activeTab.value = 'detail';
    },
);

const isOpen = computed(() => !!props.product);

function handleOpenChange(open: boolean) {
    if (!open) {
        emit('close');
    }
}

function handleAddToCart() {
    if (props.product) {
        emit('add-to-cart', props.product, qty.value);
        emit('close');
    }
}

function visitStore() {
    if (props.product) {
        const slug =
            props.product.storeSlug ||
            props.product.store.toLowerCase().replace(/[^a-z0-9]+/g, '-');
        emit('close');
        router.visit(`/store/${slug}`);
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

const mockReviews = [
    {
        name: 'Rina S.',
        avatar: 'RS',
        hue: 280,
        rating: 5,
        date: '2 hari lalu',
        text: 'Produknya bagus banget, sesuai foto! Pengiriman cepat dan packing rapi. Recommended seller 👍',
        variant: 'Ukuran M · Warna Hitam',
        likes: 12,
    },
    {
        name: 'Budi W.',
        avatar: 'BW',
        hue: 200,
        rating: 4,
        date: '1 minggu lalu',
        text: 'Kualitas oke, harga terjangkau. Cuma pengiriman agak lama 3 hari tapi masih dalam estimasi.',
        variant: 'Ukuran L',
        likes: 7,
    },
    {
        name: 'Sari A.',
        avatar: 'SA',
        hue: 30,
        rating: 5,
        date: '2 minggu lalu',
        text: 'Mantap! Sudah order ke-3 kali di toko ini, selalu puas. Produk original dan sesuai deskripsi.',
        variant: 'Ukuran S · Warna Putih',
        likes: 23,
    },
    {
        name: 'Doni P.',
        avatar: 'DP',
        hue: 150,
        rating: 4,
        date: '3 minggu lalu',
        text: 'Produk bagus, tolong tambah varian warna ya! Overall sudah puas dengan pembelian ini.',
        variant: 'Ukuran XL',
        likes: 4,
    },
];

const ratingBreakdown = [
    { stars: 5, count: 214, pct: 78 },
    { stars: 4, count: 47, pct: 17 },
    { stars: 3, count: 9, pct: 3 },
    { stars: 2, count: 3, pct: 1 },
    { stars: 1, count: 2, pct: 1 },
];
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
                    class="relative aspect-[4/3] w-full shrink-0 overflow-hidden bg-[#f5f4f0]"
                >
                    <img
                        v-if="product"
                        :src="product.img"
                        :alt="product.name"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-if="product?.discount"
                        class="absolute top-0 left-0 rounded-br-xl bg-(--brand-strong) px-2.5 py-1 text-xs font-bold text-white"
                    >
                        {{ product.discount }}% OFF
                    </div>
                    <div
                        v-else-if="product?.tag"
                        class="absolute top-0 left-0 rounded-br-xl px-2.5 py-1 text-xs font-bold text-white"
                        :class="{
                            'bg-(--brand)': product.tag === 'Bestseller',
                            'bg-(--brand-strong)': product.tag === 'Hot',
                            'bg-(--brand-accent)': product.tag === 'Baru',
                        }"
                    >
                        {{ product.tag }}
                    </div>
                    <button
                        @click="isLiked = !isLiked"
                        class="absolute top-2.5 right-2.5 flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-white/90 shadow-md backdrop-blur-sm"
                    >
                        <Heart
                            class="h-4 w-4 transition-colors"
                            :class="
                                isLiked
                                    ? 'fill-(--brand-strong) text-(--brand-strong)'
                                    : 'text-[#9090a0]'
                            "
                        />
                    </button>
                </div>

                <div v-if="product" class="flex flex-col gap-0">
                    <div class="px-4 pt-3 pb-2">
                        <div class="flex items-baseline gap-2">
                            <span
                                class="text-2xl font-extrabold text-(--brand-strong)"
                                >{{ product.price }}</span
                            >
                            <span
                                v-if="product.originalPrice"
                                class="text-sm text-[#9090a0] line-through"
                                >{{ product.originalPrice }}</span
                            >
                        </div>
                        <h2
                            class="mt-1.5 text-sm leading-snug font-semibold text-[#1c1c22]"
                        >
                            {{ product.name }}
                        </h2>
                        <div
                            class="mt-2 flex flex-wrap items-center gap-2 text-xs text-[#9090a0]"
                        >
                            <div class="flex items-center gap-1">
                                <Star
                                    class="h-3.5 w-3.5 fill-amber-400 stroke-amber-400"
                                /><span class="font-bold text-[#4a4a57]">{{
                                    product.rating
                                }}</span>
                            </div>
                            <span>·</span
                            ><span>{{ formatSold(product.sold) }} terjual</span>
                            <span>·</span
                            ><button
                                @click="activeTab = 'ulasan'"
                                class="text-(--brand)"
                            >
                                {{ mockReviews.length }} ulasan
                            </button>
                        </div>
                    </div>
                    <Separator />
                    <div
                        class="flex items-center gap-2 bg-(--brand-soft) px-4 py-2.5"
                    >
                        <Truck
                            class="h-4 w-4 shrink-0 text-(--brand-secondary)"
                        />
                        <span class="text-xs font-medium text-[#4a4a57]"
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
                                    class="truncate text-xs font-bold text-[#1c1c22]"
                                    >{{ product.store }}</span
                                ><BadgeCheck
                                    class="h-3.5 w-3.5 shrink-0 text-(--brand-accent)"
                                />
                            </div>
                            <p class="text-[10px] text-[#9090a0]">
                                Official Store · Respons cepat
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            class="shrink-0 text-xs"
                            @click="visitStore"
                            ><Store class="mr-1 h-3 w-3" />Kunjungi</Button
                        >
                    </div>
                    <Separator />
                    <div class="flex border-b border-black/8">
                        <button
                            v-for="tab in ['detail', 'ulasan']"
                            :key="tab"
                            @click="activeTab = tab as 'detail' | 'ulasan'"
                            class="flex-1 py-3 text-xs font-bold capitalize transition-colors"
                            :class="
                                activeTab === tab
                                    ? 'border-b-2 border-(--brand) text-(--brand)'
                                    : 'text-[#9090a0]'
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
                        <p class="mb-3 text-xs leading-relaxed text-[#4a4a57]">
                            Produk berkualitas tinggi dari {{ product.store }}.
                            Dibuat dengan bahan pilihan untuk memastikan
                            kenyamanan dan daya tahan maksimal.
                        </p>
                        <div class="rounded-xl bg-[#faf9f6] p-3 text-xs">
                            <p class="mb-2 font-bold text-[#1c1c22]">
                                Spesifikasi
                            </p>
                            <div class="flex flex-col gap-1.5">
                                <div class="flex justify-between">
                                    <span class="text-[#9090a0]">Kategori</span
                                    ><span class="font-medium">{{
                                        product.cat
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#9090a0]">Kondisi</span
                                    ><span class="font-medium">Baru</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#9090a0]">Terjual</span
                                    ><span class="font-medium"
                                        >{{
                                            formatSold(product.sold)
                                        }}
                                        unit</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-4 py-4">
                        <div
                            class="mb-4 flex items-center gap-4 rounded-xl bg-[#faf9f6] p-4"
                        >
                            <div class="flex flex-col items-center">
                                <span
                                    class="text-4xl font-extrabold text-[#1c1c22]"
                                    >{{ product.rating }}</span
                                >
                                <div class="my-1 flex gap-0.5">
                                    <Star
                                        v-for="i in 5"
                                        :key="i"
                                        class="h-3 w-3"
                                        :class="
                                            i <= Math.round(product.rating)
                                                ? 'fill-amber-400 stroke-amber-400'
                                                : 'fill-none stroke-black/20'
                                        "
                                    />
                                </div>
                                <span class="text-[10px] text-[#9090a0]"
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
                                        class="w-4 text-right text-[10px] text-[#9090a0]"
                                        >{{ row.stars }}</span
                                    ><Star
                                        class="h-2.5 w-2.5 fill-amber-400 stroke-amber-400"
                                    />
                                    <div
                                        class="h-1.5 flex-1 overflow-hidden rounded-full bg-black/10"
                                    >
                                        <div
                                            class="h-full rounded-full bg-amber-400"
                                            :style="{ width: `${row.pct}%` }"
                                        />
                                    </div>
                                    <span
                                        class="w-6 text-[10px] text-[#9090a0]"
                                        >{{ row.count }}</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4">
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
                                            class="text-xs font-bold text-[#1c1c22]"
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
                                                            ? 'fill-amber-400 stroke-amber-400'
                                                            : 'fill-none stroke-black/20'
                                                    "
                                                />
                                            </div>
                                            <span
                                                class="text-[10px] text-[#9090a0]"
                                                >{{ review.date }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <p class="text-[10px] text-[#9090a0]">
                                    Varian: {{ review.variant }}
                                </p>
                                <p
                                    class="text-xs leading-relaxed text-[#4a4a57]"
                                >
                                    {{ review.text }}
                                </p>
                                <div
                                    class="flex items-center gap-1 text-[10px] text-[#9090a0]"
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
                class="shrink-0 border-t border-black/8 bg-white px-4 pt-3 pb-6"
            >
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-bold text-[#1c1c22]">Jumlah</span>
                    <div
                        class="flex items-center gap-2 rounded-xl border border-black/10 bg-[#f5f4f0] p-1"
                    >
                        <button
                            @click="qty = Math.max(1, qty - 1)"
                            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-white shadow-xs hover:bg-black/5"
                        >
                            <Minus class="h-3.5 w-3.5" />
                        </button>
                        <span
                            class="min-w-[24px] text-center font-mono text-xs font-bold"
                            >{{ qty }}</span
                        >
                        <button
                            @click="qty++"
                            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-white shadow-xs hover:bg-black/5"
                        >
                            <Plus class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
                <div class="flex gap-2.5">
                    <Button
                        variant="outline"
                        size="lg"
                        class="flex h-12 flex-1 items-center justify-center gap-1.5 border-(--brand) text-xs font-bold text-(--brand)"
                        @click="handleAddToCart"
                        ><ShoppingCart class="h-4 w-4" />Keranjang</Button
                    >
                    <Button
                        variant="amber"
                        size="lg"
                        class="flex h-12 flex-[2] items-center justify-center gap-1.5 text-xs font-bold shadow-md"
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
            class="max-h-[90vh] max-w-4xl overflow-hidden rounded-3xl p-0"
        >
            <div class="flex h-full w-full">
                <!-- LEFT: Image column -->
                <div class="relative flex w-2/5 shrink-0 flex-col bg-[#f5f4f0]">
                    <div class="relative flex-1 overflow-hidden">
                        <img
                            v-if="product"
                            :src="product.img"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <!-- Discount / tag badge -->
                        <div
                            v-if="product?.discount"
                            class="absolute top-0 left-0 rounded-br-2xl bg-(--brand-strong) px-3 py-1.5 text-sm font-bold text-white"
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
                                class="rounded-lg border border-(--brand-accent) bg-(--brand-accent) px-2.5 py-1 text-xs font-black text-black shadow-xs"
                            >
                                {{ t.trim() }}
                            </span>
                        </div>
                        <!-- Actions top-right -->
                        <div class="absolute top-3 right-3 flex flex-col gap-2">
                            <button
                                @click="isLiked = !isLiked"
                                class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-white/90 shadow-md backdrop-blur-sm transition-transform active:scale-90"
                            >
                                <Heart
                                    class="h-4 w-4 transition-colors"
                                    :class="
                                        isLiked
                                            ? 'fill-(--brand-strong) text-(--brand-strong)'
                                            : 'text-[#9090a0]'
                                    "
                                />
                            </button>
                            <button
                                class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-white/90 shadow-md backdrop-blur-sm"
                            >
                                <Share2 class="h-4 w-4 text-[#9090a0]" />
                            </button>
                        </div>
                    </div>

                    <!-- Store Info block below image -->
                    <div
                        v-if="product"
                        class="border-t border-black/8 bg-white p-4"
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
                                        class="truncate text-sm font-bold text-[#1c1c22]"
                                        >{{ product.store }}</span
                                    >
                                    <BadgeCheck
                                        class="h-4 w-4 shrink-0 text-(--brand-accent)"
                                    />
                                </div>
                                <p class="text-[11px] text-[#9090a0]">
                                    Official Store · Respons &lt; 1 jam
                                </p>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                class="shrink-0 text-xs"
                                @click="visitStore"
                            >
                                <Store class="mr-1 h-3 w-3" /> Kunjungi
                            </Button>
                        </div>
                        <!-- Trust badges -->
                        <div class="mt-3 flex flex-col gap-1.5">
                            <div
                                class="flex items-center gap-2 text-xs text-[#4a4a57]"
                            >
                                <ShieldCheck
                                    class="h-3.5 w-3.5 shrink-0 text-(--brand-strong)"
                                />
                                <span>Produk 100% original bergaransi</span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-xs text-[#4a4a57]"
                            >
                                <Truck
                                    class="h-3.5 w-3.5 shrink-0 text-(--brand)"
                                />
                                <span>Gratis Ongkir · Estimasi 2-3 hari</span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-xs text-[#4a4a57]"
                            >
                                <Package
                                    class="h-3.5 w-3.5 shrink-0 text-(--brand-secondary)"
                                />
                                <span>Packing aman bubble wrap + kardus</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Detail column -->
                <div class="flex flex-1 flex-col overflow-hidden">
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
                                            variant="amber"
                                            class="px-2 py-0.5 text-[10px] font-extrabold"
                                            >{{ t.trim() }}</Badge
                                        >
                                    </template>
                                </div>

                                <h2
                                    class="text-xl leading-snug font-extrabold text-[#1c1c22]"
                                >
                                    {{ product.name }}
                                </h2>

                                <!-- Rating row -->
                                <div
                                    class="mt-2 flex flex-wrap items-center gap-3 text-sm text-[#9090a0]"
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
                                                        ? 'fill-amber-400 stroke-amber-400'
                                                        : 'fill-none stroke-black/15'
                                                "
                                            />
                                        </div>
                                        <span
                                            class="font-bold text-[#4a4a57]"
                                            >{{ product.rating }}</span
                                        >
                                    </div>
                                    <span class="text-black/20">|</span>
                                    <span
                                        >{{
                                            formatSold(product.sold)
                                        }}
                                        terjual</span
                                    >
                                    <span class="text-black/20">|</span>
                                    <button
                                        @click="activeTab = 'ulasan'"
                                        class="text-(--brand) hover:underline"
                                    >
                                        {{ mockReviews.length }} ulasan
                                    </button>
                                </div>

                                <!-- Price block -->
                                <div
                                    class="mt-4 rounded-2xl bg-gradient-to-r from-[#fff1e8] to-[#faf9f6] p-4"
                                >
                                    <p
                                        class="mb-0.5 text-[11px] font-medium text-[#9090a0]"
                                    >
                                        Harga
                                    </p>
                                    <div class="flex items-baseline gap-2.5">
                                        <span
                                            class="font-mono text-3xl font-extrabold text-(--brand-strong)"
                                            >{{ product.price }}</span
                                        >
                                        <span
                                            v-if="product.originalPrice"
                                            class="text-base text-[#9090a0] line-through"
                                            >{{ product.originalPrice }}</span
                                        >
                                        <Badge
                                            v-if="product.discount"
                                            class="bg-(--brand-strong) text-xs font-bold text-white"
                                            >Hemat
                                            {{ product.discount }}%</Badge
                                        >
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <!-- Tabs -->
                            <div class="flex border-b border-black/8 px-6">
                                <button
                                    v-for="tab in ['detail', 'ulasan']"
                                    :key="tab"
                                    @click="
                                        activeTab = tab as 'detail' | 'ulasan'
                                    "
                                    class="mr-6 py-3 text-sm font-bold capitalize transition-colors"
                                    :class="
                                        activeTab === tab
                                            ? 'border-b-2 border-(--brand) text-(--brand)'
                                            : 'text-[#9090a0] hover:text-[#4a4a57]'
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

                            <!-- Tab: Detail -->
                            <div
                                v-if="activeTab === 'detail'"
                                class="px-6 py-5"
                            >
                                <p
                                    class="mb-5 text-sm leading-relaxed text-[#4a4a57]"
                                >
                                    {{
                                        product.description ||
                                        'Produk Nike original dengan material premium, daya tahan tinggi, dan kenyamanan maksimal untuk aktivitas sehari-hari.'
                                    }}
                                </p>
                                <div
                                    class="rounded-2xl border border-black/8 bg-[#faf9f6] p-4"
                                >
                                    <p
                                        class="mb-3 text-sm font-bold text-[#1c1c22]"
                                    >
                                        Spesifikasi & Identitas Produk
                                    </p>
                                    <div
                                        class="grid grid-cols-2 gap-x-8 gap-y-2.5 text-sm"
                                    >
                                        <div class="flex justify-between">
                                            <span class="text-[#9090a0]"
                                                >Merk / Brand</span
                                            ><span
                                                class="font-bold text-[#1c1c22]"
                                                >{{
                                                    product.brand || 'Nike'
                                                }}</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[#9090a0]"
                                                >Kode SKU</span
                                            ><span
                                                class="font-mono font-bold text-[#1c1c22]"
                                                >{{
                                                    product.sku ||
                                                    'NK-' + product.id
                                                }}</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[#9090a0]"
                                                >Kategori</span
                                            ><span
                                                class="font-medium text-[#1c1c22]"
                                                >{{ product.cat }}</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[#9090a0]"
                                                >Kondisi</span
                                            ><span
                                                class="font-medium text-[#1c1c22]"
                                                >100% Baru & Original</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[#9090a0]"
                                                >Berat Produk</span
                                            ><span
                                                class="font-medium text-[#1c1c22]"
                                                >{{
                                                    product.weightGram || 500
                                                }}
                                                gram</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[#9090a0]"
                                                >Terjual</span
                                            ><span
                                                class="font-medium text-[#1c1c22]"
                                                >{{
                                                    formatSold(product.sold)
                                                }}
                                                unit</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[#9090a0]"
                                                >Rating</span
                                            ><span
                                                class="font-medium text-[#1c1c22]"
                                                >{{ product.rating }}/5.0</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[#9090a0]"
                                                >Garansi</span
                                            ><span
                                                class="font-medium text-[#1c1c22]"
                                                >Garansi Retur 100%
                                                Original</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Ulasan -->
                            <div v-else class="px-6 py-5">
                                <!-- Rating Summary -->
                                <div
                                    class="mb-5 flex items-center gap-6 rounded-2xl border border-black/8 bg-[#faf9f6] p-4"
                                >
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-5xl font-extrabold text-[#1c1c22]"
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
                                                        ? 'fill-amber-400 stroke-amber-400'
                                                        : 'fill-none stroke-black/20'
                                                "
                                            />
                                        </div>
                                        <span class="text-xs text-[#9090a0]"
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
                                                class="w-4 text-right text-xs text-[#9090a0]"
                                                >{{ row.stars }}</span
                                            >
                                            <Star
                                                class="h-3 w-3 fill-amber-400 stroke-amber-400"
                                            />
                                            <div
                                                class="h-2 flex-1 overflow-hidden rounded-full bg-black/10"
                                            >
                                                <div
                                                    class="h-full rounded-full bg-amber-400 transition-all"
                                                    :style="{
                                                        width: `${row.pct}%`,
                                                    }"
                                                />
                                            </div>
                                            <span
                                                class="w-8 text-xs text-[#9090a0]"
                                                >{{ row.count }}</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Reviews list -->
                                <div class="flex flex-col gap-5">
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
                                                    class="text-sm font-bold text-[#1c1c22]"
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
                                                                    ? 'fill-amber-400 stroke-amber-400'
                                                                    : 'fill-none stroke-black/20'
                                                            "
                                                        />
                                                    </div>
                                                    <span
                                                        class="text-xs text-[#9090a0]"
                                                        >{{ review.date }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <Badge
                                            variant="outline"
                                            class="w-fit px-2 py-0 text-[10px] text-[#9090a0]"
                                            >{{ review.variant }}</Badge
                                        >
                                        <p
                                            class="text-sm leading-relaxed text-[#4a4a57]"
                                        >
                                            {{ review.text }}
                                        </p>
                                        <button
                                            class="flex w-fit items-center gap-1 rounded-lg border border-black/10 px-2.5 py-1 text-xs text-[#9090a0] transition-colors hover:bg-black/5"
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
                                    class="mt-4 flex w-full items-center justify-center gap-1 py-3 text-sm font-bold text-(--brand) hover:underline"
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
                        class="shrink-0 border-t border-black/8 bg-white px-6 py-4"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-[#1c1c22]"
                                >Jumlah Pesanan</span
                            >
                            <div
                                class="flex items-center gap-2 rounded-xl border border-black/10 bg-[#f5f4f0] p-1"
                            >
                                <button
                                    @click="qty = Math.max(1, qty - 1)"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-white shadow-xs hover:bg-black/5"
                                >
                                    <Minus class="h-4 w-4" />
                                </button>
                                <span
                                    class="min-w-[32px] text-center font-mono text-sm font-bold text-[#1c1c22]"
                                    >{{ qty }}</span
                                >
                                <button
                                    @click="qty++"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg bg-white shadow-xs hover:bg-black/5"
                                >
                                    <Plus class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Button
                                variant="outline"
                                size="lg"
                                class="flex h-12 flex-1 items-center justify-center gap-2 border-(--brand) font-bold text-(--brand) hover:bg-(--brand)/5"
                                @click="handleAddToCart"
                            >
                                <ShoppingCart class="h-5 w-5" /> + Keranjang
                            </Button>
                            <Button
                                variant="amber"
                                size="lg"
                                class="flex h-12 flex-[2] items-center justify-center gap-2 font-bold shadow-lg"
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
