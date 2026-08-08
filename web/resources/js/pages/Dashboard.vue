<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    DollarSign,
    Package,
    Users,
    TrendingUp,
    Receipt,
    Landmark,
    Sparkles,
    Wallet,
    Plus,
    ShoppingCart,
    Settings,
    ChevronRight,
    Box,
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
        name: "Nike Air Force 1 '07 Triple White",
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
        <main
            class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- ── Top Command Banner ── -->
            <div
                class="relative overflow-hidden rounded-3xl bg-zinc-900 p-6 text-white shadow-xl sm:p-8"
            >
                <div
                    class="absolute -top-10 -right-10 h-72 w-72 rounded-full bg-gradient-to-br from-[#e07c28]/40 via-amber-500/20 to-transparent blur-3xl"
                />
                <div
                    class="absolute -bottom-10 -left-10 h-72 w-72 rounded-full bg-gradient-to-br from-emerald-600/30 via-teal-500/10 to-transparent blur-3xl"
                />

                <div
                    class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-xs font-black text-amber-400"
                            >
                                <Sparkles class="h-3.5 w-3.5" /> SELLER COMMAND
                                CENTER
                            </span>
                            <Badge
                                variant="teal"
                                class="text-[10px] font-extrabold uppercase"
                            >
                                TOKO AKTIF (BUKA)
                            </Badge>
                        </div>
                        <h1
                            class="text-2xl font-black tracking-tight text-white sm:text-3xl"
                        >
                            Selamat Datang, Nike Official Store 👋
                        </h1>
                        <p class="max-w-2xl text-xs text-zinc-400 sm:text-sm">
                            Pantau kesehatan finansial toko Anda, kelola alur
                            transaksi pembeli, dan cairkan saldo dompet toko
                            secara real-time.
                        </p>
                    </div>

                    <!-- Quick Wallet Balance Pill & Actions (Mobile Responsive) -->
                    <div
                        class="flex w-full shrink-0 flex-col items-stretch gap-3 sm:w-auto sm:flex-row sm:items-center"
                    >
                        <div
                            class="flex items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/5 p-3 px-4 backdrop-blur-md sm:justify-start"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 font-bold text-emerald-400"
                                >
                                    <Wallet class="h-5 w-5" />
                                </div>
                                <div>
                                    <span
                                        class="block text-[10px] font-bold tracking-wider text-zinc-400 uppercase"
                                        >Saldo Siap Tarik</span
                                    >
                                    <span
                                        class="font-mono text-base font-black text-emerald-400 sm:text-lg"
                                        >Rp 95.400.000</span
                                    >
                                </div>
                            </div>
                        </div>

                        <Button
                            variant="amber"
                            size="lg"
                            class="w-full cursor-pointer rounded-2xl px-5 text-xs font-extrabold shadow-lg shadow-amber-500/25 sm:w-auto"
                            @click="navigate('/wallet')"
                        >
                            <Landmark class="mr-2 h-4 w-4" /> Tarik Saldo /
                            Dompet
                        </Button>
                    </div>
                </div>

                <!-- Quick Action Shortcuts Bar -->
                <div
                    class="relative z-10 mt-6 grid grid-cols-2 gap-3 border-t border-white/10 pt-6 sm:grid-cols-4"
                >
                    <button
                        @click="navigate('/products/create')"
                        class="group flex cursor-pointer items-center gap-2.5 rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition-all hover:bg-white/10"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 font-bold text-amber-400"
                        >
                            <Plus class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-white transition-colors group-hover:text-amber-400"
                                >Tambah Produk</span
                            >
                            <span class="text-[10px] text-zinc-400"
                                >Buat katalog baru</span
                            >
                        </div>
                    </button>

                    <button
                        @click="navigate('/orders')"
                        class="group flex cursor-pointer items-center gap-2.5 rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition-all hover:bg-white/10"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 font-bold text-emerald-400"
                        >
                            <ShoppingCart class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-white transition-colors group-hover:text-emerald-400"
                                >Kelola Pesanan</span
                            >
                            <span class="text-[10px] text-zinc-400"
                                >3 Order perlu dikirim</span
                            >
                        </div>
                    </button>

                    <button
                        @click="navigate('/store-settings')"
                        class="group flex cursor-pointer items-center gap-2.5 rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition-all hover:bg-white/10"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-500/20 font-bold text-indigo-400"
                        >
                            <Settings class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-white transition-colors group-hover:text-indigo-400"
                                >Pengaturan Toko</span
                            >
                            <span class="text-[10px] text-zinc-400"
                                >Banner & Legalitas PKP</span
                            >
                        </div>
                    </button>

                    <button
                        @click="navigate('/catalog')"
                        class="group flex cursor-pointer items-center gap-2.5 rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition-all hover:bg-white/10"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-500/20 font-bold text-violet-400"
                        >
                            <Box class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-white transition-colors group-hover:text-violet-400"
                                >Taksonomi Katalog</span
                            >
                            <span class="text-[10px] text-zinc-400"
                                >Kategori & Label Promo</span
                            >
                        </div>
                    </button>
                </div>
            </div>

            <!-- ── KPI Cards Grid ── -->
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

            <!-- ── Revenue Analytics Area Chart Card ── -->
            <Card class="rounded-3xl border-black/8 bg-white p-6 shadow-xs">
                <CardHeader
                    class="mb-4 flex flex-row items-center justify-between p-0"
                >
                    <div>
                        <CardTitle class="text-lg font-black text-[#1c1c22]"
                            >Grafik Omzet & Growth Revenue</CardTitle
                        >
                        <CardDescription class="mt-0.5 text-xs text-zinc-500">
                            Analisis tren pendapatan bersih toko per
                            {{
                                period === 'Hari'
                                    ? 'hari'
                                    : period === 'Minggu'
                                      ? 'minggu'
                                      : 'bulan'
                            }}
                        </CardDescription>
                    </div>

                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center gap-2 text-xs font-bold text-zinc-600"
                        >
                            <span
                                class="inline-block h-2.5 w-2.5 rounded-full bg-[#e07c28]"
                            />
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
                <Card
                    class="flex flex-col justify-between rounded-3xl border-black/8 bg-white p-6 shadow-xs xl:col-span-4"
                >
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <CardTitle
                                class="text-base font-black text-[#1c1c22]"
                                >Alur Funnel Pesanan</CardTitle
                            >
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-auto p-0 text-xs font-bold text-amber-600"
                                @click="navigate('/orders')"
                            >
                                Lihat Semua
                                <ChevronRight class="ml-0.5 h-3.5 w-3.5" />
                            </Button>
                        </div>
                        <CardDescription class="mb-5 text-xs text-zinc-500"
                            >Status realtime alur pesanan
                            pembeli</CardDescription
                        >
                        <OrderFunnel :orderFlow="displayOrderFlow" />
                    </div>

                    <div
                        class="mt-6 flex items-center justify-between rounded-2xl border border-amber-500/20 bg-amber-500/10 p-4"
                    >
                        <span class="text-xs font-extrabold text-amber-900"
                            >Total Order Terverifikasi</span
                        >
                        <span
                            class="font-mono text-base font-black text-[#1c1c22]"
                        >
                            {{ totalOrdersThisMonth.toLocaleString('id') }}
                            Transaksi
                        </span>
                    </div>
                </Card>

                <!-- Top Sellers Products -->
                <Card
                    class="rounded-3xl border-black/8 bg-white p-6 shadow-xs xl:col-span-5"
                >
                    <div class="mb-1 flex items-center justify-between">
                        <CardTitle class="text-base font-black text-[#1c1c22]"
                            >Produk Terlaris (Top Selling Items)</CardTitle
                        >
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-auto p-0 text-xs font-bold text-amber-600"
                            @click="navigate('/products')"
                        >
                            Katalog <ChevronRight class="ml-0.5 h-3.5 w-3.5" />
                        </Button>
                    </div>
                    <CardDescription class="mb-4 text-xs text-zinc-500"
                        >Peringkat produk berdasarkan total GMV
                        penjualan</CardDescription
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
