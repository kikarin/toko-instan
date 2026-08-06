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
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface Invoice {
    id: number;
    order_number: string;
    customer_name: string;
    customer_email: string;
    total_amount: string;
    total_num: number;
    status: string;
    store_name: string;
    created_at: string;
}

interface Props {
    invoice: Invoice;
}

defineProps<Props>();

function navigate(url: string) {
    router.visit(url);
}
</script>

<template>
    <Head title="Pesanan Berhasil - Toko Instan" />

    <StorefrontLayout>
        <main
            class="mx-auto flex w-full max-w-2xl flex-col items-center p-6 py-12 font-sans"
        >
            <!-- Success Icon Animation -->
            <div
                class="mb-4 flex h-16 w-16 items-center justify-center rounded-3xl border border-[#22a15a30] bg-[#22a15a1a] text-[#22a15a] shadow-lg shadow-[#22a15a]/20"
            >
                <CheckCircle2 class="h-8 w-8" />
            </div>

            <h1
                class="text-center text-2xl font-extrabold tracking-tight text-[#1c1c22]"
            >
                Pesanan Berhasil Dibuat!
            </h1>
            <p class="mt-1 mb-8 max-w-md text-center text-xs text-[#9090a0]">
                Terima kasih telah berbelanja. Invoice pesanan Anda telah dibuat
                dan dana Anda disimpan dengan aman di Escrow Rekening Bersama.
            </p>

            <!-- Invoice Card -->
            <Card
                class="flex w-full flex-col gap-5 border-black/10 p-6 shadow-md"
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
                        variant="amber"
                        class="px-2.5 py-1 text-xs font-bold uppercase"
                    >
                        {{ invoice.status }}
                    </Badge>
                </div>

                <!-- Invoice Breakdown Grid -->
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Store class="h-3.5 w-3.5 text-[#e07c28]" /> Toko
                            Penjual
                        </span>
                        <span class="font-bold text-[#1c1c22]">{{
                            invoice.store_name
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Calendar class="h-3.5 w-3.5 text-[#e07c28]" />
                            Waktu Transaksi
                        </span>
                        <span class="font-mono font-bold text-[#1c1c22]">{{
                            invoice.created_at
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <User class="h-3.5 w-3.5 text-[#e07c28]" /> Nama
                            Pembeli
                        </span>
                        <span class="font-bold text-[#1c1c22]">{{
                            invoice.customer_name
                        }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Mail class="h-3.5 w-3.5 text-[#e07c28]" /> Email
                            Pembeli
                        </span>
                        <span class="truncate font-bold text-[#1c1c22]">{{
                            invoice.customer_email
                        }}</span>
                    </div>
                </div>

                <!-- Total Amount Banner -->
                <div
                    class="mt-2 flex items-center justify-between rounded-2xl border border-[#e07c2820] bg-[#e07c280a] p-4"
                >
                    <span class="text-xs font-bold text-[#4a4a57]"
                        >Total Pembayaran</span
                    >
                    <span
                        class="font-mono text-xl font-extrabold text-[#e07c28]"
                    >
                        {{ invoice.total_amount }}
                    </span>
                </div>

                <!-- Buttons -->
                <div class="mt-2 flex flex-col items-center gap-3 sm:flex-row">
                    <Button
                        variant="amber"
                        class="flex h-11 w-full items-center justify-center gap-2 text-xs font-bold shadow-sm"
                        @click="navigate('/marketplace')"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        <span>Kembali Belanja di Marketplace</span>
                    </Button>
                    <Button
                        variant="outline"
                        class="flex h-11 w-full items-center justify-center gap-2 border-black/12 text-xs font-bold"
                        @click="navigate('/')"
                    >
                        <span>Lihat Dashboard Seller</span>
                    </Button>
                </div>
            </Card>
        </main>
    </StorefrontLayout>
</template>
