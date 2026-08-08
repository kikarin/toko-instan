<script setup lang="ts">
import {
    ShoppingCart,
    Trash2,
    Plus,
    Minus,
    ArrowRight,
    Truck,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetFooter,
} from '@/components/ui/sheet';

export interface CartItem {
    id: number;
    name: string;
    price: number;
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

const subtotal = computed(() =>
    props.items.reduce((sum, item) => sum + item.price * item.qty, 0),
);

const freeShippingThreshold = 300000;
const shippingProgress = computed(() =>
    Math.min(100, (subtotal.value / freeShippingThreshold) * 100),
);

function fmtRp(n: number) {
    return 'Rp ' + n.toLocaleString('id');
}

function handleOpenChange(open: boolean) {
    if (!open) {
        emit('close');
    }
}
</script>

<template>
    <Sheet :open="isOpen" @update:open="handleOpenChange">
        <SheetContent
            side="right"
            class="flex w-[85vw] max-w-md flex-col gap-0 p-0 sm:w-full"
        >
            <!-- Sheet Header -->
            <SheetHeader class="border-b border-black/8 bg-[#faf9f6] px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#e07c281a] text-[#e07c28]"
                    >
                        <ShoppingCart class="h-4 w-4" />
                    </div>
                    <div>
                        <SheetTitle class="text-base font-bold text-[#1c1c22]">
                            Keranjang Belanja
                        </SheetTitle>
                        <SheetDescription class="text-[11px] text-[#9090a0]">
                            {{ items.length }} item dipilih
                        </SheetDescription>
                    </div>
                </div>
            </SheetHeader>

            <!-- Free Shipping Progress -->
            <div
                class="flex flex-col gap-2 border-b border-[#e07c2815] bg-[#e07c280a] px-4 py-3"
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

            <!-- Cart Items -->
            <div class="flex flex-1 flex-col gap-4 overflow-y-auto p-5">
                <template v-if="items.length > 0">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex gap-3 rounded-2xl bg-[#faf9f6] p-3.5 transition-all"
                    >
                        <img
                            :src="item.img"
                            :alt="item.name"
                            class="h-16 w-16 shrink-0 rounded-xl border border-black/5 bg-black/5 object-cover"
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
                                        @click="emit('remove-item', item.id)"
                                        class="shrink-0 cursor-pointer p-1 text-[#9090a0] transition-colors hover:text-red-500"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <p class="mt-0.5 text-[10px] text-[#9090a0]">
                                    {{ item.store }}
                                </p>
                            </div>

                            <div class="mt-2 flex items-end justify-between">
                                <span
                                    class="font-mono text-xs font-extrabold text-[#e07c28]"
                                >
                                    {{ fmtRp(item.price * item.qty) }}
                                </span>

                                <!-- Qty Selector -->
                                <div
                                    class="flex items-center gap-1.5 rounded-xl border border-black/8 bg-white p-1"
                                >
                                    <button
                                        @click="emit('update-qty', item.id, -1)"
                                        class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-lg bg-[#f5f4f0] text-xs font-bold hover:bg-black/8"
                                    >
                                        <Minus class="h-3 w-3" />
                                    </button>
                                    <span
                                        class="px-1.5 font-mono text-xs font-bold text-[#1c1c22]"
                                    >
                                        {{ item.qty }}
                                    </span>
                                    <button
                                        @click="emit('update-qty', item.id, 1)"
                                        class="flex h-5 w-5 cursor-pointer items-center justify-center rounded-lg bg-[#f5f4f0] text-xs font-bold hover:bg-black/8"
                                    >
                                        <Plus class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
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
                        Pilih produk menarik di marketplace untuk mulai belanja
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <SheetFooter
                v-if="items.length > 0"
                class="flex-col gap-3 border-t border-black/8 bg-[#faf9f6] px-5 py-4"
            >
                <div class="flex items-center justify-between text-xs">
                    <span class="text-[#9090a0]">Subtotal Produk</span>
                    <span
                        class="font-mono text-base font-extrabold text-[#1c1c22]"
                    >
                        {{ fmtRp(subtotal) }}
                    </span>
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
                    class="flex w-full items-center justify-center gap-2 text-sm font-bold shadow-md"
                    @click="emit('checkout')"
                >
                    <span>Lanjut ke Checkout</span>
                    <ArrowRight class="h-4 w-4" />
                </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
