<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
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
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useCart } from '@/composables/useCart';
import { useCheckout } from '@/composables/useCheckout';
import { useStoreName } from '@/composables/useStoreName';
import { Link } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useIndoRegions } from '@/composables/useIndoRegions';

const props = defineProps<{
    addresses?: any[];
}>();

// Cart is shared & persisted via localStorage composable
const { items: cartItems } = useCart();

const { storeName } = useStoreName();

const cartItemsRef = computed(() => cartItems.value);

const {
    isEmpty,
    manualName,
    manualEmail,
    manualPhone,
    manualAddress,
    manualProvince,
    manualCity,
    manualDistrict,
    manualPostalCode,
    customerName,
    customerEmail,
    customerPhone,
    shippingAddress,
    selectedAddressId,
    availableAddresses,
    selectedCourier,
    selectedPayment,
    isLoading,
    couriers,
    paymentMethods,
    subtotal,
    currentShippingFee,
    grandTotal,
    fmtRp,
    handleCheckoutSubmit,
} = useCheckout(cartItemsRef);

if (props.addresses && props.addresses.length > 0) {
    availableAddresses.value = props.addresses;
    
    // Default select first address if any
    const defaultAddress = props.addresses.find(a => a.is_default);
    selectedAddressId.value = defaultAddress ? defaultAddress.id : props.addresses[0].id;
}

// Guest mode: buyers without an account choose between ordering as a
// guest or logging in first.
const page = usePage();
const storeSlug = computed(() => (page.props.store as any)?.slug ?? '');
const isGuest = computed(() => !(page.props.auth as any)?.user);
const guestMode = ref(false);

function goLogin() {
    router.visit(`/${storeSlug.value}/login`);
}

const { provinces, cities, districts, loadProvinces, loadCities, loadDistricts } = useIndoRegions();

onMounted(() => {
    loadProvinces();
});

watch([() => manualProvince.value, provinces], ([newProvName, provs], [oldProvName]) => {
    const prov = provs.find(p => p.name === newProvName);
    if (prov) {
        loadCities(prov.id);
    } else {
        cities.value = [];
    }
    
    if (oldProvName !== undefined && oldProvName !== newProvName) {
        manualCity.value = '';
        manualDistrict.value = '';
    }
});

watch([() => manualCity.value, cities], ([newCityName, cits], [oldCityName]) => {
    const city = cits.find(c => c.name === newCityName);
    if (city) {
        loadDistricts(city.id);
    } else {
        districts.value = [];
    }
    
    if (oldCityName !== undefined && oldCityName !== newCityName) {
        manualDistrict.value = '';
    }
});
</script>

