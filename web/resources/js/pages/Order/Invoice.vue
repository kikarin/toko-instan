<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Printer, Store, ReceiptText } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useStoreName } from '@/composables/useStoreName';
import type { OrderInvoice } from '@/types/order';

interface Props {
    invoice: OrderInvoice;
}

defineProps<Props>();

const page = usePage();
const { storeName } = useStoreName();

const userRole = computed(() => (page.props.auth as any)?.user?.role ?? 'buyer');
const backUrl = computed(() => {
    if (userRole.value === 'seller') return '/orders';
    const storeSlug = (page.props.store as any)?.slug ?? '';
    return `/${storeSlug}/orders`;
});

function formatMoney(value?: number | string): string {
    if (typeof value === 'string') {
        return value;
    }

    return `Rp ${Number(value ?? 0).toLocaleString('id-ID')}`;
}

function goBack() {
    router.visit(backUrl.value);
}

function printInvoice() {
    window.print();
}
</script>

<template>
    <Head :title="`Invoice ${invoice.order_number} — ${storeName}`" />

    <div class="min-h-screen bg-[#f0efec] print:bg-white">
        <!-- Toolbar (hidden when printing) -->
        <div
            class="sticky top-0 z-40 border-b border-black/5 bg-white/90 backdrop-blur-md print:hidden"
        >
            <div
                class="mx-auto flex w-full max-w-4xl items-center justify-between px-4 py-3 sm:px-6"
            >
                <div class="flex items-center gap-3">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="h-9 gap-1.5 rounded-xl text-xs font-bold"
                        @click="goBack"
                    >
                        <ArrowLeft class="h-4 w-4" /> Kembali
                    </Button>
                    <div
                        class="flex h-8 items-center gap-2 rounded-xl bg-foreground px-3 text-accent"
                    >
                        <ReceiptText class="h-4 w-4" />
                        <span class="text-[11px] font-black tracking-wider uppercase"
                            >Invoice</span
                        >
                    </div>
                </div>
                <Button
                    class="gap-2 rounded-xl bg-card text-xs font-bold text-accent hover:bg-foreground"
                    @click="printInvoice"
                >
                    <Printer class="h-4 w-4" /> Cetak / Simpan PDF
                </Button>
            </div>
        </div>

        <!-- Invoice Sheet -->
        <div class="mx-auto w-full max-w-4xl px-4 py-6 sm:px-6 sm:py-10">
            <div
                class="overflow-hidden rounded-3xl border border-black/8 bg-white shadow-lg print:rounded-none print:border-0 print:shadow-none"
            >
                <!-- Header -->
                <div class="border-b border-black/8 p-6 sm:p-8">
                    <div
                        class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-card text-accent"
                            >
                                <Store class="h-5 w-5" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-black tracking-wider text-[#1c1c22] uppercase"
                                >
                                    {{ invoice.store_name }}
                                </p>
                                <p
                                    class="text-[10px] font-semibold text-[#9090a0]"
                                >
                                    Faktur Penjualan
                                </p>
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <p
                                class="text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                            >
                                Nomor Invoice
                            </p>
                            <p
                                class="font-mono text-lg font-black text-[#1c1c22]"
                            >
                                {{ invoice.order_number }}
                            </p>
                            <p
                                class="mt-1 inline-flex rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 uppercase"
                            >
                                {{ invoice.status }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Meta info -->
                <div class="grid grid-cols-2 gap-4 border-b border-black/8 p-6 sm:grid-cols-4 sm:p-8">
                    <div class="flex flex-col gap-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                            >Pembeli</span
                        >
                        <span class="text-xs font-bold text-[#1c1c22]">{{
                            invoice.customer_name
                        }}</span>
                        <span class="text-[10px] text-[#9090a0]">{{
                            invoice.customer_email
                        }}</span>
                        <span
                            v-if="invoice.customer_phone"
                            class="text-[10px] text-[#9090a0]"
                            >{{ invoice.customer_phone }}</span
                        >
                    </div>
                    <div class="flex flex-col gap-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                            >Tanggal</span
                        >
                        <span class="text-xs font-bold text-[#1c1c22]">{{
                            invoice.created_at
                        }}</span>
                    </div>
                    <div class="col-span-2 flex flex-col gap-1">
                        <span
                            class="text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                            >Alamat Pengiriman</span
                        >
                        <span class="text-xs leading-relaxed text-[#1c1c22]">{{
                            invoice.shipping_address || '—'
                        }}</span>
                    </div>
                </div>

                <!-- Items table -->
                <div class="p-6 sm:p-8">
                    <p
                        class="mb-3 text-[10px] font-black tracking-wider text-[#9090a0] uppercase"
                    >
                        Rincian Pesanan
                    </p>
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr
                                class="border-b border-black/8 text-[10px] tracking-wider text-[#9090a0] uppercase"
                            >
                                <th class="py-2 font-bold">Produk</th>
                                <th class="py-2 text-center font-bold">Qty</th>
                                <th class="py-2 text-right font-bold">Harga</th>
                                <th class="py-2 text-right font-bold">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in invoice.items ?? []"
                                :key="item.id"
                                class="border-b border-black/5"
                            >
                                <td class="py-3">
                                    <p class="font-bold text-[#1c1c22]">{{
                                        item.product_name
                                    }}</p>
                                    <p
                                        v-if="item.sku"
                                        class="font-mono text-[10px] text-[#9090a0]"
                                    >
                                        SKU {{ item.sku }}
                                    </p>
                                </td>
                                <td class="py-3 text-center font-bold">{{
                                    item.qty
                                }}</td>
                                <td class="py-3 text-right font-mono">{{
                                    formatMoney(item.price_formatted)
                                }}</td>
                                <td class="py-3 text-right font-mono font-bold">{{
                                    formatMoney(item.subtotal_formatted)
                                }}</td>
                            </tr>
                            <tr v-if="!(invoice.items ?? []).length">
                                <td colspan="4" class="py-4 text-center text-muted-foreground italic">
                                    Tidak ada rincian item untuk pesanan ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Notes -->
                    <p
                        v-if="invoice.notes"
                        class="mt-4 rounded-xl border border-black/5 bg-[#faf9f6] p-3 text-[11px] text-[#4a4a57]"
                    >
                        <span class="font-bold">Catatan:</span>
                        {{ invoice.notes }}
                    </p>

                    <!-- Totals -->
                    <div
                        class="ml-auto mt-6 flex w-full max-w-xs flex-col gap-2 text-xs"
                    >
                        <div
                            class="flex items-center justify-between text-[#4a4a57]"
                        >
                            <span>Subtotal</span>
                            <span class="font-mono font-semibold">{{
                                invoice.subtotal_formatted
                            }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between text-[#4a4a57]"
                        >
                            <span>Ongkos Kirim</span>
                            <span class="font-mono font-semibold">{{
                                invoice.shipping_fee > 0
                                    ? invoice.shipping_fee_formatted
                                    : 'Gratis'
                            }}</span>
                        </div>
                        <div
                            class="mt-2 flex items-center justify-between rounded-xl bg-card p-3.5 text-card-foreground"
                        >
                            <span class="text-xs font-black">Total</span>
                            <span class="font-mono text-base font-black text-accent">{{
                                invoice.total_amount
                            }}</span>
                        </div>
                    </div>
                </div>

                <div
                    class="border-t border-black/5 px-6 py-4 text-center text-[10px] text-[#b0b0bf] sm:px-8"
                >
                    Terima kasih telah berbelanja di {{ invoice.store_name }} ·
                    Invoice ini dibuat otomatis oleh sistem.
                </div>
            </div>
        </div>
    </div>
</template>
