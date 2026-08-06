<script setup lang="ts">
import { Heart, Star } from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';

interface Product {
    id: number;
    name: string;
    price: string;
    sold: number;
    rating: number;
    store: string;
    img: string;
    tag: string | null;
    cat: string;
}

interface Props {
    product: Product;
}

defineProps<Props>();

const isLiked = ref(false);
const isHovered = ref(false);

const tagVariants: Record<string, 'amber' | 'rose' | 'teal'> = {
    Bestseller: 'amber',
    Hot: 'rose',
    Baru: 'teal',
};
</script>

<template>
    <div
        class="flex cursor-pointer flex-col overflow-hidden rounded-2xl border border-[#00000012] bg-white transition-all duration-200"
        :class="[
            isHovered
                ? '-translate-y-1 border-[#00000020] shadow-lg'
                : 'shadow-xs',
        ]"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
    >
        <!-- Product Image Container -->
        <div class="relative aspect-square overflow-hidden bg-[#ede9e3]">
            <img
                :src="product.img"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 ease-out"
                :class="[isHovered ? 'scale-106' : 'scale-100']"
            />
            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"
            />

            <Badge
                v-if="product.tag"
                :variant="tagVariants[product.tag] || 'amber'"
                class="absolute top-2.5 left-2.5 px-2 py-0.5 text-[10px] font-bold shadow-xs"
            >
                {{ product.tag }}
            </Badge>

            <!-- Like Button -->
            <button
                @click.stop="isLiked = !isLiked"
                class="absolute top-2.5 right-2.5 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border border-black/10 bg-white/90 backdrop-blur-xs transition-transform active:scale-90"
            >
                <Heart
                    class="h-3.5 w-3.5 transition-colors"
                    :class="[
                        isLiked
                            ? 'fill-[#e0405a] text-[#e0405a]'
                            : 'text-[#9090a0]',
                    ]"
                />
            </button>

            <!-- Rating badge -->
            <div class="absolute bottom-2 left-2.5 flex items-center gap-1">
                <Star class="h-3 w-3 fill-amber-400 stroke-amber-400" />
                <span
                    class="font-mono text-[10px] font-medium text-white drop-shadow-xs"
                >
                    {{ product.rating }}
                </span>
            </div>
        </div>

        <!-- Details -->
        <div class="flex flex-1 flex-col gap-1 p-3.5">
            <p
                class="line-clamp-2 text-xs leading-snug font-semibold text-[#1c1c22]"
            >
                {{ product.name }}
            </p>
            <p class="text-[11px] text-[#9090a0]">{{ product.store }}</p>

            <div class="mt-auto flex items-end justify-between pt-2">
                <span class="font-mono text-sm font-extrabold text-[#e07c28]">
                    {{ product.price }}
                </span>
                <span class="font-mono text-[10px] text-[#c8c8d5]">
                    {{ product.sold.toLocaleString('id') }} terjual
                </span>
            </div>
        </div>
    </div>
</template>