<template>
    <Head :title="`Checkout Pemesanan — ${storeName}`" />

    <StorefrontLayout :cartCount="cartItems.length">
        <main
            class="mx-auto w-full max-w-[1400px] px-4 pt-4 pb-28 font-sans sm:p-6"
        >
            <div class="mb-6">
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-brand uppercase"
                >
                    Langkah Terakhir
                </p>
                <h1 class="text-2xl font-extrabold text-foreground">
                    Checkout Pemesanan
                </h1>
            </div>

            <div
                v-if="isEmpty"
                class="flex flex-col items-center justify-center rounded-3xl border border-border bg-card py-24 text-center"
            >
                <ShoppingBag class="mb-3 h-12 w-12 stroke-1 text-border" />
                <p class="text-base font-bold text-foreground">
                    Keranjang Anda Kosong
                </p>
                <p class="mt-1 mb-5 text-xs text-muted-foreground">
                    Pilih produk di marketplace untuk mulai checkout
                </p>
                <Button
                    size="lg"
                    class="font-bold bg-brand text-brand-foreground hover:opacity-90 border-0"
                    @click="router.visit('/' + (usePage().props.store?.slug ?? ''))"
                >
                    Jelajahi Marketplace
                    <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
            </div>

            <template v-else>
                <!-- Guest gate: 2 choices before showing the form -->
                <div
                    v-if="isGuest && !guestMode"
                    class="flex flex-col items-center justify-center rounded-3xl border border-border bg-card py-16 text-center"
                >
                    <ShoppingBag class="mb-3 h-12 w-12 stroke-1 text-border" />
                    <p class="text-base font-bold text-foreground">
                        Belanja Tanpa Login
                    </p>
                    <p class="mt-1 mb-6 max-w-sm text-xs text-muted-foreground">
                        Kamu bisa langsung pesan tanpa membuat akun, atau login
                        untuk menyimpan data & melacak pesanan di akunmu.
                    </p>
                    <div class="flex w-full max-w-xs flex-col gap-3 sm:flex-row sm:max-w-md">
                        <Button
                            size="lg"
                            class="flex-1 bg-brand font-bold text-brand-foreground hover:opacity-90 border-0"
                            @click="guestMode = true"
                        >
                            Langsung Pesan
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            class="flex-1 font-bold"
                            @click="goLogin"
                        >
                            Login Dulu
                        </Button>
                    </div>
                </div>

                <form
                    v-else
                    @submit.prevent="handleCheckoutSubmit"
                    class="flex flex-col gap-5 lg:grid lg:grid-cols-12 lg:gap-6"
                >
                <!-- Left Form Column — appears second on mobile -->
                <div
                    class="order-2 flex flex-col gap-5 lg:order-1 lg:col-span-7"
                >
                    <!-- Shipping Address Selection -->
                    <Card class="p-6">
                        <CardHeader class="mb-4 p-0">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <MapPin class="h-4 w-4 text-brand" />
                                Alamat Pengiriman
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4 p-0">
                            <template v-if="availableAddresses && availableAddresses.length > 0">
                                <div class="flex flex-col gap-3">
                                    <div
                                        v-for="addr in availableAddresses"
                                        :key="addr.id"
                                        @click="selectedAddressId = addr.id"
                                        class="flex cursor-pointer items-start justify-between rounded-2xl border p-4 transition-all"
                                        :class="[
                                            selectedAddressId === addr.id
                                                ? 'border-brand bg-brand-surface shadow-2xs'
                                                : 'border-border bg-card hover:border-border/60 hover:bg-muted/30',
                                        ]"
                                    >
                                        <div class="flex flex-col gap-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-bold text-foreground">{{ addr.recipient_name }}</span>
                                                <Badge v-if="addr.is_default" variant="secondary" class="bg-black/10 text-[10px]">Utama</Badge>
                                                <Badge variant="outline" class="text-[10px]">{{ addr.label }}</Badge>
                                            </div>
                                            <span class="text-xs text-muted-foreground">{{ addr.phone }}</span>
                                            <span class="mt-1 text-sm text-muted-foreground leading-relaxed">
                                                {{ addr.address }}, {{ addr.city }}, {{ addr.province }}, {{ addr.postal_code }}
                                            </span>
                                        </div>
                                        <div
                                            class="mt-1 flex h-5 w-5 items-center justify-center rounded-full border-2"
                                            :class="
                                                selectedAddressId === addr.id
                                                    ? 'border-brand'
                                                    : 'border-border'
                                            "
                                        >
                                            <div
                                                v-if="selectedAddressId === addr.id"
                                                class="h-2.5 w-2.5 rounded-full bg-brand"
                                            ></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Manual Address Option -->
                                    <div
                                        @click="selectedAddressId = 'manual'"
                                        class="flex cursor-pointer items-center justify-between rounded-2xl border p-4 transition-all"
                                        :class="[
                                            selectedAddressId === 'manual'
                                                ? 'border-brand bg-brand-surface shadow-2xs'
                                                : 'border-border bg-card hover:border-border/60 hover:bg-muted/30',
                                        ]"
                                    >
                                        <span class="font-bold text-foreground">Gunakan Alamat Baru (Manual)</span>
                                        <div
                                            class="flex h-5 w-5 items-center justify-center rounded-full border-2"
                                            :class="
                                                selectedAddressId === 'manual'
                                                    ? 'border-brand'
                                                    : 'border-border'
                                            "
                                        >
                                            <div
                                                v-if="selectedAddressId === 'manual'"
                                                class="h-2.5 w-2.5 rounded-full bg-brand"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Manual Form -->
                            <div v-if="!availableAddresses?.length || selectedAddressId === 'manual'" class="mt-2 flex flex-col gap-4 rounded-2xl border border-border bg-muted/30 p-4">
                                <div class="flex flex-col gap-1.5">
                                    <Label for="c-name" class="text-xs font-bold text-foreground">Nama Lengkap Penerima *</Label>
                                    <Input id="c-name" v-model="manualName" placeholder="Contoh: Budi Santoso" required class="bg-card" />
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="flex flex-col gap-1.5">
                                        <Label for="c-email" class="text-xs font-bold text-foreground">Email *</Label>
                                        <Input id="c-email" v-model="manualEmail" type="email" placeholder="budi@email.com" required class="bg-card" />
                                    </div>
                                    <div class="flex flex-col gap-1.5">
                                        <Label for="c-phone" class="text-xs font-bold text-foreground">Nomor WhatsApp / HP *</Label>
                                        <Input id="c-phone" v-model="manualPhone" type="tel" placeholder="08123456789" required class="bg-card" />
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <Label for="c-province" class="text-xs font-bold text-foreground">Provinsi *</Label>
                                    <Select v-model="manualProvince" required>
                                        <SelectTrigger id="c-province" class="bg-card">
                                            <SelectValue placeholder="Pilih Provinsi" />
                                        </SelectTrigger>
                                        <SelectContent class="max-h-60">
                                            <SelectGroup>
                                                <SelectItem v-for="prov in provinces" :key="prov.id" :value="prov.name">
                                                    {{ prov.name }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="flex flex-col gap-1.5">
                                        <Label for="c-city" class="text-xs font-bold text-foreground">Kota/Kabupaten *</Label>
                                        <Select v-model="manualCity" required :disabled="!manualProvince">
                                            <SelectTrigger id="c-city" class="bg-card">
                                                <SelectValue placeholder="Pilih Kota/Kabupaten" />
                                            </SelectTrigger>
                                            <SelectContent class="max-h-60">
                                                <SelectGroup>
                                                    <SelectItem v-for="city in cities" :key="city.id" :value="city.name">
                                                        {{ city.name }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="flex flex-col gap-1.5">
                                        <Label for="c-district" class="text-xs font-bold text-foreground">Kecamatan *</Label>
                                        <Select v-model="manualDistrict" required :disabled="!manualCity">
                                            <SelectTrigger id="c-district" class="bg-card">
                                                <SelectValue placeholder="Pilih Kecamatan" />
                                            </SelectTrigger>
                                            <SelectContent class="max-h-60">
                                                <SelectGroup>
                                                    <SelectItem v-for="district in districts" :key="district.id" :value="district.name">
                                                        {{ district.name }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div class="flex flex-col gap-1.5">
                                        <Label for="c-postal" class="text-xs font-bold text-foreground">Kode Pos *</Label>
                                        <Input
                                            id="c-postal"
                                            v-model="manualPostalCode"
                                            required
                                            type="number"
                                            maxlength="5"
                                            placeholder="12190"
                                            class="bg-card"
                                        />
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <Label for="c-address" class="text-xs font-bold text-foreground">Alamat Lengkap *</Label>
                                    <Textarea
                                        id="c-address"
                                        v-model="manualAddress"
                                        rows="2"
                                        placeholder="Nama Jalan, Gedung, No. Rumah, RT/RW, Patokan"
                                        required
                                        class="resize-none bg-card"
                                    />
                                </div>
                            </div>

                            <!-- If a saved address is selected, just show the email input -->
                            <div v-else class="mt-2 flex flex-col gap-1.5 rounded-2xl border border-border p-4">
                                <Label for="c-email-only" class="text-xs font-bold text-foreground">Email * <span class="font-normal text-muted-foreground">(Untuk konfirmasi pesanan)</span></Label>
                                <Input id="c-email-only" v-model="manualEmail" type="email" placeholder="budi@email.com" required />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Shipping Courier Selection -->
                    <Card class="p-6">
                        <CardHeader class="mb-4 p-0">
                            <CardTitle
                                class="flex items-center gap-2 text-base"
                            >
                                <Truck class="h-4 w-4 text-brand" />
                                Opsi Ekspedisi Pengiriman
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-2.5 p-0">
                            <div
                                v-for="c in couriers"
                                :key="c.id"
                                @click="selectedCourier = `${c.name} (${c.priceFmt})`"
                                class="flex cursor-pointer items-center justify-between rounded-2xl border p-3.5 transition-all"
                                :class="[
                                    selectedCourier.includes(c.name)
                                        ? 'border-brand bg-brand-surface shadow-2xs'
                                        : 'border-border bg-card hover:border-border/60',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-4 w-4 items-center justify-center rounded-full border-2"
                                        :class="
                                            selectedCourier.includes(c.name)
                                                ? 'border-brand'
                                                : 'border-border'
                                        "
                                    >
                                        <div
                                            v-if="selectedCourier.includes(c.name)"
                                            class="h-2 w-2 rounded-full bg-brand"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-bold text-foreground"
                                        >
                                            {{ c.name }}
                                        </p>
                                        <p class="text-[10px] text-muted-foreground">
                                            Estimasi {{ c.est }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="font-mono text-xs font-bold text-brand"
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
                                <CreditCard class="h-4 w-4 text-brand" />
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
                                            class="text-xs font-bold text-foreground"
                                        >
                                            {{ p.name }}
                                        </p>
                                        <p class="text-[10px] text-muted-foreground">
                                            {{ p.desc }}
                                        </p>
                                    </div>
                                </div>
                                <CheckCircle2
                                    v-if="selectedPayment === p.id"
                                    class="h-4 w-4 text-brand"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Summary Column — appears FIRST on mobile -->
                <div
                    class="order-1 flex flex-col gap-5 lg:order-2 lg:col-span-5"
                >
                    <Card class="sticky top-24 p-6">
                        <CardTitle
                            class="mb-4 flex items-center justify-between text-base"
                        >
                            <span>Ringkasan Pesanan</span>
                            <Badge variant="default" class="text-[10px]"
                                >{{ cartItems.length }} produk</Badge
                            >
                        </CardTitle>

                        <!-- Items list -->
                        <div
                            class="flex flex-col gap-3 border-b border-border pb-4"
                        >
                            <div
                                v-for="item in cartItems"
                                :key="item.id"
                                class="flex items-center gap-3"
                            >
                                <img
                                    :src="item.img"
                                    :alt="item.name"
                                    class="h-12 w-12 rounded-xl border border-border bg-muted/40 object-cover"
                                />
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-xs font-bold text-foreground"
                                    >
                                        {{ item.name }}
                                    </p>
                                    <p class="text-[10px] text-muted-foreground">
                                        {{ item.store }} · x{{ item.qty }}
                                    </p>
                                </div>
                                <span
                                    class="font-mono text-xs font-bold text-foreground"
                                >
                                    {{ fmtRp(item.price * item.qty) }}
                                </span>
                            </div>
                        </div>

                        <!-- Cost Summary breakdown -->
                        <div
                            class="flex flex-col gap-2 border-b border-border py-4 text-xs"
                        >
                            <div class="flex justify-between text-muted-foreground">
                                <span>Subtotal Produk</span>
                                <span class="font-mono font-semibold">{{
                                    fmtRp(subtotal)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-muted-foreground">
                                <span>Biaya Pengiriman</span>
                                <span
                                    class="font-mono font-semibold text-brand"
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
                                    class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Total Tagihan
                                </p>
                                <p
                                    class="mt-0.5 font-mono text-2xl leading-none font-extrabold text-brand"
                                >
                                    {{ fmtRp(grandTotal) }}
                                </p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            size="lg"
                            class="flex h-12 w-full items-center justify-center gap-2 text-sm font-bold shadow-md bg-brand text-brand-foreground hover:opacity-90 border-0"
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
        </template>
        </main>
    </StorefrontLayout>
</template>
