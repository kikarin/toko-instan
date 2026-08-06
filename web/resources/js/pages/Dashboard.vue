<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    DollarSign,
    Package,
    Users,
    TrendingUp,
    Receipt,
    Landmark,
    Clock,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import KpiCard from '@/components/dashboard/KpiCard.vue';
import OrderFunnel from '@/components/dashboard/OrderFunnel.vue';
import RevenueChart from '@/components/dashboard/RevenueChart.vue';
import SellerWalletCard from '@/components/dashboard/SellerWalletCard.vue';
import TopSellersList from '@/components/dashboard/TopSellersList.vue';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

interface Props {
    kpis?: any[];
    orderFlow?: any[];
    topSellers?: any[];
    wallet?: any;
}

const props = defineProps<Props>();

type Period = 'Hari' | 'Minggu' | 'Bulan';
const period = ref<Period>('Bulan');

function genRev(n: number, base: number, spread: number) {
    return Array.from({ length: n }, (_, i) => ({
        i,
        v: Math.round(
            base +
                (Math.sin(i * 0.9) * 0.4 + Math.cos(i * 1.5) * 0.3 + 0.5) *
                    spread,
        ),
    }));
}

const revSeries: Record<Period, { i: number; v: number }[]> = {
    Hari: genRev(30, 1_800_000, 2_400_000),
    Minggu: genRev(12, 11_000_000, 14_000_000),
    Bulan: genRev(12, 44_000_000, 28_000_000),
};

const revLabels: Record<Period, string[]> = {
    Hari: Array.from({ length: 30 }, (_, i) => `${i + 1}`),
    Minggu: [
        'W1',
        'W2',
        'W3',
        'W4',
        'W5',
        'W6',
        'W7',
        'W8',
        'W9',
        'W10',
        'W11',
        'W12',
    ],
    Bulan: [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Agu',
        'Sep',
        'Okt',
        'Nov',
        'Des',
    ],
};

const kpiIcons = [DollarSign, Package, Users, TrendingUp, Receipt, Landmark];

const defaultKpis = [
    {
        label: 'Gross Revenue',
        value: 'Rp 214Jt',
        delta: '+18.4%',
        up: true,
        sub: 'vs bulan lalu',
        c: '#e07c28',
        cs: 'rgba(224,124,40,0.12)',
    },
    {
        label: 'Total Pesanan',
        value: '8.341',
        delta: '+11.2%',
        up: true,
        sub: 'order masuk',
        c: '#0e9f8a',
        cs: 'rgba(14,159,138,0.12)',
    },
    {
        label: 'Unique Visitor',
        value: '54.921',
        delta: '+26.7%',
        up: true,
        sub: 'sesi unik',
        c: '#3b82f6',
        cs: 'rgba(59,130,246,0.12)',
    },
    {
        label: 'Konversi',
        value: '4.12%',
        delta: '-0.3%',
        up: false,
        sub: 'dari pengunjung',
        c: '#e0405a',
        cs: 'rgba(224,64,90,0.12)',
    },
    {
        label: 'Avg. Order',
        value: 'Rp 256rb',
        delta: '+6.8%',
        up: true,
        sub: 'per transaksi',
        c: '#6d4fc2',
        cs: 'rgba(109,79,194,0.12)',
    },
    {
        label: 'Saldo Dompet',
        value: 'Rp 38.4Jt',
        delta: 'live',
        up: null,
        sub: 'siap tarik',
        c: '#22a15a',
        cs: 'rgba(34,161,90,0.12)',
    },
];

const displayKpis = computed(() => {
    if (props.kpis && props.kpis.length > 0) {
        return props.kpis.map((k, i) => ({
            ...k,
            icon: kpiIcons[i % kpiIcons.length],
        }));
    }

    return defaultKpis.map((k, i) => ({ ...k, icon: kpiIcons[i] }));
});

const defaultOrderFlow = [
    { label: 'Pending', n: 124, c: '#d97706' },
    { label: 'Diproses', n: 312, c: '#3b82f6' },
    { label: 'Dikemas', n: 198, c: '#6d4fc2' },
    { label: 'Dikirim', n: 541, c: '#0e9f8a' },
    { label: 'Selesai', n: 6821, c: '#22a15a' },
    { label: 'Dibatalkan', n: 148, c: '#e0405a' },
];

const displayOrderFlow = computed(() => props.orderFlow || defaultOrderFlow);

