<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Sparkles,
    Wallet,
    Plus,
    ShoppingCart,
    Settings,
    ChevronRight,
    Box,
    Eye,
    Landmark,
} from 'lucide-vue-next';
import { computed } from 'vue';
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
import { useSellerDashboard } from '@/lib/useSellerDashboard';
import { useStoreName } from '@/lib/useStoreName';

interface Props {
    kpis?: any[];
    orderFlow?: any[];
    topSellers?: any[];
    wallet?: any;
    store?: any;
}

const props = defineProps<Props>();

const { storeName } = useStoreName();

const propsRef = computed(() => props);

const {
    period,
    revSeries,
    revLabels,
    displayKpis,
    displayOrderFlow,
    displayTopSellers,
    totalOrdersThisMonth,
    navigate,
} = useSellerDashboard(propsRef);
</script>

<template>
    <Head title="Dashboard Seller Hub — Toko Instan" />

    <AppLayout activePage="Dashboard" v-model:period="period">
        <main
            class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- ── Top Command Banner ── -->
            <div
                class="relative overflow-hidden rounded-3xl bg-primary p-6 text-primary-foreground shadow-xl sm:p-8"
            >
                <div
                    class="absolute -top-10 -right-10 h-72 w-72 rounded-full bg-gradient-to-br from-white/30 via-white/10 to-transparent blur-3xl"
                />
                <div
                    class="absolute -bottom-10 -left-10 h-72 w-72 rounded-full bg-gradient-to-br from-black/20 via-black/5 to-transparent blur-3xl"
                />

                <div
                    class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-primary-foreground/20 bg-primary-foreground/10 px-3 py-1 text-xs font-black text-primary-foreground"
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
                            class="text-2xl font-black tracking-tight text-primary-foreground sm:text-3xl"
                        >
                            Selamat Datang, {{ storeName }} 👋
                        </h1>
                        <p class="max-w-2xl text-xs text-primary-foreground/80 sm:text-sm">
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
                            class="flex items-center justify-between gap-3 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-3 px-4 backdrop-blur-md sm:justify-start"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-foreground/20 font-bold text-primary-foreground"
                                >
                                    <Wallet class="h-5 w-5" />
                                </div>
                                <div>
                                    <span
                                        class="block text-[10px] font-bold tracking-wider text-primary-foreground/80 uppercase"
                                        >Saldo Siap Tarik</span
                                    >
                                    <span
                                        class="font-mono text-base font-black text-primary-foreground sm:text-lg"
                                        >Rp 95.400.000</span
                                    >
                                </div>
                            </div>
                        </div>

                        <Button
                            variant="secondary"
                            size="lg"
                            class="w-full cursor-pointer rounded-2xl px-5 text-xs font-extrabold sm:w-auto"
                            @click="navigate('/wallet')"
                        >
                            <Landmark class="mr-2 h-4 w-4" /> Tarik Saldo /
                            Dompet
                        </Button>
                        <Button
                            variant="secondary"
                            size="lg"
                            class="w-full cursor-pointer rounded-2xl px-5 text-xs font-extrabold sm:w-auto"
                            @click="navigate('/' + ((usePage().props.store as any)?.slug ?? ''))"
                        >
                            <Eye class="mr-2 h-4 w-4" /> Lihat Marketplace
                        </Button>
                    </div>
                </div>

                <!-- Quick Action Shortcuts Bar -->
                <div
                    class="relative z-10 mt-6 grid grid-cols-2 gap-3 border-t border-primary-foreground/10 pt-6 sm:grid-cols-4"
                >
                    <button
                        @click="navigate('/products/create')"
                        class="group flex cursor-pointer items-center gap-2.5 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-3 text-left transition-all hover:bg-primary-foreground/10"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-foreground/20 font-bold text-primary-foreground"
                        >
                            <Plus class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-primary-foreground transition-colors group-hover:opacity-80"
                                >Tambah Produk</span
                            >
                            <span class="text-[10px] text-primary-foreground/80"
                                >Buat katalog baru</span
                            >
                        </div>
                    </button>

                    <button
                        @click="navigate('/orders')"
                        class="group flex cursor-pointer items-center gap-2.5 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-3 text-left transition-all hover:bg-primary-foreground/10"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-foreground/20 font-bold text-primary-foreground"
                        >
                            <ShoppingCart class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-primary-foreground transition-colors group-hover:opacity-80"
                                >Kelola Pesanan</span
                            >
                            <span class="text-[10px] text-primary-foreground/80"
                                >3 Order perlu dikirim</span
                            >
                        </div>
                    </button>

                    <button
                        @click="navigate('/store-settings')"
                        class="group flex cursor-pointer items-center gap-2.5 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-3 text-left transition-all hover:bg-primary-foreground/10"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-foreground/20 font-bold text-primary-foreground"
                        >
                            <Settings class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-primary-foreground transition-colors group-hover:opacity-80"
                                >Pengaturan Toko</span
                            >
                            <span class="text-[10px] text-primary-foreground/80"
                                >Banner & Legalitas PKP</span
                            >
                        </div>
                    </button>

                    <button
                        @click="navigate('/catalog')"
                        class="group flex cursor-pointer items-center gap-2.5 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-3 text-left transition-all hover:bg-primary-foreground/10"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary-foreground/20 font-bold text-primary-foreground"
                        >
                            <Box class="h-4 w-4" />
                        </div>
                        <div>
                            <span
                                class="block text-xs font-bold text-primary-foreground transition-colors group-hover:opacity-80"
                                >Taksonomi Katalog</span
                            >
                            <span class="text-[10px] text-primary-foreground/80"
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
            <Card class="rounded-3xl border-border bg-card p-6 shadow-xs">
                <CardHeader
                    class="mb-4 flex flex-row items-center justify-between p-0"
                >
                    <div>
                        <CardTitle class="text-lg font-black text-foreground"
                            >Grafik Omzet & Growth Revenue</CardTitle
                        >
                        <CardDescription class="mt-0.5 text-xs text-muted-foreground">
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
                            class="flex items-center gap-2 text-xs font-bold text-muted-foreground"
                        >
                            <span
                                class="inline-block h-2.5 w-2.5 rounded-full bg-primary"
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
                    class="flex flex-col justify-between rounded-3xl border-border bg-card p-6 shadow-xs xl:col-span-4"
                >
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <CardTitle
                                class="text-base font-black text-foreground"
                                >Alur Funnel Pesanan</CardTitle
                            >
                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-auto p-0 text-xs font-bold text-primary"
                                @click="navigate('/orders')"
                            >
                                Lihat Semua
                                <ChevronRight class="ml-0.5 h-3.5 w-3.5" />
                            </Button>
                        </div>
                        <CardDescription class="mb-5 text-xs text-muted-foreground"
                            >Status realtime alur pesanan
                            pembeli</CardDescription
                        >
                        <OrderFunnel :orderFlow="displayOrderFlow" />
                    </div>

                    <div
                        class="mt-6 flex items-center justify-between rounded-2xl border border-primary/20 bg-primary/10 p-4"
                    >
                        <span class="text-xs font-extrabold text-primary"
                            >Total Order Terverifikasi</span
                        >
                        <span
                            class="font-mono text-base font-black text-foreground"
                        >
                            {{ totalOrdersThisMonth.toLocaleString('id') }}
                            Transaksi
                        </span>
                    </div>
                </Card>

                <!-- Top Sellers Products -->
                <Card
                    class="rounded-3xl border-border bg-card p-6 shadow-xs xl:col-span-5"
                >
                    <div class="mb-1 flex items-center justify-between">
                        <CardTitle class="text-base font-black text-foreground"
                            >Produk Terlaris (Top Selling Items)</CardTitle
                        >
                        <Button
                            variant="ghost"
                            size="sm"
                            class="h-auto p-0 text-xs font-bold text-primary"
                            @click="navigate('/products')"
                        >
                            Katalog <ChevronRight class="ml-0.5 h-3.5 w-3.5" />
                        </Button>
                    </div>
                    <CardDescription class="mb-4 text-xs text-muted-foreground"
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
