<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/sonner';
import AppLayout from '@/layouts/AppLayout.vue';

interface OrderItem {
    id: number;
    product_name: string;
    sku?: string | null;
    quantity: number;
    price: number;
    subtotal: number;
}

interface SellerOrder {
    id: number;
    order_number: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    shipping_address: string;
    store_name: string;
    total_amount: string;
    total_num: number;
    status: string;
    created_at: string;
    items?: OrderItem[];
    notes?: string;
    tracking_number?: string;
}

interface Props {
    orders?: SellerOrder[];
}

const props = withDefaults(defineProps<Props>(), {
    orders: () => [],
});

const searchQuery = ref('');
const statusFilter = ref<string>('all');
const currentPage = ref(1);
const perPage = ref(5);
const expandedOrders = ref<Record<number, boolean>>({});

// Dynamic KPI metrics computed directly from props.orders (No Hardcoding)
const totalOrdersCount = computed(() => (props.orders ?? []).length);

const pendingCount = computed(
    () =>
        (props.orders ?? []).filter((o) => o.status.toLowerCase() === 'pending')
            .length,
);

const processingCount = computed(
    () =>
        (props.orders ?? []).filter(
            (o) => o.status.toLowerCase() === 'processing',
        ).length,
);

const shippedCount = computed(
    () =>
        (props.orders ?? []).filter((o) => o.status.toLowerCase() === 'shipped')
            .length,
);

const completedCount = computed(
    () =>
        (props.orders ?? []).filter(
            (o) => o.status.toLowerCase() === 'completed',
        ).length,
);

const totalRevenueSum = computed(() => {
    return (props.orders ?? [])
        .filter((o) => o.status.toLowerCase() !== 'cancelled')
        .reduce((sum, o) => sum + (o.total_num || 0), 0);
});

function formatRupiah(val: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
}

// Filtered Orders Computation
const filteredOrders = computed(() => {
    let list = props.orders ?? [];

    if (statusFilter.value !== 'all') {
        list = list.filter(
            (o) => o.status.toLowerCase() === statusFilter.value,
        );
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(
            (o) =>
                o.order_number.toLowerCase().includes(q) ||
                o.customer_name.toLowerCase().includes(q) ||
                o.customer_email.toLowerCase().includes(q) ||
                o.customer_phone.toLowerCase().includes(q),
        );
    }

    return list;
});

const totalPages = computed(
    () => Math.ceil(filteredOrders.value.length / perPage.value) || 1,
);

const paginatedOrders = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;

    return filteredOrders.value.slice(start, start + perPage.value);
});

const paginationStart = computed(() => {
    if (filteredOrders.value.length === 0) {
        return 0;
    }

    return (currentPage.value - 1) * perPage.value + 1;
});

const paginationEnd = computed(() => {
    return Math.min(
        currentPage.value * perPage.value,
        filteredOrders.value.length,
    );
});

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
}

function goToPage(p: number) {
    currentPage.value = p;
}

function toggleExpand(id: number) {
    expandedOrders.value[id] = !expandedOrders.value[id];
}

watch([searchQuery, statusFilter, perPage], () => {
    currentPage.value = 1;
});

function updateOrderStatus(orderId: number, newStatus: string) {
    router.patch(
        `/orders/${orderId}/status`,
        { status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    `Status pesanan #${orderId} berhasil diubah menjadi ${newStatus.toUpperCase()}!`,
                );
            },
            onError: () => {
                toast.error('Gagal memperbarui status pesanan.');
            },
        },
    );
}

const statusBadgeMap: Record<
    string,
    {
        label: string;
        variant: 'amber' | 'teal' | 'rose' | 'violetSolid' | 'outline';
    }
> = {
    pending: { label: 'Menunggu Bayar', variant: 'amber' },
    paid: { label: 'Sudah Dibayar', variant: 'teal' },
    processing: { label: 'Diproses Seller', variant: 'violetSolid' },
    shipped: { label: 'Dikirim', variant: 'teal' },
    completed: { label: 'Selesai', variant: 'teal' },
    cancelled: { label: 'Dibatalkan', variant: 'rose' },
};
</script>

