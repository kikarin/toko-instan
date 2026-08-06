<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    User,
    MapPin,
    Truck,
    CreditCard,
    ShoppingBag,
    ShieldCheck,
    ArrowRight,
    Loader2,
    CheckCircle2,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/sonner';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useCart } from '@/lib/useCart';

// Cart is shared & persisted via localStorage composable
const { items: cartItems } = useCart();

const isEmpty = computed(() => cartItems.value.length === 0);

const customerName = ref('');
const customerEmail = ref('');
const customerPhone = ref('');
const shippingAddress = ref('');
const selectedCourier = ref('JNE Reguler (Rp 15.000)');
const selectedPayment = ref('qris');
const notes = ref('');
const isLoading = ref(false);

const couriers = [
    {
        id: 'jne',
        name: 'JNE Reguler',
        price: 15000,
        priceFmt: 'Rp 15.000',
        est: '2-3 Hari',
    },
    {
        id: 'jnt',
        name: 'J&T Express',
        price: 18000,
        priceFmt: 'Rp 18.000',
        est: '1-2 Hari',
    },
    {
        id: 'sicepat',
        name: 'SiCepat BEST',
        price: 22000,
        priceFmt: 'Rp 22.000',
        est: 'Besok Sampai',
    },
];

const paymentMethods = [
    {
        id: 'qris',
        name: 'QRIS (All Bank & E-Wallet)',
        desc: 'BCA, Mandiri, GoPay, ShopeePay',
        icon: '📱',
    },
    {
        id: 'va',
        name: 'Virtual Account Bank',
        desc: 'BCA, Mandiri, BNI, BRI Auto Detect',
        icon: '🏦',
    },
    {
        id: 'cod',
        name: 'Bayar di Tempat (COD)',
        desc: 'Bayar tunai ke kurir saat barang sampai',
        icon: '💵',
    },
];

const subtotal = computed(() => {
    return cartItems.value.reduce(
        (sum, item) => sum + item.price * item.qty,
        0,
    );
});

const currentShippingFee = computed(() => {
    if (subtotal.value >= 300000) {
        return 0;
    }

    const found = couriers.find((c) => selectedCourier.value.includes(c.name));

    return found ? found.price : 15000;
});

const grandTotal = computed(() => subtotal.value + currentShippingFee.value);

function fmtRp(n: number) {
    return 'Rp ' + n.toLocaleString('id');
}

