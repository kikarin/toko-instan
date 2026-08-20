<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Users, Search, ShoppingCart, DollarSign, Calendar, Phone, Mail, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

interface Customer {
    name: string;
    email: string;
    phone: string;
    total_orders: number;
    total_spent: number;
    total_spent_formatted: string;
    first_order_at: string;
    last_order_at: string;
}

interface Props {
    customers?: Customer[];
    storeName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    customers: () => [],
    storeName: 'Toko Anda',
});

const searchQuery = ref('');
const currentPage = ref(1);
const perPage = ref(10);

const filteredCustomers = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();

    if (!q) {
return props.customers;
}

    return props.customers.filter(
        (c) =>
            (c.name && c.name.toLowerCase().includes(q)) ||
            (c.email && c.email.toLowerCase().includes(q)) ||
            (c.phone && c.phone.toLowerCase().includes(q))
    );
});

const totalPages = computed(() => {
    return Math.ceil(filteredCustomers.value.length / perPage.value) || 1;
});

const paginatedCustomers = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;

    return filteredCustomers.value.slice(start, end);
});

const paginationStart = computed(() => {
    if (filteredCustomers.value.length === 0) {
return 0;
}

    return (currentPage.value - 1) * perPage.value + 1;
});

const paginationEnd = computed(() => {
    return Math.min(currentPage.value * perPage.value, filteredCustomers.value.length);
});

function goToPage(p: number) {
    if (p >= 1 && p <= totalPages.value) {
        currentPage.value = p;
    }
}

watch(searchQuery, () => {
    currentPage.value = 1;
});

watch(perPage, () => {
    currentPage.value = 1;
});

const totalCustomersCount = computed(() => props.customers.length);
const totalSpentSum = computed(() => {
    return props.customers.reduce((acc, c) => acc + c.total_spent, 0);
});
const totalOrdersSum = computed(() => {
    return props.customers.reduce((acc, c) => acc + c.total_orders, 0);
});

