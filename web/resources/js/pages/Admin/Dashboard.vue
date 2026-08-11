<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Users,
    Store,
    Package,
    ShoppingCart,
    Wallet,
    ArrowRight,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Card, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { AdminStats } from '@/types/admin';
import type { RecentOrder } from '@/types/admin';
import type { RecentUser } from '@/types/admin';
import type { RecentStore } from '@/types/admin';

interface Props {
    stats?: AdminStats;
    recent_orders?: RecentOrder[];
    recent_users?: RecentUser[];
    recent_stores?: RecentStore[];
}

defineProps<Props>();

const kpiCards = [
    {
        label: 'Total Users',
        key: 'users',
        icon: Users,
        cClass: 'text-primary',
        bgClass: 'bg-primary/10',
    },
    {
        label: 'Toko Aktif',
        key: 'stores',
        icon: Store,
        cClass: 'text-secondary',
        bgClass: 'bg-secondary/10',
    },
    {
        label: 'Produk',
        key: 'products',
        icon: Package,
        cClass: 'text-accent',
        bgClass: 'bg-accent/10',
    },
    {
        label: 'Total Pesanan',
        key: 'orders',
        icon: ShoppingCart,
        cClass: 'text-primary',
        bgClass: 'bg-primary/10',
    },
] as const;

type StatKey = (typeof kpiCards)[number]['key'];

const statusVariant: Record<string, 'default' | 'destructive' | 'secondary' | 'outline'> =
    {
        pending: 'secondary',
        paid: 'default',
        completed: 'default',
        cancelled: 'destructive',
    };
</script>

<template>
    <Head title="Admin Dashboard - Toko Instan" />

    <AdminLayout activePage="Admin">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p
                        class="mb-1 text-xs font-extrabold tracking-widest text-primary uppercase"
                    >
                        Admin Master
                    </p>
                    <h1 class="text-2xl font-extrabold text-foreground">
                        Dashboard Admin
                    </h1>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Card
                    v-for="k in kpiCards"
                    :key="k.label"
                    class="flex items-center gap-3.5 p-4"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl"
                        :class="[k.bgClass, k.cClass]"
                    >
                        <component :is="k.icon" class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="font-mono text-xl leading-none font-extrabold text-foreground"
                        >
                            {{ stats?.[k.key as StatKey] ?? 0 }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">{{ k.label }}</p>
                    </div>
                </Card>
            </div>

            <!-- Revenue banner -->
            <Card
                class="border-none bg-primary p-5"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-[11px] font-bold tracking-widest text-white/60 uppercase"
                        >
                            Total Revenue (completed)
                        </p>
                        <p
                            class="mt-1 font-mono text-3xl font-extrabold text-white"
                        >
                            {{ stats?.revenue ?? 'Rp 0' }}
                        </p>
                    </div>
                    <Wallet class="h-8 w-8 text-white/30" />
                </div>
            </Card>

            <!-- Pending withdrawals -->
            <Card class="flex flex-wrap items-center justify-between gap-3 p-5">
                <div>
                    <p
                        class="text-[11px] font-bold tracking-widest text-[#9090a0] uppercase"
                    >
                        Pending withdraw
                    </p>
                    <p class="mt-1 font-mono text-2xl font-extrabold text-[#1c1c22]">
                        {{ stats?.pending_withdrawals_sum ?? 'Rp 0' }}
                    </p>
                    <p class="mt-1 text-xs text-[#9090a0]">
                        {{ stats?.pending_withdrawals ?? 0 }} permintaan menunggu
                        review
                    </p>
                </div>
                <a
                    href="/admin/withdrawals"
                    class="inline-flex items-center gap-1 text-xs font-bold text-[#e07c28] hover:underline"
                >
                    Kelola penarikan
                    <ArrowRight class="h-3.5 w-3.5" />
                </a>
            </Card>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- Recent Orders -->
                <Card class="p-5">
                    <CardTitle
                        class="mb-3 flex items-center justify-between text-sm"
                    >
                        <span>Pesanan Terbaru</span>
                        <ArrowRight class="h-4 w-4 text-muted-foreground" />
                    </CardTitle>
                    <div class="flex flex-col gap-2">
                        <div
                            v-for="o in (recent_orders ?? []).slice(0, 5)"
                            :key="o.order_number"
                            class="flex items-center justify-between rounded-xl border border-border bg-card px-3 py-2"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate font-mono text-xs font-bold text-foreground"
                                >
                                    {{ o.order_number }}
                                </p>
                                <p class="text-[10px] text-muted-foreground">
                                    {{ o.store_name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <Badge
                                    :variant="
                                        statusVariant[o.status.toLowerCase()] ??
                                        'default'
                                    "
                                    class="px-2 py-0 text-[9px]"
                                >
                                    {{ o.status }}
                                </Badge>
                                <p
                                    class="mt-1 font-mono text-xs font-bold text-foreground"
                                >
                                    {{ o.total_amount }}
                                </p>
                            </div>
                        </div>
                        <p
                            v-if="!(recent_orders ?? []).length"
                            class="py-4 text-center text-xs text-muted-foreground"
                        >
                            Belum ada pesanan.
                        </p>
                    </div>
                </Card>

                <!-- Recent Users -->
                <Card class="p-5">
                    <CardTitle class="mb-3 text-sm">User Terbaru</CardTitle>
                    <div class="flex flex-col gap-2">
                        <div
                            v-for="u in (recent_users ?? []).slice(0, 5)"
                            :key="u.id"
                            class="flex items-center justify-between rounded-xl border border-border bg-card px-3 py-2"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate text-xs font-bold text-foreground"
                                >
                                    {{ u.name }}
                                </p>
                                <p class="truncate text-[10px] text-muted-foreground">
                                    {{ u.email }}
                                </p>
                            </div>
                            <Badge
                                :variant="
                                    u.role === 'admin'
                                        ? 'default'
                                        : u.role === 'seller'
                                          ? 'secondary'
                                          : 'outline'
                                "
                                class="px-2 py-0 text-[9px] uppercase"
                            >
                                {{ u.role }}
                            </Badge>
                        </div>
                    </div>
                </Card>
            </div>
        </main>
    </AdminLayout>
</template>
