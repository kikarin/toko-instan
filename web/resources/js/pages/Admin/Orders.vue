<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface OrderRow {
    id: number;
    order_number: string;
    store_name: string;
    customer_name: string | null;
    customer_email: string | null;
    total_amount: string;
    status: string;
    created_at: string | null;
}

defineProps<{
    orders?: OrderRow[];
}>();

const statusVariant: Record<string, 'amber' | 'rose' | 'teal' | 'violetSolid'> =
    {
        pending: 'amber',
        processing: 'violetSolid',
        packed: 'violetSolid',
        shipped: 'teal',
        completed: 'teal',
        cancelled: 'rose',
    };
</script>

<template>
    <Head title="Orders - Admin" />

    <AdminLayout activePage="Orders">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6">
            <div>
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#6d4fc2] uppercase"
                >
                    Admin
                </p>
                <h1 class="text-2xl font-extrabold text-[#1c1c22]">
                    Pesanan Platform
                </h1>
            </div>

            <Card class="overflow-hidden p-0">
                <div
                    class="grid grid-cols-12 gap-2 border-b border-black/5 bg-[#faf9f6] px-4 py-3 text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                >
                    <div class="col-span-3">Order</div>
                    <div class="col-span-2">Toko</div>
                    <div class="col-span-3">Customer</div>
                    <div class="col-span-2">Total</div>
                    <div class="col-span-2">Status</div>
                </div>
                <div
                    v-for="o in orders ?? []"
                    :key="o.id"
                    class="grid grid-cols-12 items-center gap-2 border-b border-black/5 px-4 py-3 text-xs last:border-0"
                >
                    <div class="col-span-3 min-w-0">
                        <p class="truncate font-mono font-bold text-[#1c1c22]">
                            {{ o.order_number }}
                        </p>
                        <p class="text-[10px] text-[#9090a0]">
                            {{ o.created_at }}
                        </p>
                    </div>
                    <div class="col-span-2 truncate text-[#1c1c22]">
                        {{ o.store_name }}
                    </div>
                    <div class="col-span-3 min-w-0">
                        <p class="truncate text-[#1c1c22]">
                            {{ o.customer_name ?? '—' }}
                        </p>
                        <p class="truncate text-[10px] text-[#9090a0]">
                            {{ o.customer_email }}
                        </p>
                    </div>
                    <div class="col-span-2 font-mono font-bold text-[#1c1c22]">
                        {{ o.total_amount }}
                    </div>
                    <div class="col-span-2">
                        <Badge
                            :variant="statusVariant[o.status] ?? 'amber'"
                            class="text-[9px] uppercase"
                        >
                            {{ o.status }}
                        </Badge>
                    </div>
                </div>
                <p
                    v-if="!(orders ?? []).length"
                    class="px-4 py-8 text-center text-xs text-[#9090a0]"
                >
                    Belum ada pesanan.
                </p>
            </Card>
        </main>
    </AdminLayout>
</template>
