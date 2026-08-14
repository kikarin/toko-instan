<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Store,
    Calendar,
    User,
    Mail,
    ShoppingBag,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useStoreName } from '@/composables/useStoreName';
import type { OrderInvoice } from '@/types/order';

interface Props {
    invoice: OrderInvoice;
}

defineProps<Props>();

const { storeName } = useStoreName();

const page = usePage();
const isGuest = computed(() => !(page.props.auth as any)?.user);

function navigate(url: string) {
    router.visit(url);
}
</script>

<template>
    <Head :title="`Pesanan Berhasil — ${storeName}`" />

    <StorefrontLayout>
        <main
            class="mx-auto flex w-full max-w-2xl flex-col items-center px-4 pt-6 pb-28 font-sans sm:p-6 sm:py-12"
        >
            <!-- Success Icon Animation -->
            <div
                class="mb-4 flex h-16 w-16 items-center justify-center rounded-3xl border border-emerald-500/30 bg-emerald-50 text-emerald-600 shadow-md shadow-emerald-500/10"
            >
                <CheckCircle2 class="h-8 w-8" />
            </div>

            <h1
                class="text-center text-2xl font-black tracking-tight text-[#1c1c22]"
            >
                Pesanan {{ storeName }} Berhasil Dibuat!
            </h1>
            <p class="mt-1 mb-8 max-w-md text-center text-xs text-[#9090a0]">
                Terima kasih telah berbelanja di {{ storeName }}. Pesanan
                Anda sedang diproses dengan garansi 100% keaslian.
            </p>

            <!-- Invoice Card -->
            <Card
                class="flex w-full flex-col gap-5 rounded-2xl border-black/10 bg-white p-6 shadow-md"
            >
                <div
                    class="flex items-center justify-between border-b border-black/8 pb-4"
                >
                    <div>
                        <p
                            class="text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                        >
                            Nomor Invoice
                        </p>
                        <p
                            class="mt-0.5 font-mono text-base font-extrabold text-[#1c1c22]"
                        >
                            {{ invoice.order_number }}
                        </p>
                    </div>
                    <Badge
                        variant="default"
                        class="border-none bg-black px-2.5 py-1 text-xs font-bold text-accent uppercase"
                    >
                        {{ invoice.status }}
                    </Badge>
                </div>

                <!-- Invoice Breakdown Grid -->
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Store class="h-3.5 w-3.5 text-black" /> Toko
                            Penjual
                        </span>
                        <span class="font-bold text-[#1c1c22]">{{
                            invoice.store_name
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Calendar class="h-3.5 w-3.5 text-black" />
                            Waktu Transaksi
                        </span>
                        <span class="font-mono font-bold text-[#1c1c22]">{{
                            invoice.created_at
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <User class="h-3.5 w-3.5 text-black" /> Nama Pembeli
                        </span>
                        <span class="font-bold text-[#1c1c22]">{{
                            invoice.customer_name
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Mail class="h-3.5 w-3.5 text-black" /> Email
                            Pembeli
                        </span>
                        <span class="truncate font-bold text-[#1c1c22]">{{
                            invoice.customer_email
                        }}</span>
                    </div>
                </div>

                <!-- Total Amount Banner -->
                <div
                    class="mt-2 flex items-center justify-between rounded-2xl border border-border bg-card p-4 text-card-foreground"
                >
                    <span class="text-xs font-bold text-muted-foreground"
                        >Total Pembayaran</span
                    >
                    <span class="font-mono text-xl font-black text-accent">
                        {{ invoice.total_amount }}
                    </span>
                </div>

                <!-- Tracking Info -->
                <div
                    v-if="invoice.tracking_number"
                    class="mt-2 flex flex-col gap-2 rounded-2xl border border-border bg-card p-4"
                >
                    <p class="text-xs font-bold text-muted-foreground">
                        Nomor Resi Pengiriman
                    </p>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-mono text-base font-black text-foreground">
                                {{ invoice.tracking_number }}
                            </p>
                            <p class="text-[10px] text-muted-foreground">
                                {{ invoice.tracking_courier }} · Dikirim pada
                                {{ invoice.shipped_at }}
                            </p>
                        </div>
                        <a
                            v-if="invoice.tracking_url"
                            :href="invoice.tracking_url"
                            target="_blank"
                            rel="noopener"
                        >
                            <Button
                                variant="outline"
                                class="h-10 border-black/12 text-xs font-bold"
                            >
                                Lacak Paket
                            </Button>
                        </a>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-2 flex flex-col items-center gap-3 sm:flex-row">
                    <Button
                        v-if="!isGuest"
                        variant="default"
                        class="flex h-11 w-full items-center justify-center gap-2 bg-foreground text-xs font-bold text-accent shadow-md hover:bg-foreground/90"
                        @click="navigate('/' + (usePage().props.store as any)?.slug + '/orders')"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        <span>Lihat Riwayat Pesanan Saya</span>
                    </Button>
                    <Button
                        variant="outline"
                        class="flex h-11 w-full items-center justify-center gap-2 border-black/12 text-xs font-bold"
                        @click="navigate('/' + (usePage().props.store as any)?.slug)"
                    >
                        <span>Kembali Belanja</span>
                    </Button>
                </div>
            </Card>
        </main>
    </StorefrontLayout>
</template>