function formatRupiah(val: number): string {
    return 'Rp ' + val.toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Manajemen Pelanggan — Dashboard Merchant" />

    <AppLayout title="Pelanggan" activePage="Pelanggan">
        <main class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-2">
                <p class="text-xs font-extrabold tracking-widest text-[#e07c28] uppercase">
                    {{ storeName }} · CRM
                </p>
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h1 class="flex items-center gap-2.5 text-2xl font-black text-[#1c1c22] sm:text-3xl">
                            <Users class="h-7 w-7 text-[#e07c28]" /> Daftar Pelanggan
                        </h1>
                        <p class="text-xs text-[#9090a0] sm:text-sm">
                            Daftar pembeli yang pernah bertransaksi di toko Anda berdasarkan data pesanan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card class="flex items-center gap-4 p-5 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600">
                        <Users class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold tracking-wider text-[#9090a0] uppercase">Total Pelanggan</p>
                        <p class="text-2xl font-black text-[#1c1c22]">{{ totalCustomersCount }}</p>
                    </div>
                </Card>
                <Card class="flex items-center gap-4 p-5 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600">
                        <ShoppingCart class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold tracking-wider text-[#9090a0] uppercase">Total Transaksi</p>
                        <p class="text-2xl font-black text-[#1c1c22]">{{ totalOrdersSum }} Pesanan</p>
                    </div>
                </Card>
                <Card class="flex items-center gap-4 p-5 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600">
                        <DollarSign class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold tracking-wider text-[#9090a0] uppercase">Total Pendapatan</p>
                        <p class="text-2xl font-black text-[#1c1c22]">{{ formatRupiah(totalSpentSum) }}</p>
                    </div>
                </Card>
            </div>

            <!-- Search Filter Bar -->
            <Card class="p-4 shadow-sm">
                <div class="relative flex items-center">
                    <Search class="absolute left-3.5 h-4 w-4 text-[#9090a0]" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari berdasarkan nama, email, atau nomor telepon..."
                        class="w-full pl-10 text-sm"
                    />
                </div>
            </Card>

            <!-- Customers Table Card -->
            <Card class="overflow-hidden p-0 shadow-sm">
                <div v-if="filteredCustomers.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#f5f4f0] text-[#9090a0]">
                        <Users class="h-8 w-8" />
                    </div>
                    <p class="mt-4 text-base font-bold text-[#1c1c22]">Belum ada pelanggan ditemukan</p>
                    <p class="mt-1 text-xs text-[#9090a0]">Pelanggan akan otomatis muncul setelah ada pesanan masuk ke toko Anda.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-black/5 bg-[#fafafa] text-[11px] font-black tracking-wider text-[#9090a0] uppercase">
                                <th class="px-5 py-3.5">Pelanggan</th>
                                <th class="px-5 py-3.5">Kontak</th>
                                <th class="px-5 py-3.5 text-center">Total Pesanan</th>
                                <th class="px-5 py-3.5 text-right">Total Belanja</th>
                                <th class="px-5 py-3.5">Transaksi Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 text-sm">
                            <tr v-for="(customer, index) in paginatedCustomers" :key="index" class="transition-colors hover:bg-black/[0.01]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#1c1c22] text-xs font-black text-white">
                                            {{ customer.name ? customer.name.substring(0, 2).toUpperCase() : 'PL' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#1c1c22]">{{ customer.name || 'Tanpa Nama' }}</p>
                                            <p class="text-xs text-[#9090a0]">Pertama kali: {{ customer.first_order_at || '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-col gap-1 text-xs text-[#606070]">
                                        <div v-if="customer.email" class="flex items-center gap-1.5">
                                            <Mail class="h-3.5 w-3.5 text-[#9090a0]" /> {{ customer.email }}
                                        </div>
                                        <div v-if="customer.phone" class="flex items-center gap-1.5">
                                            <Phone class="h-3.5 w-3.5 text-[#9090a0]" /> {{ customer.phone }}
                                        </div>
                                        <span v-if="!customer.email && !customer.phone" class="text-zinc-400 italic">Tidak ada kontak</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <Badge variant="secondary" class="font-bold">
                                        {{ customer.total_orders }} Pesanan
                                    </Badge>
                                </td>
                                <td class="px-5 py-4 text-right font-black text-[#1c1c22]">
                                    {{ customer.total_spent_formatted }}
                                </td>
                                <td class="px-5 py-4 text-xs text-[#606070]">
                                    <div class="flex items-center gap-1.5">
                                        <Calendar class="h-3.5 w-3.5 text-[#9090a0]" />
                                        {{ customer.last_order_at || '-' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div
                    v-if="filteredCustomers.length > 0"
                    class="flex flex-col items-center justify-between gap-4 border-t border-black/5 bg-[#fafafa] p-4 sm:flex-row sm:px-6"
                >
                    <div
                        class="flex items-center gap-3 text-xs font-semibold text-zinc-500"
                    >
                        <span>
                            Menampilkan
                            <strong class="font-black text-[#1c1c22]"
                                >{{ paginationStart }} - {{ paginationEnd }}</strong
                            >
                            dari
                            <strong class="font-black text-[#1c1c22]">{{
                                filteredCustomers.length
                            }}</strong>
                            pelanggan
                        </span>
                        <div
                            class="flex items-center gap-1.5 rounded-xl border border-black/10 bg-white px-2.5 py-1"
                        >
                            <span>Per Halaman:</span>
                            <select
                                v-model.number="perPage"
                                class="cursor-pointer border-none bg-transparent text-xs font-bold text-[#1c1c22] focus:outline-none"
                            >
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <!-- Page Navigation Buttons -->
                    <div class="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-9 cursor-pointer rounded-xl px-3 text-xs font-bold"
                            :disabled="currentPage === 1"
                            @click="goToPage(currentPage - 1)"
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
                                        ? 'bg-amber-500 text-white shadow-xs'
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
                            @click="goToPage(currentPage + 1)"
                        >
                            Next <ChevronRight class="ml-1 h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </Card>
        </main>
    </AppLayout>
</template>
