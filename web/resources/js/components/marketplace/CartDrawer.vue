<script setup lang="ts">
import {
    ShoppingCart,
    X,
    Trash2,
    Plus,
    Minus,
    ArrowRight,
    Truck,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

export interface CartItem {
    id: number;
    name: string;
    price: number; // Numeric value
    formattedPrice: string;
    img: string;
    store: string;
    qty: number;
}

interface Props {
    isOpen: boolean;
    items: CartItem[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update-qty', id: number, delta: number): void;
    (e: 'remove-item', id: number): void;
    (e: 'checkout'): void;
}>();

const subtotal = computed(() => {
    return props.items.reduce((sum, item) => sum + item.price * item.qty, 0);
});

const freeShippingThreshold = 300000;
const shippingProgress = computed(() => {
    return Math.min(100, (subtotal.value / freeShippingThreshold) * 100);
});

function fmtRp(n: number) {
    return 'Rp ' + n.toLocaleString('id');
}
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                @click="emit('close')"
                class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs select-none"
            />
        </Transition>

        <!-- Sliding Drawer Panel -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="isOpen"
                class="fixed top-0 right-0 bottom-0 z-50 flex w-full max-w-md flex-col bg-white font-sans shadow-2xl select-none"
            >
                <!-- Drawer Header -->
                <div
                    class="flex items-center justify-between border-b border-black/8 bg-[#faf9f6] p-5"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#e07c281a] text-[#e07c28]"
                        >
                            <ShoppingCart class="h-4 w-4" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-[#1c1c22]">
                                Keranjang Belanja
                            </h2>
                            <p class="text-[11px] text-[#9090a0]">
                                {{ items.length }} item dipilih
                            </p>
                        </div>
                    </div>

                    <button
                        @click="emit('close')"
                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl bg-black/5 text-[#9090a0] transition-colors hover:bg-black/10 hover:text-[#1c1c22]"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Free shipping notice -->
                <div
                    class="flex flex-col gap-2 border-b border-[#e07c2815] bg-[#e07c280a] p-4"
                >
                    <div class="flex items-center justify-between text-xs">
                        <span
                            class="flex items-center gap-1.5 font-bold text-[#e07c28]"
                        >
                            <Truck class="h-4 w-4" />
                            {{
                                subtotal >= freeShippingThreshold
                                    ? 'Selamat! Anda Gratis Ongkir'
                                    : 'Tambah belanja untuk Gratis Ongkir'
                            }}
                        </span>
                        <span
                            class="font-mono text-[11px] font-bold text-[#4a4a57]"
                        >
                            {{ fmtRp(subtotal) }} /
                            {{ fmtRp(freeShippingThreshold) }}
                        </span>
                    </div>
                    <div
                        class="h-1.5 w-full overflow-hidden rounded-full bg-black/10"
                    >
                        <div
                            class="h-full rounded-full bg-[#e07c28] transition-all duration-300"
                            :style="{ width: `${shippingProgress}%` }"
                        />
                    </div>
                </div>

                <!-- Cart Items List -->
                <div class="flex flex-1 flex-col gap-4 overflow-y-auto p-5">
                    <template v-if="items.length > 0">
                        <div
                            v-for="item in items"
                            :key="item.id"
                            class="flex gap-3 rounded-2xl border border-black/8 bg-white p-3.5 shadow-2xs transition-all hover:border-black/15"
                        >
                            <img
                                :src="item.img"
                                :alt="item.name"
                                class="h-18 w-18 shrink-0 rounded-xl border border-black/5 bg-black/5 object-cover"
                            />

                            <div
                                class="flex min-w-0 flex-1 flex-col justify-between"
                            >
                                <div>
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <p
                                            class="line-clamp-2 text-xs leading-snug font-bold text-[#1c1c22]"
                                        >
                                            {{ item.name }}
                                        </p>
                                        <button
                                            @click="
                                                emit('remove-item', item.id)
                                            "
                                            class="shrink-0 cursor-pointer p-1 text-[#9090a0] transition-colors hover:text-red-500"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                    <p
                                        class="mt-0.5 text-[10px] text-[#9090a0]"
                                    >
                                        {{ item.store }}
                                    </p>
                                </div>

                                <div
                                    class="mt-2 flex items-end justify-between"
                                >
                                    <span
                                        class="font-mono text-xs font-extrabold text-[#e07c28]"
                                    >
                                        {{ fmtRp(item.price * item.qty) }}
                                    </span>

                                    <!-- Qty Selector -->
                                    <div
                                        class="flex items-center gap-1.5 rounded-xl border border-black/8 bg-[#f5f4f0] p-1"
                                    >
                                        <button
                                            @click="
                                                emit('update-qty', item.id, -1)
                                            "
                                            class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-lg bg-white text-xs font-bold shadow-2xs hover:bg-black/5"
                                        >
                                            <Minus class="h-3 w-3" />
                                        </button>
                                        <span
                                            class="px-1.5 font-mono text-xs font-bold text-[#1c1c22]"
                                        >
                                            {{ item.qty }}
                                        </span>
                                        <button
                                            @click="
                                                emit('update-qty', item.id, 1)
                                            "
                                            class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-lg bg-white text-xs font-bold shadow-2xs hover:bg-black/5"
                                        >
                                            <Plus class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty state -->
                    <div
                        v-else
                        class="flex flex-col items-center justify-center py-20 text-center text-[#9090a0]"
                    >
                        <ShoppingCart
                            class="mb-3 h-12 w-12 stroke-1 text-[#c8c8d5]"
                        />
                        <p class="text-sm font-bold text-[#4a4a57]">
                            Keranjang Anda Kosong
                        </p>
                        <p class="mt-1 text-xs">
                            Pilih produk menarik di marketplace untuk mulai
                            belanja
                        </p>
                    </div>
                </div>

                <!-- Footer Summary & Checkout Button -->
                <div
                    v-if="items.length > 0"
                    class="flex flex-col gap-3 border-t border-black/8 bg-[#faf9f6] p-5"
                >
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#9090a0]">Subtotal Produk</span>
                        <span
                            class="font-mono text-base font-extrabold text-[#1c1c22]"
                            >{{ fmtRp(subtotal) }}</span
                        >
                    </div>

                    <div
                        class="flex items-center gap-2 text-[10px] font-medium text-[#22a15a]"
                    >
                        <ShieldCheck class="h-3.5 w-3.5 shrink-0" />
                        <span
                            >Transaksi Terproteksi Escrow Toko Instan
                            Guarantee</span
                        >
                    </div>

                    <Button
                        variant="amber"
                        size="lg"
                        class="flex w-full items-center justify-center gap-2 py-3 text-sm font-bold shadow-md"
                        @click="emit('checkout')"
                    >
                        <span>Lanjut ke Checkout</span>
                        <ArrowRight class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
