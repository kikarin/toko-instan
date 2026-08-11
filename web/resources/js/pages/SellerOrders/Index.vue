<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    ShoppingCart,
    Search,
    CheckCircle2,
    Truck,
    PackageCheck,
    Clock,
    XCircle,
    User,
    Phone,
    MapPin,
    ChevronLeft,
    ChevronRight,
    DollarSign,
    Sparkles,
    ChevronDown,
    ChevronUp,
    FileText,
    Printer,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { useSellerOrders } from '@/composables/useSellerOrders';
import type { SellerOrder } from '@/types/order';

interface Props {
    orders?: SellerOrder[];
}

const props = withDefaults(defineProps<Props>(), {
    orders: () => [],
});

const ordersRef = computed(() => props.orders ?? []);

const {
    searchQuery,
    statusFilter,
    currentPage,
    perPage,
    expandedOrders,
    totalOrdersCount,
    pendingCount,
    processingCount,
    shippedCount,
    completedCount,
    totalRevenueSum,
    formatRupiah,
    filteredOrders,
    totalPages,
    paginatedOrders,
    paginationStart,
    paginationEnd,
    prevPage,
    nextPage,
    goToPage,
    toggleExpand,
    updateOrderStatus,
    statusBadgeMap,
} = useSellerOrders(ordersRef);

function openInvoice(orderNumber: string) {
    router.get(`/orders/${orderNumber}/invoice`);
}
</script>

