<script setup lang="ts">
import { Heart, Star, ShoppingBag } from 'lucide-vue-next';
import { ref } from 'vue';

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

defineProps<Props>();
const emit = defineEmits<{ (e: 'click'): void }>();

const isLiked = ref(false);

// Format sold count like Tokopedia: 1.2rb, 24rb, 1jt+
function formatSold(n: number): string {
    if (n >= 1_000_000) return `${(n / 1_000_000).toFixed(1)}jt+`;
    if (n >= 1_000) return `${(n / 1_000).toFixed(n >= 10_000 ? 0 : 1)}rb+`;
    return String(n);
}
</script>

<template>
    <div
        class="group flex cursor-pointer flex-col overflow-hidden rounded-xl bg-white shadow-sm transition-all duration-200 active:scale-[0.98]"
        @click="emit('click')"
    >
        <!-- ── Product Image ── -->
        <div class="relative aspect-square w-full overflow-hidden bg-[#f5f4f0]">
            <img
                :src="product.img"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />

            <!-- Discount Badge — top left (Tokopedia style red pill) -->
            <div
                v-if="product.discount"
                class="absolute top-0 left-0 rounded-br-xl bg-[#e02020] px-2 py-0.5 text-[10px] font-bold text-white"
            >
                {{ product.discount }}%
            </div>

            <!-- Tag badge (Bestseller / Hot / Baru) — only if no discount -->
            <div
                v-else-if="product.tag"
                class="absolute top-0 left-0 rounded-br-xl px-2 py-0.5 text-[10px] font-bold text-white"
                :class="{
                    'bg-[#e07c28]': product.tag === 'Bestseller',
                    'bg-[#e0405a]': product.tag === 'Hot',
                    'bg-[#0d9488]': product.tag === 'Baru',
                }"
            >
                {{ product.tag }}
            </div>

            <!-- Wishlist button — top right -->
            <button
                @click.stop="isLiked = !isLiked"
                class="absolute top-1.5 right-1.5 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-white/90 shadow-sm backdrop-blur-sm transition-transform active:scale-90"
            >
                <Heart
                    class="h-3.5 w-3.5 transition-colors"
                    :class="isLiked ? 'fill-[#e0405a] text-[#e0405a]' : 'text-[#c8c8d5]'"
                />
            </button>

            <!-- Free shipping label — bottom -->
            <div
                v-if="product.freeShipping"
                class="absolute bottom-0 left-0 right-0 flex items-center gap-1 bg-gradient-to-t from-[#00000060] to-transparent px-2 pt-3 pb-1.5"
            >
                <span class="rounded bg-[#00aa5b] px-1.5 py-0.5 text-[9px] font-bold text-white">
                    Gratis Ongkir
                </span>
            </div>
        </div>

        <!-- ── Product Info ── -->
        <div class="flex flex-1 flex-col gap-1 px-2 pt-2 pb-2.5">
            <!-- Product name — 2 lines max -->
            <p class="line-clamp-2 text-xs leading-snug text-[#1c1c22]">
                {{ product.name }}
            </p>

            <!-- Price row -->
            <div class="mt-0.5">
                <!-- Original price (strikethrough) if discount exists -->
                <p v-if="product.originalPrice" class="text-[9px] text-[#9090a0] line-through leading-none">
                    {{ product.originalPrice }}
                </p>
                <!-- Main price -->
                <p class="text-sm font-extrabold leading-tight text-[#e02020]">
                    {{ product.price }}
                </p>
            </div>

            <!-- Rating + Sold -->
            <div class="mt-auto flex items-center gap-1.5 pt-1">
                <div class="flex items-center gap-0.5">
                    <Star class="h-2.5 w-2.5 fill-amber-400 stroke-amber-400" />
                    <span class="text-[10px] text-[#9090a0]">{{ product.rating }}</span>
                </div>
                <span class="text-[10px] text-[#c8c8d5]">·</span>
                <span class="text-[10px] text-[#9090a0]">{{ formatSold(product.sold) }} terjual</span>
            </div>

            <!-- Store name -->
            <p class="truncate text-[9px] text-[#b0b0bf]">{{ product.store }}</p>
        </div>
    </div>
</template>
