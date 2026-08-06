<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ReceiptText, Package, Inbox } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface OrderItem {
    product_name: string;
    qty: number;
}

interface Order {
    order_number: string;
    status: string;
    total_amount: string;
    store_name: string;
    items: OrderItem[];
    created_at: string | null;
}

interface Props {
    orders?: Order[];
}

defineProps<Props>();

const statusVariant: Record<string, 'amber' | 'teal' | 'rose' | 'violetSolid'> =
    {
        pending: 'amber',
        paid: 'teal',
        completed: 'teal',
        cancelled: 'rose',
    };
</script>

<template>
    <Head title="Pesanan Saya - Toko Instan" />

    <StorefrontLayout>
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 sm:p-6">
            <div class="flex items-center gap-2">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#e07c2825] bg-[#e07c2818]"
                >
                    <ReceiptText class="h-4.5 w-4.5 text-[#e07c28]" />
                </div>
                <div>
                    <h1
                        class="text-lg leading-none font-extrabold text-[#1c1c22]"
                    >
                        Pesanan Saya
                    </h1>
                    <p class="mt-0.5 text-[11px] text-[#9090a0]">
                        Riwayat transaksi marketplace kamu
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <div
                    v-for="o in orders ?? []"
                    :key="o.order_number"
                    class="rounded-2xl border border-black/8 bg-white p-4 shadow-2xs"
                >
                    <!-- header -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-2 border-b border-black/5 pb-3"
                    >
                        <div class="flex items-center gap-2">
                            <p
                                class="font-mono text-xs font-bold text-[#1c1c22]"
                            >
                                {{ o.order_number }}
                            </p>
                            <Badge
                                :variant="
                                    statusVariant[o.status?.toLowerCase()] ??
                                    'amber'
                                "
                                class="px-2 py-0 text-[9px] uppercase"
                            >
                                {{ o.status }}
                            </Badge>
                        </div>
                        <p class="text-[10px] text-[#9090a0]">
                            {{ o.store_name }} · {{ o.created_at ?? '-' }}
                        </p>
                    </div>

                    <!-- items -->
                    <div class="flex flex-col gap-2 py-3">
                        <div
                            v-for="it in o.items ?? []"
                            :key="it.product_name"
                            class="flex items-center gap-2.5"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-black/6 bg-[#f5f4f0]"
                            >
                                <Package class="h-4 w-4 text-[#e07c28]" />
                            </div>
                            <p
                                class="flex-1 truncate text-xs font-semibold text-[#1c1c22]"
                            >
                                {{ it.product_name }}
                            </p>
                            <p class="text-xs text-[#9090a0]">{{ it.qty }}x</p>
                        </div>
                    </div>

                    <!-- footer -->
                    <div
                        class="flex items-center justify-between border-t border-black/5 pt-3"
                    >
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-[11px] font-bold text-[#6d4fc2]"
                        >
                            Lihat Detail
                        </Button>
                        <div class="text-right">
                            <p
                                class="text-[9px] tracking-wide text-[#9090a0] uppercase"
                            >
                                Total
                            </p>
                            <p
                                class="font-mono text-sm font-extrabold text-[#1c1c22]"
                            >
                                {{ o.total_amount }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- empty state -->
                <div
                    v-if="!(orders ?? []).length"
                    class="flex flex-col items-center gap-2 py-14 text-center"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-black/6 bg-[#f5f4f0]"
                    >
                        <Inbox class="h-6 w-6 text-[#9090a0]" />
                    </div>
                    <p class="text-sm font-bold text-[#1c1c22]">
                        Belum ada pesanan
                    </p>
                    <p class="text-xs text-[#9090a0]">
                        Pesanan yang kamu buat akan muncul di sini.
                    </p>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
