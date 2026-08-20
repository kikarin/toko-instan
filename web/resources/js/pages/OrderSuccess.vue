<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Store,
    Calendar,
    User,
    Mail,
    ShoppingBag,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { useStoreName } from '@/composables/useStoreName';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import type { OrderInvoice } from '@/types/order';

interface Props {
    invoice: OrderInvoice;
}

defineProps<Props>();

const { storeName } = useStoreName();

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
            <!-- Success Icon Animation (Mengikuti warna brand/tema) -->
            <div
                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-brand/30 bg-brand-soft text-brand shadow-md shadow-brand/10"
            >
                <CheckCircle2 class="h-8 w-8" />
            </div>

            <h1
                class="text-center text-2xl font-black tracking-tight text-foreground"
            >
                Pesanan {{ storeName }} Berhasil Dibuat!
            </h1>
            <p class="mt-1 mb-8 max-w-md text-center text-xs text-muted-foreground">
                Terima kasih telah berbelanja di {{ storeName }}. Pesanan
                Anda sedang diproses dengan garansi 100% keaslian.
            </p>

            <!-- Invoice Card -->
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
                    <!-- Badge Status PENDING (Menyesuaikan aksen tema atau warna brand) -->
                    <Badge
                        class="border-none px-2.5 py-1 text-xs font-bold uppercase rounded-xl bg-brand text-brand-foreground shadow-xs"
                    >
                        {{ invoice.status }}
                    </Badge>
                </div>

                <!-- Invoice Breakdown Grid -->
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-muted-foreground">
                            <Store class="h-3.5 w-3.5 text-brand" /> Toko
                            Penjual
                        </span>
                        <span class="font-bold text-foreground">{{
                            invoice.store_name
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-muted-foreground">
                            <Calendar class="h-3.5 w-3.5 text-brand" />
                            Waktu Transaksi
                        </span>
                        <span class="font-mono font-bold text-foreground">{{
                            invoice.created_at
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-muted-foreground">
                            <User class="h-3.5 w-3.5 text-brand" /> Nama Pembeli
                        </span>
                        <span class="font-bold text-foreground">{{
                            invoice.customer_name
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-muted-foreground">
                            <Mail class="h-3.5 w-3.5 text-brand" /> Email
                            Pembeli
                        </span>
                        <span class="truncate font-bold text-foreground">{{
                            invoice.customer_email
                        }}</span>
                    </div>
                </div>

                <!-- Total Amount Banner (Otomatis mendukung tema Light & Dark Card serta warna Brand) -->
                <div
                    class="mt-2 flex items-center justify-between rounded-2xl border border-border bg-muted/60 p-4 text-foreground shadow-xs"
                >
                    <span class="text-xs font-bold text-muted-foreground"
                        >Total Pembayaran</span
                    >
                    <span class="font-mono text-xl font-black text-brand">
                        {{ invoice.total_amount }}
                    </span>
                </div>

                <!-- Buttons (Tombol Utama menggunakan warna Brand, Tombol Kedua menggunakan Outline konsisten) -->
                <div class="mt-2 flex flex-col gap-3">
                    <Button
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-brand text-brand-foreground text-xs font-bold shadow-md hover:opacity-90 border-0"
                        @click="navigate('/' + (usePage().props.store as any)?.slug + '/orders')"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        <span>Lihat Riwayat Pesanan Saya</span>
                    </Button>
                    <Button
                        variant="outline"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-2xl border-border text-xs font-bold text-foreground hover:bg-muted"
                        @click="navigate('/' + (usePage().props.store as any)?.slug)"
                    >
                        <span>Kembali Belanja</span>
                    </Button>
                </div>
            </Card>
        </main>
    </StorefrontLayout>
</template>
