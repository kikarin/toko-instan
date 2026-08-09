<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Boxes, TrendingDown, TrendingUp } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { StockMovement } from '@/types/inventory';

interface Props {
    product?: {
        id: number;
        name: string;
        img: string | null;
        stock: number;
        sku: string | null;
    };
    movements?: StockMovement[];
}

defineProps<Props>();

function typeLabel(type: string) {
    return type === 'in'
        ? 'Stok Masuk'
        : type === 'out'
          ? 'Stok Keluar'
          : 'Penyesuaian';
}
</script>

<template>
    <Head title="Riwayat Stok — Toko Instan" />

    <AppLayout title="Riwayat Stok" activePage="Stok & Inventory">
        <main class="mx-auto flex w-full max-w-3xl flex-col gap-5 p-4 sm:p-6">
            <div class="flex items-center gap-3">
                <Button
                    variant="outline"
                    size="icon"
                    class="h-9 w-9"
                    @click="router.visit('/inventory')"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <p
                        class="text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                    >
                        Stock Movement
                    </p>
                    <h1
                        class="flex items-center gap-2 text-xl font-extrabold text-[#1c1c22]"
                    >
                        <Boxes class="h-5 w-5" /> Riwayat — {{ product?.name }}
                    </h1>
                    <p class="text-[11px] text-[#9090a0]">
                        {{ product?.sku || 'Tanpa SKU' }} • Stok saat ini
                        <span class="font-bold text-[#1c1c22]">{{
                            product?.stock
                        }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-2.5">
                <div
                    v-for="m in movements ?? []"
                    :key="m.id"
                    class="flex items-center justify-between gap-3 rounded-2xl border border-black/5 bg-white px-4 py-3 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                            :class="
                                m.type === 'out'
                                    ? 'bg-red-50 text-red-500'
                                    : 'bg-green-50 text-green-600'
                            "
                        >
                            <TrendingUp v-if="m.delta > 0" class="h-4 w-4" />
                            <TrendingDown v-else class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#1c1c22]">
                                {{ typeLabel(m.type) }}
                            </p>
                            <p class="text-[10px] text-[#9090a0]">
                                {{ m.reason || 'Tanpa alasan' }} •
                                {{ m.created_at }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p
                            class="font-mono text-sm font-extrabold"
                            :class="
                                m.delta > 0 ? 'text-green-600' : 'text-red-500'
                            "
                        >
                            {{ m.delta > 0 ? '+' : '' }}{{ m.delta }}
                        </p>
                        <p class="text-[10px] text-[#9090a0]">
                            {{ m.stock_before }} → {{ m.stock_after }}
                        </p>
                    </div>
                </div>

                <Card
                    v-if="!(movements ?? []).length"
                    class="py-14 text-center"
                >
                    <CardContent>
                        <p class="text-sm font-semibold text-[#4a4a57]">
                            Belum ada pergerakan stok
                        </p>
                        <p class="mt-1 text-xs text-[#9090a0]">
                            Catat stok masuk/keluar dari halaman inventory.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </main>
    </AppLayout>
</template>
