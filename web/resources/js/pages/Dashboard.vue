<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    DollarSign,
    Package,
    Users,
    TrendingUp,
    Receipt,
    Landmark,
    Clock,
    Sparkles,
    Wallet,
    ArrowUpRight,
    Plus,
    ShoppingCart,
    Store,
    Settings,
    ShieldCheck,
    ChevronRight,
    ExternalLink,
    Box,
    Activity,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import KpiCard from '@/components/dashboard/KpiCard.vue';
import OrderFunnel from '@/components/dashboard/OrderFunnel.vue';
import RevenueChart from '@/components/dashboard/RevenueChart.vue';
import SellerWalletCard from '@/components/dashboard/SellerWalletCard.vue';
import TopSellersList from '@/components/dashboard/TopSellersList.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
    store?: any;
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
        value: 'Rp 214.5Jt',
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
        sub: 'order terkonfirmasi',
        c: '#0e9f8a',
        cs: 'rgba(14,159,138,0.12)',
    },
    {
        label: 'Pengunjung Toko',
        value: '54.921',
        delta: '+26.7%',
        up: true,
        sub: 'sesi unik storefront',
        c: '#3b82f6',
        cs: 'rgba(59,130,246,0.12)',
    },
    {
        label: 'Tingkat Konversi',
        value: '4.12%',
        delta: '-0.3%',
        up: false,
        sub: 'dari pengunjung',
        c: '#e0405a',
        cs: 'rgba(224,64,90,0.12)',
    },
    {
        label: 'Rata-rata Order (AOV)',
        value: 'Rp 256rb',
        delta: '+6.8%',
        up: true,
        sub: 'per nilai belanja',
        c: '#6d4fc2',
        cs: 'rgba(109,79,194,0.12)',
    },
    {
        label: 'Saldo Dompet Live',
        value: 'Rp 95.4Jt',
        delta: 'live',
        up: null,
        sub: 'siap ditarik',
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
        name: 'Nike Air Force 1 \'07 Triple White',
        gmv: 'Rp 48.4Jt',
        orders: 312,
        rating: 4.9,
        badge: 'top' as const,
        avatar: 'AF',
        hue: 220,
    },
    {
        name: 'Nike Dunk Low Retro Panda',
        gmv: 'Rp 34.1Jt',
        orders: 218,
        rating: 4.8,
        badge: 'pro' as const,
        avatar: 'DL',
        hue: 280,
    },
    {
        name: 'Nike Air Max 270 Black White',
        gmv: 'Rp 21.7Jt',
        orders: 187,
        rating: 4.7,
        badge: null,
        avatar: 'AM',
        hue: 190,
    },
    {
        name: 'Nike Zoom Fly 5 Running',
        gmv: 'Rp 19.2Jt',
        orders: 134,
        rating: 4.6,
        badge: null,
        avatar: 'ZF',
        hue: 150,
    },
    {
        name: 'Nike Tech Fleece Hoodie',
        gmv: 'Rp 17.8Jt',
        orders: 98,
        rating: 4.5,
        badge: null,
        avatar: 'TF',
        hue: 30,
    },
];

const displayTopSellers = computed(() => props.topSellers || defaultTopSellers);

const totalOrdersThisMonth = computed(() =>
    displayOrderFlow.value.reduce((s, d) => s + d.n, 0),
);

function navigate(url: string) {
    router.visit(url);
}
</script>

