<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    CreditCard,
    Store,
    Calendar,
    ShoppingBag,
    Loader2,
    ArrowRight,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useStoreName } from '@/composables/useStoreName';
import type { OrderInvoice } from '@/types/order';

interface Props {
    invoice: OrderInvoice;
}

const props = defineProps<Props>();

const { storeName } = useStoreName();
const isPaying = ref(false);

function confirmPayment() {
    isPaying.value = true;
    router.post(`/pay/${props.invoice.order_number}/confirm`, undefined, {
        onFinish: () => {
            isPaying.value = false;
        },
    });
}

function navigate(url: string) {
    router.visit(url);
}
</script>

<template>
    <Head :title="`Pembayaran — ${storeName}`" />

    <StorefrontLayout>
        <main
            class="mx-auto flex w-full max-w-2xl flex-col items-center px-4 pt-6 pb-28 font-sans sm:p-6 sm:py-12"
        >
            <div
                class="mb-4 flex h-16 w-16 items-center justify-center rounded-3xl border border-brand/30 bg-brand-surface text-brand"
            >
                <CreditCard class="h-8 w-8" />
            </div>

            <h1
                class="text-center text-2xl font-black tracking-tight text-foreground"
            >
                Selesaikan Pembayaran
            </h1>
            <p class="mt-1 mb-8 max-w-md text-center text-xs text-muted-foreground">
                Pesanan {{ invoice.order_number }} menunggu pembayaran.
            </p>

            <Card
                class="flex w-full flex-col gap-5 rounded-2xl border-border bg-card p-6 shadow-md"
            >
                <div
                    class="flex items-center justify-between border-b border-border pb-4"
                >
                    <div>
                        <p
                            class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                        >
                            Nomor Invoice
                        </p>
                        <p
                            class="mt-0.5 font-mono text-base font-extrabold text-foreground"
                        >
                            {{ invoice.order_number }}
                        </p>
                    </div>
                    <Badge variant="default" class="text-xs font-bold uppercase">
                        {{ invoice.status }}
                    </Badge>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="flex flex-col gap-1">
                        <span
                            class="flex items-center gap-1.5 text-muted-foreground"
                        >
                            <Store class="h-3.5 w-3.5" /> Toko Penjual
                        </span>
                        <span class="font-bold text-foreground">{{
                            invoice.store_name
                        }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span
                            class="flex items-center gap-1.5 text-muted-foreground"
                        >
                            <Calendar class="h-3.5 w-3.5" /> Waktu Transaksi
                        </span>
                        <span class="font-mono font-bold text-foreground">{{
                            invoice.created_at
                        }}</span>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-border bg-card p-4"
                >
                    <span class="text-xs font-bold text-muted-foreground"
                        >Total Pembayaran</span
                    >
                    <span class="font-mono text-xl font-black text-brand">
                        {{ invoice.total_amount }}
                    </span>
                </div>

                <div class="flex flex-col items-center gap-3 sm:flex-row">
                    <Button
                        size="lg"
                        class="flex h-12 w-full items-center justify-center gap-2 bg-brand font-bold text-brand-foreground hover:opacity-90 border-0"
                        :disabled="isPaying"
                        @click="confirmPayment"
                    >
                        <Loader2 v-if="isPaying" class="h-4 w-4 animate-spin" />
                        <template v-else>
                            <span>Bayar Sekarang</span>
                            <ArrowRight class="h-4 w-4" />
                        </template>
                    </Button>
                    <Button
                        variant="outline"
                        class="flex h-12 w-full items-center justify-center text-xs font-bold"
                        @click="navigate('/' + (usePage().props.store as any)?.slug)"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        <span>Kembali Belanja</span>
                    </Button>
                </div>
            </Card>
        </main>
    </StorefrontLayout>
</template>