const defaultTopSellers = [
    {
        name: 'NovaBatik Studio',
        gmv: 'Rp 18.4Jt',
        orders: 412,
        rating: 4.9,
        badge: 'top' as const,
        avatar: 'NB',
        hue: 220,
    },
    {
        name: 'KuliKain Official',
        gmv: 'Rp 14.1Jt',
        orders: 318,
        rating: 4.8,
        badge: 'pro' as const,
        avatar: 'KK',
        hue: 280,
    },
    {
        name: 'Jaya Elektronik',
        gmv: 'Rp 11.7Jt',
        orders: 287,
        rating: 4.7,
        badge: null,
        avatar: 'JE',
        hue: 190,
    },
    {
        name: 'Warung Digital ID',
        gmv: 'Rp 9.2Jt',
        orders: 234,
        rating: 4.6,
        badge: null,
        avatar: 'WD',
        hue: 150,
    },
    {
        name: 'Mode Nusantara',
        gmv: 'Rp 7.8Jt',
        orders: 198,
        rating: 4.5,
        badge: null,
        avatar: 'MN',
        hue: 30,
    },
];

const displayTopSellers = computed(() => props.topSellers || defaultTopSellers);

const totalOrdersThisMonth = computed(() =>
    displayOrderFlow.value.reduce((s, d) => s + d.n, 0),
);
</script>

<template>
    <Head title="Dashboard - Toko Instan" />

    <AppLayout activePage="Dashboard" v-model:period="period">
        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-6">
            <!-- Heading -->
            <div class="flex items-end justify-between">
                <div>
                    <p
                        class="mb-1 text-[11px] font-bold tracking-widest text-[#9090a0] uppercase"
                    >
                        Ringkasan · Agustus 2026
                    </p>
                    <h1
                        class="text-2xl font-extrabold tracking-tight text-[#1c1c22]"
                    >
                        Performa Toko
                    </h1>
                </div>
                <p
                    class="flex items-center gap-1 font-mono text-xs text-[#c8c8d5]"
                >
                    <Clock class="h-3.5 w-3.5 text-[#9090a0]" />
                    Diperbarui 2 menit lalu
                </p>
            </div>

            <!-- KPI Cards Grid -->
            <div
                class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
            >
                <KpiCard
                    v-for="(kpi, i) in displayKpis"
                    :key="i"
                    :label="kpi.label"
                    :value="kpi.value"
                    :delta="kpi.delta"
                    :up="kpi.up"
                    :sub="kpi.sub"
                    :color="kpi.c"
                    :softColor="kpi.cs"
                    :sparkData="revSeries[period].slice(-8).map((d) => d.v)"
                    :icon="kpi.icon"
                />
            </div>

            <!-- Revenue Area Chart -->
            <Card class="p-6">
                <CardHeader
                    class="mb-4 flex flex-row items-center justify-between p-0"
                >
                    <div>
                        <CardTitle>Revenue</CardTitle>
                        <CardDescription class="mt-1">
                            Pendapatan bersih per
                            {{
                                period === 'Hari'
                                    ? 'hari'
                                    : period === 'Minggu'
                                      ? 'minggu'
                                      : 'bulan'
                            }}
                        </CardDescription>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-[#9090a0]">
                        <span
                            class="inline-block h-1 w-3.5 rounded-full bg-[#e07c28]"
                        />
                        <span class="font-medium">Revenue</span>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <RevenueChart
                        :data="revSeries[period]"
                        :labels="revLabels[period]"
                    />
                </CardContent>
            </Card>

            <!-- Bottom Section: Funnel + Top Sellers + Wallet -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 xl:grid-cols-12">
                <!-- Order Funnel -->
                <Card class="flex flex-col justify-between p-5 xl:col-span-4">
                    <div>
                        <CardTitle class="text-base">Alur Pesanan</CardTitle>
                        <CardDescription class="mt-1 mb-5"
                            >Status realtime semua order</CardDescription
                        >
                        <OrderFunnel :orderFlow="displayOrderFlow" />
                    </div>

                    <div
                        class="mt-6 flex items-center justify-between rounded-xl border border-[#e07c2825] bg-[#e07c280f] p-3.5"
                    >
                        <span class="text-xs font-semibold text-[#e07c28]"
                            >Total order bulan ini</span
                        >
                        <span
                            class="font-mono text-base font-extrabold text-[#1c1c22]"
                        >
                            {{ totalOrdersThisMonth.toLocaleString('id') }}
                        </span>
                    </div>
                </Card>

                <!-- Top Sellers -->
                <Card class="p-5 xl:col-span-5">
                    <CardTitle class="text-base">Top Seller</CardTitle>
                    <CardDescription class="mt-1 mb-4"
                        >Berdasarkan GMV bulan ini</CardDescription
                    >
                    <TopSellersList :sellers="displayTopSellers" />
                </Card>

                <!-- Seller Wallet Widget -->
                <div class="xl:col-span-3">
                    <SellerWalletCard />
                </div>
            </div>
        </main>
    </AppLayout>
</template>
