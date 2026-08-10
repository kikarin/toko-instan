<script setup lang="ts">
import { Heart, Star, ShoppingCart } from 'lucide-vue-next';
import { toast } from '@/components/ui/sonner';
import { useWishlist } from '@/lib/useWishlist';
import type { MarketplaceProduct } from '@/types/product';

interface Props {
    product: MarketplaceProduct;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'click'): void;
    (e: 'add-to-cart', product: MarketplaceProduct): void;
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
    <div class="group relative flex cursor-pointer flex-col overflow-hidden rounded-2xl border-2 border-brand-soft bg-gradient-to-b from-card from-40% to-brand-soft shadow transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl active:scale-[0.98]" @click="emit('click')">
        <!-- ── Top Accent Bar ── -->
        <div class="h-1.5 w-full transition-opacity duration-300 opacity-90 group-hover:opacity-100 bg-brand" />

        <!-- ── Product Image ── -->
        <div class="relative aspect-square w-full overflow-hidden bg-muted">
            <img :src="product.img" :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-108" />

            <!-- Discount Badge — top left (Dynamic Strong Color Gradient) -->
            <div v-if="product.discount"
                class="absolute top-0 left-0 rounded-br-xl px-2.5 py-1 text-[10px] font-black text-white shadow-md backdrop-blur-md bg-brand-strong">
                {{ product.discount }}% OFF
            </div>

            <!-- Tag badge (Bestseller / Hot / Baru) — dynamic theme 4-hex gradients -->
            <div v-else-if="product.tag"
                class="absolute top-0 left-0 rounded-br-xl px-2.5 py-1 text-[10px] font-black text-white shadow-md backdrop-blur-md"
                :class="
                        product.tag === 'Bestseller'
                            ? 'bg-brand'
                            : product.tag === 'Hot'
                                ? 'bg-brand-strong'
                                : 'bg-brand-secondary'
                ">
                {{ product.tag }}
            </div>

            <!-- Wishlist button — top right -->
            <button @click.stop="handleToggleWishlist"
                class="absolute top-2 right-2 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-card/95 shadow-md backdrop-blur-sm transition-all hover:scale-110 active:scale-90"
                title="Wishlist">
                <Heart class="h-4 w-4 transition-colors" :class="isInWishlist(product.id)
                        ? 'fill-brand-strong text-brand-strong'
                        : 'text-muted-foreground hover:text-brand-strong'
                    " />
            </button>

            <!-- Add to cart button — bottom right -->
            <button title="Tambah ke keranjang" @click.stop="emit('add-to-cart', props.product)"
                class="absolute right-2 bottom-2 flex h-8.5 w-8.5 cursor-pointer items-center justify-center rounded-full text-brand-foreground shadow-lg backdrop-blur-sm transition-all hover:scale-115 active:scale-90 bg-brand hover:bg-brand/90">
                <ShoppingCart class="h-4 w-4" />
            </button>

            <!-- Free shipping label — bottom -->
            <div v-if="product.freeShipping"
                class="absolute right-0 bottom-0 left-0 flex items-center gap-1 bg-gradient-to-t from-black/70 to-transparent px-2.5 pt-4 pb-1.5">
                <span
                    class="rounded-md px-1.5 py-0.5 text-[9px] font-black tracking-wider text-brand-foreground uppercase shadow-xs bg-brand">
                    ⚡ Gratis Ongkir
                </span>
            </div>
        </div>

        <!-- ── Product Info (Dynamic Theme Tint) ── -->
        <div class="flex flex-1 flex-col gap-1.5 p-3.5 transition-colors duration-300">
            <!-- Category & SKU -->
            <div v-if="product.sku || product.cat" class="flex items-center justify-between gap-1.5">
                <span v-if="product.cat" class="truncate rounded-md bg-brand-surface px-1.5 py-0.5 text-[8px] font-black tracking-widest text-brand uppercase">
                    {{ product.cat }}
                </span>
                <span v-if="product.sku" class="truncate font-mono text-[9px] font-bold text-muted-foreground/70">
                    SKU: {{ product.sku }}
                </span>
            </div>

            <!-- Product name — 2 lines max -->
            <p class="line-clamp-2 text-xs leading-snug font-extrabold text-foreground group-hover:text-brand-strong transition-colors">
                {{ product.name }}
            </p>

            <!-- Price row (Dynamic Strong Theme Color) -->
            <div class="mt-0.5">
                <!-- Original price (strikethrough) if discount exists -->
                <p v-if="product.originalPrice" class="font-mono text-[10px] leading-none text-muted-foreground line-through">
                    {{ product.originalPrice }}
                </p>
                <!-- Main price -->
                <p class="font-mono text-sm leading-tight font-black sm:text-base text-brand-strong">
                    {{ product.price }}
                </p>
            </div>

            <!-- Rating + Sold + Stock -->
            <div class="mt-auto flex items-center justify-between border-t border-brand/15 pt-2">
                <div class="flex items-center gap-1.5">
                    <div class="flex items-center gap-0.5">
                        <Star class="h-3.5 w-3.5 fill-accent stroke-accent" />
                        <span class="text-[10px] font-extrabold text-muted-foreground">{{
                            product.rating
                            }}</span>
                    </div>
                    <span class="text-[10px] text-muted">·</span>
                    <span class="text-[10px] font-semibold text-muted-foreground">{{ formatSold(product.sold) }} terjual</span>
                </div>
                
                <span v-if="product.stock !== undefined" class="text-[9px] font-bold" :class="product.stock > 5 ? 'text-muted-foreground' : 'text-destructive'">
                    Sisa {{ product.stock }}
                </span>
            </div>
        </div>
    </div>
</template>