<template>
    <Head title="Manajemen Pesanan Masuk — Dashboard Merchant" />

    <AppLayout title="Pesanan" activePage="Pesanan">
        <div
            class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- ── Top Command Banner Hub ── -->
            <div
                class="relative overflow-hidden rounded-3xl bg-zinc-900 p-6 text-white shadow-xl sm:p-8"
            >
                <div
                    class="absolute -top-10 -right-10 h-64 w-64 rounded-full bg-gradient-to-br from-amber-500/30 via-orange-500/20 to-transparent blur-3xl"
                />
                <div
                    class="absolute -bottom-10 -left-10 h-64 w-64 rounded-full bg-gradient-to-br from-emerald-500/30 via-teal-500/10 to-transparent blur-3xl"
                />

                <div
                    class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-xs font-black text-amber-400"
                            >
                                <Sparkles class="h-3.5 w-3.5" /> REALTIME ORDER
                                FLOW
                            </span>
                        </div>
                        <h1
                            class="text-2xl font-black tracking-tight text-white sm:text-3xl"
                        >
                            Manajemen Pesanan Masuk
                        </h1>
                        <p class="max-w-2xl text-xs text-zinc-400 sm:text-sm">
                            Kelola pesanan pembeli, verifikasi pembayaran,
                            perbarui status pengiriman barang, dan pantau total
                            omzet masuk secara transparan.
                        </p>
                    </div>

                    <!-- Dynamic Revenue Summary Counter -->
                    <div
                        class="flex w-full shrink-0 items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3.5 backdrop-blur-md sm:w-auto sm:p-4"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 font-bold text-amber-400"
                        >
                            <DollarSign class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <span
                                class="block truncate text-[10px] font-bold tracking-wider text-zinc-400 uppercase"
                                >Total Omzet Pesanan Valid</span
                            >
                            <span
                                class="block truncate font-mono text-lg font-black text-amber-400 sm:text-xl"
                                :title="formatRupiah(totalRevenueSum)"
                                >{{ formatRupiah(totalRevenueSum) }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Dynamic Status Metrics Cards Bar (Mobile Responsive) -->
                <div
                    class="relative z-10 mt-6 grid grid-cols-2 gap-2.5 border-t border-white/10 pt-6 sm:grid-cols-3 sm:gap-3 md:grid-cols-5"
                >
                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Total Masuk</span
                        >
                        <span
                            class="truncate font-mono text-sm font-black text-white sm:text-xl"
                            >{{ totalOrdersCount }} Orders</span
                        >
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Menunggu Bayar</span
                        >
                        <span
                            class="truncate font-mono text-sm font-black text-amber-400 sm:text-xl"
                            >{{ pendingCount }} Order</span
                        >
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Diproses Seller</span
                        >
                        <span
                            class="truncate font-mono text-sm font-black text-indigo-400 sm:text-xl"
                            >{{ processingCount }} Order</span
                        >
                    </div>

                    <div
                        class="flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Dalam Pengiriman</span
                        >
                        <span
                            class="truncate font-mono text-sm font-black text-blue-400 sm:text-xl"
                            >{{ shippedCount }} Order</span
                        >
                    </div>

                    <div
                        class="col-span-2 flex min-w-0 flex-col gap-1 rounded-2xl border border-white/10 bg-white/5 p-2.5 backdrop-blur-md sm:col-span-1 sm:p-3"
                    >
                        <span
                            class="truncate text-[9px] font-extrabold tracking-wider text-zinc-400 uppercase sm:text-[10px]"
                            >Selesai / Paid</span
                        >
                        <span
                            class="font-mono text-xl font-black text-emerald-400"
                            >{{ completedCount }} Order</span
                        >
                    </div>
                </div>
            </div>

            <!-- ── Search & Dynamic Status Tabs Navigation Bar ── -->
            <div
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
            >
                <!-- Search Input -->
                <div class="relative w-full lg:w-80">
                    <Search
                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-zinc-400"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari No. Order / Nama / Email / HP..."
                        class="h-10 rounded-2xl border-black/10 bg-white pl-10 text-xs"
                    />
                </div>

                <!-- Dynamic Status Filter Tabs (Counts computed directly from props.orders) -->
                <div
                    class="flex w-full items-center gap-1 overflow-x-auto rounded-2xl border border-black/8 bg-[#faf9f6] p-1 lg:w-auto"
                >
                    <button
                        @click="statusFilter = 'all'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            statusFilter === 'all'
                                ? 'bg-black text-amber-400 shadow-xs'
                                : 'text-zinc-600 hover:text-black'
                        "
                    >
                        Semua ({{ totalOrdersCount }})
                    </button>
                    <button
                        @click="statusFilter = 'pending'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            statusFilter === 'pending'
                                ? 'bg-amber-400 font-black text-black shadow-xs'
                                : 'text-zinc-600 hover:text-black'
                        "
                    >
                        Pending ({{ pendingCount }})
                    </button>
                    <button
                        @click="statusFilter = 'processing'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            statusFilter === 'processing'
                                ? 'bg-indigo-600 text-white shadow-xs'
                                : 'text-zinc-600 hover:text-black'
                        "
                    >
                        Diproses ({{ processingCount }})
                    </button>
                    <button
                        @click="statusFilter = 'shipped'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            statusFilter === 'shipped'
                                ? 'bg-blue-600 text-white shadow-xs'
                                : 'text-zinc-600 hover:text-black'
                        "
                    >
                        Dikirim ({{ shippedCount }})
                    </button>
                    <button
                        @click="statusFilter = 'completed'"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            statusFilter === 'completed'
                                ? 'bg-emerald-600 text-white shadow-xs'
                                : 'text-zinc-600 hover:text-black'
                        "
                    >
                        Selesai ({{ completedCount }})
                    </button>
                </div>
            </div>

            <!-- ── Order Cards List ── -->
            <div class="flex flex-col gap-4">
                <Card
                    v-for="order in paginatedOrders"
                    :key="order.id"
                    class="group relative overflow-hidden rounded-3xl border-black/8 bg-white shadow-xs transition-all duration-300 hover:border-amber-500/30 hover:shadow-xl"
                >
                    <!-- Header Card -->
                    <CardHeader
                        class="flex flex-col items-start justify-between gap-3 border-b border-black/5 bg-[#faf9f6] p-5 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-3.5">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-zinc-900 font-black text-amber-400 shadow-xs"
                            >
                                <ShoppingCart class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3
                                        class="font-mono text-base font-black text-[#1c1c22]"
                                    >
                                        {{ order.order_number }}
                                    </h3>
                                    <Badge
                                        :variant="
                                            statusBadgeMap[
                                                order.status.toLowerCase()
                                            ]?.variant || 'amber'
                                        "
                                        class="px-2.5 py-0.5 text-[10px] font-black uppercase"
                                    >
                                        {{
                                            statusBadgeMap[
                                                order.status.toLowerCase()
                                            ]?.label || order.status
                                        }}
                                    </Badge>
                                </div>
                                <p
                                    class="mt-0.5 flex items-center gap-1.5 text-xs font-medium text-zinc-400"
                                >
                                    <Clock class="h-3.5 w-3.5 text-zinc-400" />
                                    <span
                                        >Dipesan pada
                                        {{ order.created_at }}</span
                                    >
                                    <span>•</span>
                                    <span class="font-bold text-zinc-600">{{
                                        order.store_name
                                    }}</span>
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex w-full items-center justify-between gap-4 sm:w-auto sm:justify-end"
                        >
                            <div class="text-left sm:text-right">
                                <span
                                    class="block text-[10px] font-extrabold text-zinc-400 uppercase"
                                    >Total Tagihan Order</span
                                >
                                <p
                                    class="font-mono text-lg font-black text-amber-600"
                                >
                                    {{ order.total_amount }}
                                </p>
                            </div>

                            <Button
                                variant="ghost"
                                size="sm"
                                class="h-9 rounded-xl px-3 text-xs font-bold text-zinc-500 hover:bg-black/5 hover:text-black"
                                @click="toggleExpand(order.id)"
                            >
                                <span>Rincian</span>
                                <ChevronDown
                                    v-if="!expandedOrders[order.id]"
                                    class="ml-1 h-4 w-4"
                                />
                                <ChevronUp v-else class="ml-1 h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>

                    <!-- Body Card: Buyer Information -->
                    <CardContent
                        class="flex flex-col items-start justify-between gap-6 p-5 md:flex-row"
                    >
                        <!-- Buyer Details Grid -->
                        <div
                            class="grid flex-1 grid-cols-1 gap-4 text-xs sm:grid-cols-3"
                        >
                            <div
                                class="flex items-start gap-2.5 rounded-2xl border border-black/5 bg-zinc-50 p-3"
                            >
                                <User
                                    class="mt-0.5 h-4 w-4 shrink-0 text-amber-500"
                                />
                                <div class="flex min-w-0 flex-col">
                                    <span
                                        class="text-[10px] font-bold text-zinc-400 uppercase"
                                        >Nama Pembeli</span
                                    >
                                    <span
                                        class="truncate font-extrabold text-[#1c1c22]"
                                        >{{ order.customer_name }}</span
                                    >
                                    <span
                                        class="truncate font-mono text-[10px] text-zinc-400"
                                        >{{ order.customer_email }}</span
                                    >
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-2.5 rounded-2xl border border-black/5 bg-zinc-50 p-3"
                            >
                                <Phone
                                    class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500"
                                />
                                <div class="flex min-w-0 flex-col">
                                    <span
                                        class="text-[10px] font-bold text-zinc-400 uppercase"
                                        >WhatsApp / No. HP</span
                                    >
                                    <span
                                        class="font-mono font-black text-zinc-700"
                                        >{{ order.customer_phone }}</span
                                    >
                                    <span
                                        class="text-[10px] font-bold text-emerald-600"
                                        >Terverifikasi</span
                                    >
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-2.5 rounded-2xl border border-black/5 bg-zinc-50 p-3"
                            >
                                <MapPin
                                    class="mt-0.5 h-4 w-4 shrink-0 text-rose-500"
                                />
                                <div class="flex min-w-0 flex-col">
                                    <span
                                        class="text-[10px] font-bold text-zinc-400 uppercase"
                                        >Alamat Tujuan Pengiriman</span
                                    >
                                    <span
                                        class="line-clamp-2 leading-tight font-semibold text-zinc-700"
                                        >{{ order.shipping_address }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Action Buttons (State Controlled) -->
                        <div
                            class="flex w-full shrink-0 flex-wrap items-center gap-2 border-t border-black/5 pt-3 md:w-auto md:border-t-0 md:pt-0"
                        >
                            <Button
                                v-if="
                                    order.status.toLowerCase() === 'pending' ||
                                    order.status.toLowerCase() === 'paid'
                                "
                                size="sm"
                                class="h-10 cursor-pointer gap-2 rounded-xl bg-zinc-900 px-5 text-xs font-black text-amber-400 shadow-md hover:bg-black"
                                @click="
                                    updateOrderStatus(order.id, 'processing')
                                "
                            >
                                <PackageCheck class="h-4 w-4 text-amber-400" />
                                Proses Pesanan
                            </Button>

                            <Button
                                v-if="
                                    order.status.toLowerCase() === 'processing'
                                "
                                size="sm"
                                class="h-10 cursor-pointer gap-2 rounded-xl bg-blue-600 px-5 text-xs font-black text-white shadow-md hover:bg-blue-700"
                                @click="updateOrderStatus(order.id, 'shipped')"
                            >
                                <Truck class="h-4 w-4" />
                                Kirim Pesanan
                            </Button>

                            <Button
                                v-if="order.status.toLowerCase() === 'shipped'"
                                size="sm"
                                class="h-10 cursor-pointer gap-2 rounded-xl bg-emerald-600 px-5 text-xs font-black text-white shadow-md hover:bg-emerald-700"
                                @click="
                                    updateOrderStatus(order.id, 'completed')
                                "
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                Pesanan Selesai
                            </Button>

                            <Button
                                v-if="
                                    order.status.toLowerCase() !==
                                        'completed' &&
                                    order.status.toLowerCase() !== 'cancelled'
                                "
                                variant="outline"
                                size="sm"
                                class="h-10 cursor-pointer rounded-xl border-rose-200 px-4 text-xs font-bold text-rose-600 hover:bg-rose-50"
                                @click="
                                    updateOrderStatus(order.id, 'cancelled')
                                "
                            >
                                <XCircle class="mr-1 h-4 w-4" />
                                Batalkan Order
                            </Button>
                        </div>
                    </CardContent>

                    <!-- Expandable Item Breakdown Drawer -->
                    <div
                        v-if="expandedOrders[order.id]"
                        class="flex flex-col gap-3 border-t border-black/5 bg-amber-50/20 p-5"
                    >
                        <span
                            class="flex items-center gap-1.5 text-xs font-black text-[#1c1c22]"
                        >
                            <FileText class="h-4 w-4 text-amber-500" /> Rincian
                            Barang Dipesan & Catatan Pembeli
                        </span>

                        <div
                            v-if="order.items && order.items.length > 0"
                            class="divide-y divide-black/5 overflow-hidden rounded-2xl border bg-white text-xs"
                        >
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-center justify-between gap-3 p-3"
                            >
                                <div class="flex min-w-0 flex-col">
                                    <span
                                        class="truncate font-bold text-[#1c1c22]"
                                        >{{ item.product_name }}</span
                                    >
                                    <span
                                        v-if="item.sku"
                                        class="font-mono text-[10px] text-zinc-400"
                                        >SKU {{ item.sku }}</span
                                    >
                                </div>
                                <div
                                    class="flex shrink-0 items-center gap-4 font-mono"
                                >
                                    <span class="text-zinc-500"
                                        >{{ item.quantity }} pcs x
                                        {{ formatRupiah(item.price) }}</span
                                    >
                                    <span class="font-black text-amber-600">{{
                                        formatRupiah(item.subtotal)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <p
                            v-else
                            class="rounded-xl border border-black/5 bg-white p-3 text-xs text-zinc-500 italic"
                        >
                            Belum ada line item tersimpan untuk pesanan ini
                            (order lama sebelum snapshot). Total tagihan:
                            <b>{{ order.total_amount }}</b
                            >.
                        </p>
                    </div>
                </Card>

                <!-- Empty State -->
                <div
                    v-if="filteredOrders.length === 0"
                    class="flex flex-col items-center justify-center rounded-3xl border border-black/8 bg-white p-6 py-16 text-center shadow-xs"
                >
                    <div
                        class="mb-3 flex h-16 w-16 items-center justify-center rounded-3xl bg-amber-50 text-amber-500"
                    >
                        <ShoppingCart class="h-8 w-8" />
                    </div>
                    <h3 class="text-base font-black text-[#1c1c22]">
                        Tidak Ada Pesanan Ditemukan
                    </h3>
                    <p class="mt-1 max-w-sm text-xs text-zinc-400">
                        Belum ada transaksi pesanan yang sesuai dengan kriteria
                        filter atau kata kunci pencarian kamu.
                    </p>
                </div>
            </div>

            <!-- ── Pagination Bar ── -->
            <div
                v-if="filteredOrders.length > 0"
                class="flex flex-col items-center justify-between gap-4 rounded-3xl border border-black/8 bg-white p-4 shadow-xs sm:flex-row"
            >
                <div
                    class="flex items-center gap-3 text-xs font-semibold text-zinc-500"
                >
                    <span
                        >Menampilkan
                        <strong class="font-black text-[#1c1c22]"
                            >{{ paginationStart }}–{{ paginationEnd }}</strong
                        >
                        dari
                        <strong class="font-black text-[#1c1c22]">{{
                            filteredOrders.length
                        }}</strong>
                        pesanan</span
                    >
                    <span class="text-zinc-300">|</span>
                    <div class="flex items-center gap-1.5">
                        <span>Per Halaman:</span>
                        <select
                            v-model="perPage"
                            class="h-8 cursor-pointer rounded-xl border border-black/10 bg-zinc-50 px-2 text-xs font-bold"
                        >
                            <option :value="5">5</option>
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 cursor-pointer rounded-xl px-3 text-xs font-bold"
                        :disabled="currentPage === 1"
                        @click="prevPage"
                    >
                        <ChevronLeft class="mr-1 h-4 w-4" /> Prev
                    </Button>

                    <div class="flex items-center gap-1 px-1">
                        <button
                            v-for="p in totalPages"
                            :key="p"
                            @click="goToPage(p)"
                            class="h-9 w-9 cursor-pointer rounded-xl text-xs font-black transition-all"
                            :class="
                                currentPage === p
                                    ? 'bg-black text-amber-400 shadow-xs'
                                    : 'border border-black/8 bg-white text-zinc-600 hover:bg-zinc-100'
                            "
                        >
                            {{ p }}
                        </button>
                    </div>

                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 cursor-pointer rounded-xl px-3 text-xs font-bold"
                        :disabled="currentPage === totalPages"
                        @click="nextPage"
                    >
                        Next <ChevronRight class="ml-1 h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
