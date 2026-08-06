<script setup lang="ts">
import {
    X,
    Star,
    ShoppingCart,
    Plus,
    Minus,
    ShieldCheck,
    Truck,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

export interface ProductDetail {
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

watch(
    () => props.product,
    () => {
        qty.value = 1;
        isLiked.value = false;
    },
);

function handleAddToCart() {
    if (props.product) {
        emit('add-to-cart', props.product, qty.value);
        emit('close');
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="product"
                @click="emit('close')"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs select-none"
            >
                <div
                    @click.stop
                    class="relative flex w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-black/10 bg-white font-sans shadow-2xl md:flex-row"
                >
                    <!-- Close button -->
                    <button
                        @click="emit('close')"
                        class="absolute top-3 right-3 z-10 flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-black/10 bg-white/80 text-[#9090a0] backdrop-blur-xs transition-colors hover:text-[#1c1c22]"
                    >
                        <X class="h-4 w-4" />
                    </button>

                    <!-- Product Image Left -->
                    <div
                        class="relative aspect-square bg-[#ede9e3] md:aspect-auto md:w-1/2"
                    >
                        <img
                            :src="product.img"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <Badge
                            v-if="product.tag"
                            variant="amber"
                            class="absolute top-3 left-3 px-2.5 py-0.5 text-xs font-bold shadow-xs"
                        >
                            {{ product.tag }}
                        </Badge>
                    </div>

                    <!-- Details Right -->
                    <div
                        class="flex flex-col justify-between gap-4 p-6 md:w-1/2"
                    >
                        <div>
                            <!-- Store Tag -->
                            <div class="mb-2 flex items-center gap-2">
                                <Avatar fallback="ST" :hue="200" size="sm" />
                                <span
                                    class="text-xs font-semibold text-[#4a4a57]"
                                    >{{ product.store }}</span
                                >
                                <Badge
                                    variant="teal"
                                    class="px-1.5 py-0 text-[9px]"
                                    >Verified Store</Badge
                                >
                            </div>

                            <!-- Title & Category -->
                            <h2
                                class="mb-2 text-lg leading-tight font-extrabold text-[#1c1c22]"
                            >
                                {{ product.name }}
                            </h2>

                            <!-- Rating & Sold Count -->
                            <div
                                class="mb-4 flex items-center gap-3 text-xs text-[#9090a0]"
                            >
                                <div
                                    class="flex items-center gap-1 font-mono font-bold text-amber-500"
                                >
                                    <Star
                                        class="h-3.5 w-3.5 fill-amber-400 stroke-amber-400"
                                    />
                                    {{ product.rating }}
                                </div>
                                <span>·</span>
                                <span
                                    class="font-mono font-semibold text-[#4a4a57]"
                                >
                                    {{ product.sold.toLocaleString('id') }}
                                    Terjual
                                </span>
                                <span>·</span>
                                <Badge
                                    variant="outline"
                                    class="text-[10px] uppercase"
                                >
                                    {{ product.cat }}
                                </Badge>
                            </div>

                            <!-- Price Display -->
                            <div
                                class="mb-4 rounded-2xl border border-[#e07c2820] bg-[#e07c280a] p-3.5"
                            >
                                <p class="text-[10px] text-[#9090a0]">
                                    Harga Spesial Marketplace
                                </p>
                                <p
                                    class="mt-0.5 font-mono text-2xl leading-none font-extrabold text-[#e07c28]"
                                >
                                    {{ product.price }}
                                </p>
                            </div>

                            <!-- Trust Badges -->
                            <div
                                class="flex flex-col gap-1.5 text-xs text-[#4a4a57]"
                            >
                                <div class="flex items-center gap-2">
                                    <Truck
                                        class="h-4 w-4 shrink-0 text-teal-600"
                                    />
                                    <span
                                        >Pengiriman cepat dari toko resmi</span
                                    >
                                </div>
                                <div class="flex items-center gap-2">
                                    <ShieldCheck
                                        class="h-4 w-4 shrink-0 text-amber-600"
                                    />
                                    <span>Jaminan 100% Produk Original</span>
                                </div>
                            </div>
                        </div>

                        <!-- Qty Selector & Add to Cart Action -->
                        <div
                            class="flex flex-col gap-3 border-t border-black/8 pt-3"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#1c1c22]"
                                    >Jumlah Pesanan</span
                                >
                                <div
                                    class="flex items-center gap-2 rounded-xl border border-black/10 bg-[#f5f4f0] p-1"
                                >
                                    <button
                                        @click="qty = Math.max(1, qty - 1)"
                                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-white text-xs font-bold shadow-2xs hover:bg-black/5"
                                    >
                                        <Minus class="h-3.5 w-3.5" />
                                    </button>
                                    <span
                                        class="px-2 font-mono text-xs font-bold text-[#1c1c22]"
                                        >{{ qty }}</span
                                    >
                                    <button
                                        @click="qty++"
                                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-white text-xs font-bold shadow-2xs hover:bg-black/5"
                                    >
                                        <Plus class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>

                            <Button
                                variant="amber"
                                size="lg"
                                class="flex w-full items-center justify-center gap-2 py-3 text-xs font-bold shadow-md"
                                @click="handleAddToCart"
                            >
                                <ShoppingCart class="h-4 w-4" />
                                <span
                                    >Tambah ke Keranjang ·
                                    {{ product.price }}</span
                                >
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