function handleCheckoutSubmit() {
    if (isEmpty.value) {
        toast.error('Keranjang Anda kosong. Tambahkan produk terlebih dahulu.');

        return;
    }

    if (
        !customerName.value ||
        !customerEmail.value ||
        !customerPhone.value ||
        !shippingAddress.value
    ) {
        toast.error(
            'Lengkapi nama, email, nomor HP, dan alamat pengiriman Anda.',
        );

        return;
    }

    isLoading.value = true;

    router.post(
        '/checkout',
        {
            customer_name: customerName.value,
            customer_email: customerEmail.value,
            customer_phone: customerPhone.value,
            shipping_address: shippingAddress.value,
            shipping_courier: selectedCourier.value,
            payment_method: selectedPayment.value,
            items: cartItems.value.map((item) => ({
                id: item.id,
                name: item.name,
                price: item.price,
                qty: item.qty,
            })),
            notes: notes.value,
        },
        {
            onSuccess: () => {
                toast.success('Pesanan berhasil dibuat!');
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: () => {
                toast.error(
                    'Gagal membuat pesanan. Silakan periksa kembali data Anda.',
                );
            },
        },
    );
}
</script>

<template>
    <Head title="Checkout Pemesanan - Toko Instan" />

    <StorefrontLayout :cartCount="cartItems.length">
        <main class="mx-auto w-full max-w-[1400px] p-4 font-sans sm:p-6">
            <div class="mb-6">
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                >
                    Langkah Terakhir
                </p>
                <h1 class="text-2xl font-extrabold text-[#1c1c22]">
                    Checkout Pemesanan
                </h1>
            </div>

            <div
                v-if="isEmpty"
                class="flex flex-col items-center justify-center rounded-3xl border border-black/5 bg-white py-24 text-center"
            >
                <ShoppingBag class="mb-3 h-12 w-12 stroke-1 text-[#c8c8d5]" />
                <p class="text-base font-bold text-[#4a4a57]">
                    Keranjang Anda Kosong
                </p>
                <p class="mt-1 mb-5 text-xs text-[#9090a0]">
                    Pilih produk di marketplace untuk mulai checkout
                </p>
                <Button
                    variant="amber"
                    size="lg"
                    class="font-bold"
                    @click="router.visit('/marketplace')"
                >
                    Jelajahi Marketplace
                    <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
            </div>

            <form
                v-else
                @submit.prevent="handleCheckoutSubmit"
                class="grid grid-cols-1 gap-6 lg:grid-cols-12"
            >
                <!-- Left Form Column -->
                <div class="flex flex-col gap-5 lg:col-span-7">
                    <!-- Customer Information Card -->
                    <Card class="p-6">
                        <CardHeader class="mb-4 p-0">
                            <CardTitle
                                class="flex items-center gap-2 text-base"
                            >
                                <User class="h-4 w-4 text-[#e07c28]" />
                                Informasi Pembeli
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4 p-0">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold text-[#1c1c22]"
                                    >Nama Lengkap *</label
                                >
                                <Input
                                    v-model="customerName"
                                    placeholder="Contoh: Budi Santoso"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="flex flex-col gap-1">
                                    <label
                                        class="text-xs font-bold text-[#1c1c22]"
                                        >Email *</label
                                    >
                                    <Input
                                        v-model="customerEmail"
                                        type="email"
                                        placeholder="budi@email.com"
                                        required
                                    />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label
                                        class="text-xs font-bold text-[#1c1c22]"
                                        >Nomor WhatsApp / HP *</label
                                    >
                                    <Input
                                        v-model="customerPhone"
                                        type="tel"
                                        placeholder="08123456789"
                                        required
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Shipping Address Card -->
                    <Card class="p-6">
                        <CardHeader class="mb-4 p-0">
                            <CardTitle
                                class="flex items-center gap-2 text-base"
                            >
                                <MapPin class="h-4 w-4 text-[#e07c28]" />
                                Alamat Pengiriman
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4 p-0">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold text-[#1c1c22]"
                                    >Alamat Lengkap (Jalan, No. Rumah, RT/RW,
                                    Kecamatan, Kota) *</label
                                >
                                <textarea
                                    v-model="shippingAddress"
                                    rows="3"
                                    placeholder="Jl. Sudirman No. 45, RT 02/05, Kec. Kebayoran Baru, Jakarta Selatan, 12190"
                                    required
                                    class="w-full rounded-2xl border border-black/12 bg-white px-3.5 py-2.5 text-xs text-[#1c1c22] transition-all outline-none focus:border-[#e07c28] focus:ring-2 focus:ring-[#e07c28]/20"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Shipping Courier Selection -->
                    <Card class="p-6">
                        <CardHeader class="mb-4 p-0">
                            <CardTitle
                                class="flex items-center gap-2 text-base"
                            >
                                <Truck class="h-4 w-4 text-[#e07c28]" />
                                Opsi Ekspedisi Pengiriman
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-2.5 p-0">
                            <div
                                v-for="c in couriers"
                                :key="c.id"
                                @click="
                                    selectedCourier = `${c.name} (${c.priceFmt})`
                                "
                                class="flex cursor-pointer items-center justify-between rounded-2xl border p-3.5 transition-all"
                                :class="[
                                    selectedCourier.includes(c.name)
                                        ? 'border-[#e07c28] bg-[#e07c280f] shadow-2xs'
                                        : 'border-black/10 bg-white hover:border-black/20',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-4 w-4 items-center justify-center rounded-full border-2"
                                        :class="
                                            selectedCourier.includes(c.name)
                                                ? 'border-[#e07c28]'
                                                : 'border-black/20'
                                        "
                                    >
                                        <div
                                            v-if="
                                                selectedCourier.includes(c.name)
                                            "
                                            class="h-2 w-2 rounded-full bg-[#e07c28]"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-bold text-[#1c1c22]"
                                        >
                                            {{ c.name }}
                                        </p>
                                        <p class="text-[10px] text-[#9090a0]">
                                            Estimasi {{ c.est }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="font-mono text-xs font-bold text-[#e07c28]"
                                >
                                    {{
                                        subtotal >= 300000
                                            ? 'GRATIS'
                                            : c.priceFmt
                                    }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Payment Method Card -->
                    <Card class="p-6">
                        <CardHeader class="mb-4 p-0">
                            <CardTitle
                                class="flex items-center gap-2 text-base"
                            >
                                <CreditCard class="h-4 w-4 text-[#e07c28]" />
                                Metode Pembayaran
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-2.5 p-0">
                            <div
                                v-for="p in paymentMethods"
                                :key="p.id"
                                @click="selectedPayment = p.id"
                                class="flex cursor-pointer items-center justify-between rounded-2xl border p-3.5 transition-all"
                                :class="[
                                    selectedPayment === p.id
                                        ? 'border-[#e07c28] bg-[#e07c280f] shadow-2xs'
                                        : 'border-black/10 bg-white hover:border-black/20',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">{{ p.icon }}</span>
                                    <div>
                                        <p
                                            class="text-xs font-bold text-[#1c1c22]"
                                        >
                                            {{ p.name }}
                                        </p>
                                        <p class="text-[10px] text-[#9090a0]">
                                            {{ p.desc }}
                                        </p>
                                    </div>
                                </div>
                                <CheckCircle2
                                    v-if="selectedPayment === p.id"
                                    class="h-4 w-4 text-[#e07c28]"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Summary Column -->
                <div class="flex flex-col gap-5 lg:col-span-5">
                    <Card class="sticky top-24 p-6">
                        <CardTitle
                            class="mb-4 flex items-center justify-between text-base"
                        >
                            <span>Ringkasan Pesanan</span>
                            <Badge variant="amber" class="text-[10px]"
                                >{{ cartItems.length }} produk</Badge
                            >
                        </CardTitle>

                        <!-- Items list -->
                        <div
                            class="flex flex-col gap-3 border-b border-black/8 pb-4"
                        >
                            <div
                                v-for="item in cartItems"
                                :key="item.id"
                                class="flex items-center gap-3"
                            >
                                <img
                                    :src="item.img"
                                    :alt="item.name"
                                    class="h-12 w-12 rounded-xl border border-black/5 bg-black/5 object-cover"
                                />
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-xs font-bold text-[#1c1c22]"
                                    >
                                        {{ item.name }}
                                    </p>
                                    <p class="text-[10px] text-[#9090a0]">
                                        {{ item.store }} · x{{ item.qty }}
                                    </p>
                                </div>
                                <span
                                    class="font-mono text-xs font-bold text-[#1c1c22]"
                                >
                                    {{ fmtRp(item.price * item.qty) }}
                                </span>
                            </div>
                        </div>

                        <!-- Cost Summary breakdown -->
                        <div
                            class="flex flex-col gap-2 border-b border-black/8 py-4 text-xs"
                        >
                            <div class="flex justify-between text-[#4a4a57]">
                                <span>Subtotal Produk</span>
                                <span class="font-mono font-semibold">{{
                                    fmtRp(subtotal)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-[#4a4a57]">
                                <span>Biaya Pengiriman</span>
                                <span
                                    class="font-mono font-semibold text-[#e07c28]"
                                >
                                    {{
                                        currentShippingFee === 0
                                            ? 'GRATIS'
                                            : fmtRp(currentShippingFee)
                                    }}
                                </span>
                            </div>
                            <div
                                class="flex justify-between pt-1 font-medium text-[#22a15a]"
                            >
                                <span class="flex items-center gap-1">
                                    <ShieldCheck class="h-3.5 w-3.5" /> Jaminan
                                    Rekening Bersama Escrow
                                </span>
                                <span>Gratis</span>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div
                            class="flex items-center justify-between pt-4 pb-5"
                        >
                            <div>
                                <p
                                    class="text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                                >
                                    Total Tagihan
                                </p>
                                <p
                                    class="mt-0.5 font-mono text-2xl leading-none font-extrabold text-[#e07c28]"
                                >
                                    {{ fmtRp(grandTotal) }}
                                </p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            variant="amber"
                            size="lg"
                            class="flex h-12 w-full items-center justify-center gap-2 text-sm font-bold shadow-md"
                            :disabled="isLoading"
                        >
                            <Loader2
                                v-if="isLoading"
                                class="h-4 w-4 animate-spin"
                            />
                            <template v-else>
                                <span>Buat Pesanan Sekarang</span>
                                <ArrowRight class="h-4 w-4" />
                            </template>
                        </Button>
                    </Card>
                </div>
            </form>
        </main>
    </StorefrontLayout>
</template>