<template>

    <Head title="Manajemen Pesanan Masuk — Dashboard Merchant" />

    <AppLayout title="Pesanan" activePage="Pesanan">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
            <!-- ── Top Command Banner Hub ── -->
            <div class="relative overflow-hidden rounded-3xl bg-primary p-6 text-primary-foreground shadow-xl sm:p-8">
                <div
                    class="absolute -top-10 -right-10 h-64 w-64 rounded-full bg-gradient-to-br from-white/30 via-white/10 to-transparent blur-3xl" />
                <div
                    class="absolute -bottom-10 -left-10 h-64 w-64 rounded-full bg-gradient-to-br from-black/20 via-black/5 to-transparent blur-3xl" />

                <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-primary-foreground/20 bg-primary-foreground/10 px-3 py-1 text-xs font-black text-primary-foreground">
                                <Sparkles class="h-3.5 w-3.5" /> REALTIME ORDER
                                FLOW
                            </span>
                        </div>
                        <h1 class="text-2xl font-black tracking-tight text-primary-foreground sm:text-3xl">
                            Manajemen Pesanan Masuk
                        </h1>
                        <p class="max-w-2xl text-xs text-primary-foreground/80 sm:text-sm">
                            Kelola pesanan pembeli, verifikasi pembayaran,
                            perbarui status pengiriman barang, dan pantau total
                            omzet masuk secara transparan.
                        </p>
                    </div>

                    <!-- Dynamic Revenue Summary Counter -->
                    <div
                        class="flex w-full shrink-0 items-center gap-3 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-3.5 backdrop-blur-md sm:w-auto sm:p-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-foreground/20 font-bold text-primary-foreground">
                            <DollarSign class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <span
                                class="block truncate text-[10px] font-bold tracking-wider text-primary-foreground/80 uppercase">Total
                                Omzet Pesanan Valid</span>
                            <span class="block truncate font-mono text-lg font-black text-primary-foreground sm:text-xl"
                                :title="formatRupiah(totalRevenueSum)">{{ formatRupiah(totalRevenueSum) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Status Metrics Cards Bar (Mobile Responsive) -->
                <div
                    class="relative z-10 mt-6 grid grid-cols-2 gap-2.5 border-t border-primary-foreground/10 pt-6 sm:grid-cols-3 sm:gap-3 md:grid-cols-5">
                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-2.5 backdrop-blur-md sm:p-3">
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-primary-foreground/80 uppercase sm:text-[10px]">Total
                            Masuk</span>
                        <span class="truncate font-mono text-sm font-black text-primary-foreground sm:text-xl">{{ totalOrdersCount }}
                            Orders</span>
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-2.5 backdrop-blur-md sm:p-3">
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-primary-foreground/80 uppercase sm:text-[10px]">Menunggu
                            Bayar</span>
                        <span class="truncate font-mono text-sm font-black text-primary-foreground sm:text-xl">{{ pendingCount }}
                            Order</span>
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-2.5 backdrop-blur-md sm:p-3">
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-primary-foreground/80 uppercase sm:text-[10px]">Diproses
                            Seller</span>
                        <span class="truncate font-mono text-sm font-black text-primary-foreground sm:text-xl">{{
                            processingCount }} Order</span>
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-2.5 backdrop-blur-md sm:p-3">
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-primary-foreground/80 uppercase sm:text-[10px]">Dalam
                            Pengiriman</span>
                        <span class="truncate font-mono text-sm font-black text-primary-foreground sm:text-xl">{{ shippedCount }}
                            Order</span>
                    </div>

                    <div
                        class="col-span-2 flex min-w-0 flex-col gap-1 rounded-2xl border border-primary-foreground/10 bg-primary-foreground/5 p-2.5 backdrop-blur-md sm:col-span-1 sm:p-3">
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-primary-foreground/80 uppercase sm:text-[10px]">Selesai
                            / Paid</span>
                        <span class="font-mono text-xl font-black text-primary-foreground">{{ completedCount }} Order</span>
                    </div>
                </div>
            </div>

            <!-- ── Search & Dynamic Status Tabs Navigation Bar ── -->
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
                <!-- Search Input -->
                <div class="relative w-full lg:w-80">
                    <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Cari No. Order / Nama / Email / HP..."
                        class="h-10 rounded-2xl border-border bg-background pl-10 text-xs" />
                </div>

                <!-- Dynamic Status Filter Tabs (Counts computed directly from props.orders) -->
                <div
                    class="flex w-full items-center gap-1 overflow-x-auto rounded-2xl border border-border bg-card p-1 lg:w-auto">
                    <button @click="statusFilter = 'all'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="statusFilter === 'all'
                                ? 'bg-primary text-primary-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                            ">
                        Semua ({{ totalOrdersCount }})
                    </button>
                    <button @click="statusFilter = 'pending'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="statusFilter === 'pending'
                                ? 'bg-primary text-primary-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                            ">
                        Pending ({{ pendingCount }})
                    </button>
                    <button @click="statusFilter = 'processing'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="statusFilter === 'processing'
                                ? 'bg-primary text-primary-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                            ">
                        Diproses ({{ processingCount }})
                    </button>
                    <button @click="statusFilter = 'shipped'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="statusFilter === 'shipped'
                                ? 'bg-primary text-primary-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                            ">
                        Dikirim ({{ shippedCount }})
                    </button>
                    <button @click="statusFilter = 'completed'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="statusFilter === 'completed'
                                ? 'bg-primary text-primary-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                            ">
                        Selesai ({{ completedCount }})
                    </button>
                </div>
            </div>

            <!-- ── Order Cards List ── -->
            <div class="flex flex-col gap-4">
                <Card v-for="order in paginatedOrders" :key="order.id"
                    class="group relative overflow-hidden rounded-3xl border-border bg-card shadow-xs transition-all duration-300 hover:border-primary/50 hover:shadow-xl">
                    <!-- Header Card -->
                    <CardHeader
                        class="flex flex-col items-start justify-between gap-3 border-b border-border bg-muted/30 p-5 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3.5">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary/10 font-black text-primary shadow-xs">
                                <ShoppingCart class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-mono text-base font-black text-foreground">
                                        {{ order.order_number }}
                                    </h3>
                                    <Badge :variant="statusBadgeMap[
                                            order.status.toLowerCase()
                                        ]?.variant || 'amber'
                                        " class="px-2.5 py-0.5 text-[10px] font-black uppercase">
                                        {{
                                            statusBadgeMap[
                                                order.status.toLowerCase()
                                            ]?.label || order.status
                                        }}
                                    </Badge>
                                </div>
                                <p class="mt-0.5 flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                                    <Clock class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span>Dipesan pada
                                        {{ order.created_at }}</span>
                                    <span>•</span>
                                    <span class="font-bold text-muted-foreground">{{
                                        order.store_name
                                        }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex w-full items-center justify-between gap-4 sm:w-auto sm:justify-end">
                            <div class="text-left sm:text-right">
                                <span class="block text-[10px] font-extrabold text-muted-foreground uppercase">Total Tagihan
                                    Order</span>
                                <p class="font-mono text-lg font-black text-primary">
                                    {{ order.total_amount }}
                                </p>
                            </div>

                            <Button variant="ghost" size="sm"
                                class="h-9 rounded-xl px-3 text-xs font-bold text-muted-foreground hover:bg-secondary hover:text-foreground"
                                @click="toggleExpand(order.id)">
                                <span>Rincian</span>
                                <ChevronDown v-if="!expandedOrders[order.id]" class="ml-1 h-4 w-4" />
                                <ChevronUp v-else class="ml-1 h-4 w-4" />
                            </Button>
                            <Button variant="outline" size="sm"
                                class="h-9 rounded-xl px-3 text-xs font-bold text-muted-foreground hover:bg-secondary hover:text-foreground"
                                @click="openInvoice(order.order_number)">
                                <Printer class="mr-1 h-3.5 w-3.5" /> Cetak Invoice
                            </Button>
                        </div>
                    </CardHeader>

                    <!-- Body Card: Buyer Information -->
                    <CardContent class="flex flex-col items-start justify-between gap-6 p-5 md:flex-row">
                        <!-- Buyer Details Grid -->
                        <div class="grid flex-1 grid-cols-1 gap-4 text-xs sm:grid-cols-3">
                            <div class="flex items-start gap-2.5 rounded-2xl border border-border bg-muted/30 p-3">
                                <User class="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                                <div class="flex min-w-0 flex-col">
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase">Nama Pembeli</span>
                                    <span class="truncate font-extrabold text-foreground">{{ order.customer_name
                                        }}</span>
                                    <span class="truncate font-mono text-[10px] text-muted-foreground">{{ order.customer_email
                                        }}</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 rounded-2xl border border-border bg-muted/30 p-3">
                                <Phone class="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                                <div class="flex min-w-0 flex-col">
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase">WhatsApp / No. HP</span>
                                    <span class="font-mono font-black text-foreground">{{ order.customer_phone }}</span>
                                    <span class="text-[10px] font-bold text-primary">Terverifikasi</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 rounded-2xl border border-border bg-muted/30 p-3">
                                <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                                <div class="flex min-w-0 flex-col">
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase">Alamat Tujuan
                                        Pengiriman</span>
                                    <span class="leading-tight font-semibold text-foreground">{{
                                        order.shipping_address }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Action Buttons (State Controlled) -->
                        <div
                            class="flex w-full shrink-0 flex-wrap items-center gap-2 border-t border-border pt-3 md:w-auto md:border-t-0 md:pt-0">
                            <Button v-if="
                                order.status.toLowerCase() === 'pending' ||
                                order.status.toLowerCase() === 'paid'
                            " size="sm"
                                class="h-10 cursor-pointer gap-2 rounded-xl bg-primary px-5 text-xs font-black text-primary-foreground shadow-md hover:bg-primary/90"
                                @click="
                                    updateOrderStatus(order.id, 'processing')
                                    ">
                                <PackageCheck class="h-4 w-4" />
                                Proses Pesanan
                            </Button>

                            <Button v-if="
                                order.status.toLowerCase() === 'processing'
                            " size="sm"
                                class="h-10 cursor-pointer gap-2 rounded-xl bg-secondary px-5 text-xs font-black text-secondary-foreground shadow-md hover:bg-secondary/80"
                                @click="updateOrderStatus(order.id, 'shipped')">
                                <Truck class="h-4 w-4" />
                                Kirim Pesanan
                            </Button>

                            <Button v-if="order.status.toLowerCase() === 'shipped'" size="sm"
                                class="h-10 cursor-pointer gap-2 rounded-xl bg-accent px-5 text-xs font-black text-accent-foreground shadow-md hover:bg-accent/80"
                                @click="
                                    updateOrderStatus(order.id, 'completed')
                                    ">
                                <CheckCircle2 class="h-4 w-4" />
                                Pesanan Selesai
                            </Button>

                            <Button v-if="
                                order.status.toLowerCase() !==
                                'completed' &&
                                order.status.toLowerCase() !== 'cancelled'
                            " variant="outline" size="sm"
                                class="h-10 cursor-pointer rounded-xl border-destructive/30 px-4 text-xs font-bold text-destructive hover:bg-destructive/10"
                                @click="
                                    updateOrderStatus(order.id, 'cancelled')
                                    ">
                                <XCircle class="mr-1 h-4 w-4" />
                                Batalkan Order
                            </Button>
                        </div>
                    </CardContent>

                    <!-- Expandable Item Breakdown Drawer -->
                    <div v-if="expandedOrders[order.id]"
                        class="flex flex-col gap-3 border-t border-border bg-muted/50 p-5">
                        <span class="flex items-center gap-1.5 text-xs font-black text-foreground">
                            <FileText class="h-4 w-4 text-primary" /> Rincian
                            Barang Dipesan & Catatan Pembeli
                        </span>

                        <div v-if="order.items && order.items.length > 0"
                            class="divide-y divide-border overflow-hidden rounded-2xl border border-border bg-card text-xs">
                            <div v-for="item in order.items" :key="item.id"
                                class="flex items-center justify-between gap-3 p-3">
                                <div class="flex min-w-0 flex-col">
                                    <span class="truncate font-bold text-foreground">{{ item.product_name }}</span>
                                    <span v-if="item.sku" class="font-mono text-[10px] text-muted-foreground">SKU {{ item.sku
                                        }}</span>
                                </div>
                                <div class="flex shrink-0 items-center gap-4 font-mono">
                                    <span class="text-muted-foreground">{{ item.quantity }} pcs x
                                        {{ formatRupiah(item.price) }}</span>
                                    <span class="font-black text-primary">{{
                                        formatRupiah(item.subtotal)
                                        }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="rounded-xl border border-border bg-card p-3 text-xs text-muted-foreground italic">
                            Belum ada line item tersimpan untuk pesanan ini
                            (order lama sebelum snapshot). Total tagihan:
                            <b>{{ order.total_amount }}</b>.
                        </p>
                    </div>
                </Card>

                <!-- Empty State -->
                <div v-if="filteredOrders.length === 0"
                    class="flex flex-col items-center justify-center rounded-3xl border border-border bg-card p-6 py-16 text-center shadow-xs">
                    <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-3xl bg-primary/10 text-primary">
                        <ShoppingCart class="h-8 w-8" />
                    </div>
                    <h3 class="text-base font-black text-foreground">
                        Tidak Ada Pesanan Ditemukan
                    </h3>
                    <p class="mt-1 max-w-sm text-xs text-muted-foreground">
                        Belum ada transaksi pesanan yang sesuai dengan kriteria
                        filter atau kata kunci pencarian kamu.
                    </p>
                </div>
            </div>

            <!-- ── Pagination Bar ── -->
            <div v-if="filteredOrders.length > 0"
                class="flex flex-col items-center justify-between gap-4 rounded-3xl border border-border bg-card p-4 shadow-xs sm:flex-row">
                <div class="flex items-center gap-3 text-xs font-semibold text-muted-foreground">
                    <span>Menampilkan
                        <strong class="font-black text-foreground">{{ paginationStart }}–{{ paginationEnd }}</strong>
                        dari
                        <strong class="font-black text-foreground">{{
                            filteredOrders.length
                            }}</strong>
                        pesanan</span>
                    <span class="text-muted-foreground/50">|</span>
                    <div class="flex items-center gap-1.5">
                        <span>Per Halaman:</span>
                        <select v-model="perPage"
                            class="h-8 cursor-pointer rounded-xl border border-border bg-muted/50 text-foreground px-2 text-xs font-bold">
                            <option :value="5">5</option>
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <Button variant="outline" size="sm" class="h-9 cursor-pointer rounded-xl px-3 text-xs font-bold"
                        :disabled="currentPage === 1" @click="prevPage">
                        <ChevronLeft class="mr-1 h-4 w-4" /> Prev
                    </Button>

                    <div class="flex items-center gap-1 px-1">
                        <button v-for="p in totalPages" :key="p" @click="goToPage(p)"
                            class="h-9 w-9 cursor-pointer rounded-xl text-xs font-black transition-all" :class="currentPage === p
                                    ? 'bg-primary text-primary-foreground shadow-xs'
                                    : 'border border-border bg-card text-muted-foreground hover:bg-secondary hover:text-foreground'
                                ">
                            {{ p }}
                        </button>
                    </div>

                    <Button variant="outline" size="sm" class="h-9 cursor-pointer rounded-xl px-3 text-xs font-bold"
                        :disabled="currentPage === totalPages" @click="nextPage">
                        Next
                        <ChevronRight class="ml-1 h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
