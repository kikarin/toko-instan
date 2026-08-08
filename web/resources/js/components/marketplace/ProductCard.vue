<script setup lang="ts">
import { Heart, Star, ShoppingCart } from 'lucide-vue-next';
import { toast } from '@/components/ui/sonner';
import { useWishlist } from '@/lib/useWishlist';

interface Product {
    id: number;
    name: string;
    price: string;
    priceNum?: number;
    sold: number;
    rating: number;
    store: string;
    img: string;
    tag: string | null;
    cat: string;
    discount?: number;
    originalPrice?: string;
    freeShipping?: boolean;
}

interface Props {
    product: Product;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'click'): void;
    (e: 'add-to-cart', product: Product): void;
}>();

const { isInWishlist, toggleWishlist } = useWishlist();

function handleToggleWishlist() {
    const added = toggleWishlist(props.product);

    if (added) {
        toast.success(`${props.product.name} ditambahkan ke Wishlist!`);
    } else {
        toast.info(`${props.product.name} dihapus dari Wishlist`);
    }
}

// Format sold count like Tokopedia: 1.2rb, 24rb, 1jt+
function formatSold(n: number): string {
    if (n >= 1_000_000) {
        return `${(n / 1_000_000).toFixed(1)}jt+`;
    }

    if (n >= 1_000) {
        return `${(n / 1_000).toFixed(n >= 10_000 ? 0 : 1)}rb+`;
    }

    return String(n);
}
</script>

<template>
    <div
        class="group relative flex cursor-pointer flex-col overflow-hidden rounded-2xl border-2 shadow-md transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl active:scale-[0.98]"
        :style="{
            borderColor: 'var(--brand-soft)',
            background:
                'linear-gradient(180deg, #ffffff 40%, var(--brand-soft) 100%)',
        }"
        @click="emit('click')"
    >
        <!-- ── 4-Hex Theme Top Gradient Bar ── -->
        <div
            class="h-1.5 w-full transition-opacity duration-300"
            :style="{
                background:
                    'linear-gradient(90deg, var(--brand), var(--brand-secondary), var(--brand-accent), var(--brand-strong))',
            }"
        />

        <!-- ── Product Image ── -->
        <div class="relative aspect-square w-full overflow-hidden bg-[#f5f4f0]">
            <img
                :src="product.img"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            <!-- Discount Badge — top left (Dynamic Strong Color Gradient) -->
            <div
                v-if="product.discount"
                class="absolute top-0 left-0 rounded-br-xl px-2.5 py-0.5 text-[10px] font-black text-white shadow-md"
                :style="{
                    background:
                        'linear-gradient(135deg, var(--brand-strong), var(--brand-strong))',
                }"
            >
                {{ product.discount }}% OFF
            </div>

            <!-- Tag badge (Bestseller / Hot / Baru) — dynamic theme 4-hex gradients -->
            <div
                v-else-if="product.tag"
                class="absolute top-0 left-0 rounded-br-xl px-2.5 py-0.5 text-[10px] font-black text-white shadow-md"
                :style="{
                    background:
                        product.tag === 'Bestseller'
                            ? 'linear-gradient(135deg, var(--brand), var(--brand-secondary))'
                            : product.tag === 'Hot'
                              ? 'linear-gradient(135deg, var(--brand-strong), var(--brand-accent))'
                              : 'linear-gradient(135deg, var(--brand-accent), var(--brand-secondary))',
                }"
            >
                {{ product.tag }}
            </div>

            <!-- Wishlist button — top right -->
            <button
                @click.stop="handleToggleWishlist"
                class="absolute top-2 right-2 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-white/90 shadow-xs backdrop-blur-sm transition-transform active:scale-90"
            >
                <Heart
                    class="h-3.5 w-3.5 transition-colors"
                    :class="
                        isInWishlist(product.id)
                            ? 'fill-[var(--brand-strong)] text-[var(--brand-strong)]'
                            : 'text-[#c8c8d5]'
                    "
                />
            </button>

            <!-- Add to cart button — bottom right (Dynamic 4-Hex Theme Gradient) -->
            <button
                title="Tambah ke keranjang"
                @click.stop="emit('add-to-cart', props.product)"
                class="absolute right-2 bottom-2 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-white shadow-lg backdrop-blur-sm transition-all hover:scale-110 active:scale-90"
                :style="{
                    background:
                        'linear-gradient(135deg, var(--brand), var(--brand-strong))',
                }"
            >
                <ShoppingCart class="h-4 w-4" />
            </button>

            <!-- Free shipping label — bottom -->
            <div
                v-if="product.freeShipping"
                class="absolute right-0 bottom-0 left-0 flex items-center gap-1 bg-gradient-to-t from-black/60 to-transparent px-2 pt-3 pb-1.5"
            >
                <span
                    class="rounded-md px-1.5 py-0.5 text-[9px] font-black tracking-wider text-white uppercase shadow-xs"
                    :style="{
                        background:
                            'linear-gradient(90deg, var(--brand-accent), var(--brand-secondary))',
                    }"
                >
                    Gratis Ongkir
                </span>
            </div>
        </div>

        <!-- ── Product Info (Dynamic Theme Tint) ── -->
        <div
            class="flex flex-1 flex-col gap-1 p-3 transition-colors duration-300"
        >
            <!-- Product name — 2 lines max -->
            <p
                class="line-clamp-2 text-xs leading-snug font-bold text-[#1c1c22]"
            >
                {{ product.name }}
            </p>

            <!-- Price row (Dynamic Strong Theme Color) -->
            <div class="mt-0.5">
                <!-- Original price (strikethrough) if discount exists -->
                <p
                    v-if="product.originalPrice"
                    class="font-mono text-[9px] leading-none text-[#9090a0] line-through"
                >
                    {{ product.originalPrice }}
                </p>
                <!-- Main price -->
                <p
                    class="font-mono text-sm leading-tight font-black sm:text-base"
                    :style="{ color: 'var(--brand-strong, #e0405a)' }"
                >
                    {{ product.price }}
                </p>
            </div>

            <!-- Rating + Sold -->
            <div
                class="mt-auto flex items-center gap-1.5 border-t border-[var(--brand)]/15 pt-1.5"
            >
                <div class="flex items-center gap-0.5">
                    <Star class="h-3 w-3 fill-amber-400 stroke-amber-400" />
                    <span class="text-[10px] font-bold text-[#4a4a57]">{{
                        product.rating
                    }}</span>
                </div>
                <span class="text-[10px] text-[#c8c8d5]">·</span>
                <span class="text-[10px] font-medium text-[#9090a0]"
                    >{{ formatSold(product.sold) }} terjual</span
                >
            </div>

            <!-- Store name badge -->
            <p class="truncate text-[9px] font-bold text-zinc-400">
                Nike Official
            </p>
        </div>
    </div>
</template>