<template>
    <Head title="Dashboard Seller Hub — Toko Instan" />

    <AppLayout activePage="Dashboard" v-model:period="period">
        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-4 sm:p-6 lg:p-8">
            <!-- ── Top Command Banner ── -->
            <div class="relative overflow-hidden rounded-3xl bg-zinc-900 p-6 sm:p-8 text-white shadow-xl">
                <div class="absolute -right-10 -top-10 h-72 w-72 rounded-full bg-gradient-to-br from-[#e07c28]/40 via-amber-500/20 to-transparent blur-3xl" />
                <div class="absolute -left-10 -bottom-10 h-72 w-72 rounded-full bg-gradient-to-br from-emerald-600/30 via-teal-500/10 to-transparent blur-3xl" />

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-400/10 px-3 py-1 text-xs font-black text-amber-400 border border-amber-400/20">
                                <Sparkles class="h-3.5 w-3.5" /> SELLER COMMAND CENTER
                            </span>
                            <Badge variant="teal" class="font-extrabold text-[10px] uppercase">
                                TOKO AKTIF (BUKA)
                            </Badge>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            Selamat Datang, Nike Official Store 👋
                        </h1>
                        <p class="text-xs sm:text-sm text-zinc-400 max-w-2xl">
                            Pantau kesehatan finansial toko Anda, kelola alur transaksi pembeli, dan cairkan saldo dompet toko secara real-time.
                        </p>
                    </div>

                    <!-- Quick Wallet Balance Pill & Actions (Mobile Responsive) -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0 w-full sm:w-auto">
                        <div class="flex items-center gap-3 p-3 px-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md justify-between sm:justify-start">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold shrink-0">
                                    <Wallet class="h-5 w-5" />
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Saldo Siap Tarik</span>
                                    <span class="text-base sm:text-lg font-black text-emerald-400 font-mono">Rp 95.400.000</span>
                                </div>
                            </div>
                        </div>

                        <Button
                            variant="amber"
                            size="lg"
                            class="w-full sm:w-auto px-5 text-xs font-extrabold rounded-2xl shadow-lg shadow-amber-500/25 cursor-pointer"
                            @click="navigate('/wallet')"
                        >
                            <Landmark class="mr-2 h-4 w-4" /> Tarik Saldo / Dompet
                        </Button>
                    </div>
                </div>

                <!-- Quick Action Shortcuts Bar -->
                <div class="relative z-10 grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-white/10">
                    <button
                        @click="navigate('/products/create')"
                        class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all text-left cursor-pointer group"
                    >
                        <div class="h-8 w-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold shrink-0">
                            <Plus class="h-4 w-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-white group-hover:text-amber-400 transition-colors block">Tambah Produk</span>
                            <span class="text-[10px] text-zinc-400">Buat katalog baru</span>
                        </div>
                    </button>

                    <button
                        @click="navigate('/orders')"
                        class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all text-left cursor-pointer group"
                    >
                        <div class="h-8 w-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold shrink-0">
                            <ShoppingCart class="h-4 w-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-white group-hover:text-emerald-400 transition-colors block">Kelola Pesanan</span>
                            <span class="text-[10px] text-zinc-400">3 Order perlu dikirim</span>
                        </div>
                    </button>

                    <button
                        @click="navigate('/store-settings')"
                        class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all text-left cursor-pointer group"
                    >
                        <div class="h-8 w-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold shrink-0">
                            <Settings class="h-4 w-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-white group-hover:text-indigo-400 transition-colors block">Pengaturan Toko</span>
                            <span class="text-[10px] text-zinc-400">Banner & Legalitas PKP</span>
                        </div>
                    </button>

                    <button
                        @click="navigate('/catalog')"
                        class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all text-left cursor-pointer group"
                    >
                        <div class="h-8 w-8 rounded-xl bg-violet-500/20 text-violet-400 flex items-center justify-center font-bold shrink-0">
                            <Box class="h-4 w-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-white group-hover:text-violet-400 transition-colors block">Taksonomi Katalog</span>
                            <span class="text-[10px] text-zinc-400">Kategori & Label Promo</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- ── KPI Cards Grid ── -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
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

            <!-- ── Revenue Analytics Area Chart Card ── -->
            <Card class="p-6 rounded-3xl border-black/8 shadow-xs bg-white">
                <CardHeader class="mb-4 flex flex-row items-center justify-between p-0">
                    <div>
                        <CardTitle class="text-lg font-black text-[#1c1c22]">Grafik Omzet & Growth Revenue</CardTitle>
                        <CardDescription class="mt-0.5 text-xs text-zinc-500">
                            Analisis tren pendapatan bersih toko per {{ period === 'Hari' ? 'hari' : period === 'Minggu' ? 'minggu' : 'bulan' }}
                        </CardDescription>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-xs text-zinc-600 font-bold">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#e07c28]" />
                            <span>Gross Revenue</span>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <RevenueChart
                        :data="revSeries[period]"
                        :labels="revLabels[period]"
                    />
                </CardContent>
            </Card>

            <!-- ── Bottom Section: Order Funnel + Top Sellers + Wallet ── -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 xl:grid-cols-12">
                <!-- Order Funnel -->
                <Card class="flex flex-col justify-between p-6 rounded-3xl border-black/8 shadow-xs bg-white xl:col-span-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <CardTitle class="text-base font-black text-[#1c1c22]">Alur Funnel Pesanan</CardTitle>
                            <Button variant="ghost" size="sm" class="text-xs font-bold text-amber-600 p-0 h-auto" @click="navigate('/orders')">
                                Lihat Semua <ChevronRight class="h-3.5 w-3.5 ml-0.5" />
                            </Button>
                        </div>
                        <CardDescription class="mb-5 text-xs text-zinc-500">Status realtime alur pesanan pembeli</CardDescription>
                        <OrderFunnel :orderFlow="displayOrderFlow" />
                    </div>

                    <div class="mt-6 flex items-center justify-between rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4">
                        <span class="text-xs font-extrabold text-amber-900">Total Order Terverifikasi</span>
                        <span class="font-mono text-base font-black text-[#1c1c22]">
                            {{ totalOrdersThisMonth.toLocaleString('id') }} Transaksi
                        </span>
                    </div>
                </Card>

                <!-- Top Sellers Products -->
                <Card class="p-6 rounded-3xl border-black/8 shadow-xs bg-white xl:col-span-5">
                    <div class="flex items-center justify-between mb-1">
                        <CardTitle class="text-base font-black text-[#1c1c22]">Produk Terlaris (Top Selling Items)</CardTitle>
                        <Button variant="ghost" size="sm" class="text-xs font-bold text-amber-600 p-0 h-auto" @click="navigate('/products')">
                            Katalog <ChevronRight class="h-3.5 w-3.5 ml-0.5" />
                        </Button>
                    </div>
                    <CardDescription class="mb-4 text-xs text-zinc-500">Peringkat produk berdasarkan total GMV penjualan</CardDescription>
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
