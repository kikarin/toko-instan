<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Settings,
    HelpCircle,
    ChevronRight,
    CreditCard,
    Package,
    Truck,
    CheckCircle2,
    Star,
    Store,
    Share2,
    Heart,
    Gift,
    QrCode,
    Sparkles,
    ShoppingBag,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import ProductDetailModal from '@/components/marketplace/ProductDetailModal.vue';
import { Avatar } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { toast } from '@/components/ui/sonner';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useActiveUser } from '@/composables/useActiveUser';
import { useCart } from '@/composables/useCart';
import { useStoreName } from '@/composables/useStoreName';
import type { ProductDetail } from '@/types/product';
import type { UserProfile } from '@/types/user';
import { useMarketplaceCart } from '@/composables/useMarketplaceCart';

interface Props {
    user: UserProfile;
    orderCounts: {
        bayar: number;
        diproses: number;
        dikirim: number;
        sudah_tiba: number;
        ulasan: number;
    };
    vouchers: {
        shopping: number;
        shipping: number;
    };
    recommendedProducts: any[];
}

const props = defineProps<Props>();
const activeUser = useActiveUser();

const { storeName } = useStoreName();

const userName = computed(() => {
    return (
        activeUser.value?.displayName ||
        activeUser.value?.name ||
        props.user.name ||
        'Pengguna Toko Instan'
    );
});

const userAvatar = computed(() => {
    return activeUser.value?.photoURL || props.user.avatar || undefined;
});

const userInitial = computed(() => {
    return userName.value.substring(0, 2).toUpperCase();
});

// Cart
const {
    cartItems,
    totalCartCount,
    isCartOpen,
    openCart,
    addToCart,
    updateCartQty,
    removeFromCart,
    goCheckout,
} = useMarketplaceCart();

// Modal
const activeProductModal = ref<ProductDetail | null>(null);

function openProductDetail(product: any) {
    activeProductModal.value = product;
}
</script>

<template>
    <Head :title="`Akun Saya — ${storeName}`" />

    <StorefrontLayout
        :cartCount="totalCartCount"
        @search="
            (q: string) => router.visit('/' + (usePage().props.store?.slug ?? ''), { data: { search: q } })
        "
    >
        <main
            class="mx-auto w-full max-w-[1200px] px-3 pt-3 pb-28 sm:p-6 lg:p-8"
        >
            <div class="flex flex-col gap-6">
                <!-- ── 1. Top User Profile Header ── -->
                <div class="flex items-center justify-between pt-2">
                    <div
                        @click="router.visit('/profile/edit')"
                        class="group flex cursor-pointer items-center gap-3.5 sm:gap-4"
                    >
                        <Avatar
                            :src="userAvatar"
                            :fallback="userInitial"
                            :hue="220"
                            class="h-16 w-16 text-xl font-extrabold shadow-md ring-4 ring-white transition-transform group-hover:scale-105 sm:h-20 sm:w-20 sm:text-2xl"
                        />
                        <div class="flex flex-col gap-1">
                            <h1
                                class="text-lg font-extrabold text-[#1c1c22] transition-colors group-hover:text-black sm:text-2xl"
                            >
                                {{ userName }}
                            </h1>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="default"
                                    size="sm"
                                    class="h-6 gap-1 rounded-full bg-black px-2.5 text-[10px] font-black text-accent uppercase shadow-2xs hover:bg-foreground/90"
                                    @click="
                                        toast.info(
                                            `Akun Anda terverifikasi sebagai ${storeName} Member.`,
                                        )
                                    "
                                >
                                    <ShoppingBag
                                        class="h-3 w-3 text-accent"
                                    />
                                    {{ storeName }} Member VIP
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Top Action Icons -->
                    <div class="flex items-center gap-2">
                        <button
                            title="Bantuan & Dukungan"
                            @click="
                                toast.info(
                                    `Layanan Pelanggan ${storeName} Siap 24/7!`,
                                )
                            "
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-black/6 bg-white text-[#4a4a57] shadow-xs transition-colors hover:bg-[#f5f4f0]"
                        >
                            <HelpCircle class="h-4 w-4" />
                        </button>
                        <button
                            title="Pengaturan"
                            @click="router.visit('/' + ((usePage().props.store as any)?.slug ?? '') + '/settings')"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-black/6 bg-white text-[#4a4a57] shadow-xs transition-colors hover:bg-[#f5f4f0]"
                        >
                            <Settings class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- ── 2. Top Promos & Loyalty Card ── -->
                <Card
                    class="overflow-hidden rounded-2xl border-black/6 bg-white shadow-xs"
                >
                    <CardContent class="flex flex-col gap-4 p-4 sm:p-5">
                        <!-- Top Banner Split -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <!-- Banner 1 -->
                            <div
                                class="flex items-center justify-between rounded-xl border border-border bg-gradient-to-r from-zinc-900 via-black to-zinc-800 p-3.5 text-white"
                            >
                                <div>
                                    <p
                                        class="text-xs leading-tight font-bold text-accent"
                                    >
                                        {{ storeName }} Member Rewards
                                    </p>
                                    <p class="mt-0.5 text-[10px] text-muted-foreground">
                                        Dapatkan diskon khusus & rilis sepatu
                                        perdana
                                    </p>
                                </div>
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent text-black shadow-xs"
                                >
                                    <ShoppingBag class="h-5 w-5" />
                                </div>
                            </div>

                            <!-- Banner 2 -->
                            <div
                                class="flex items-center justify-between rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 p-3.5 text-white shadow-xs"
                            >
                                <div>
                                    <div class="flex items-center gap-1">
                                        <Sparkles
                                            class="h-3.5 w-3.5 fill-accent/80 text-accent/80"
                                        />
                                        <span
                                            class="text-xs font-black tracking-wider text-accent-foreground uppercase"
                                            >Official Guarantee</span
                                        >
                                    </div>
                                    <p class="mt-0.5 text-xs font-bold">
                                        100% Produk Original & Bebas Ongkir
                                    </p>
                                </div>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="h-7 rounded-lg border-white/40 bg-white/20 text-[10px] font-bold text-white hover:bg-white/30"
                                >
                                    Cek Status
                                </Button>
                            </div>
                        </div>

                        <!-- 4 Loyalty Stats Bar -->
                        <div
                            class="grid grid-cols-4 divide-x divide-black/6 pt-1 text-center"
                        >
                            <!-- 1. Voucher Diskon -->
                            <div
                                class="flex cursor-pointer flex-col items-center gap-1 px-1 py-1 hover:opacity-80"
                                @click="
                                    toast.info(
                                        `Anda memiliki 5 Voucher Diskon ${storeName}!`,
                                    )
                                "
                            >
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-destructive/20 text-xs font-black text-destructive"
                                >
                                    %
                                </div>
                                <span
                                    class="font-mono text-xs font-black text-[#1c1c22]"
                                    >{{ vouchers.shopping }}</span
                                >
                                <span
                                    class="text-[9px] leading-none font-semibold text-[#9090a0] sm:text-[10px]"
                                    >Voucher Diskon</span
                                >
                            </div>

                            <!-- 2. Voucher Ongkir -->
                            <div
                                class="flex cursor-pointer flex-col items-center gap-1 px-1 py-1 hover:opacity-80"
                                @click="
                                    toast.info(
                                        'Anda memiliki 6 Voucher Bebas Ongkir!',
                                    )
                                "
                            >
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/20 text-xs font-black text-emerald-600"
                                >
                                    <Truck class="h-4 w-4" />
                                </div>
                                <span
                                    class="font-mono text-xs font-black text-[#1c1c22]"
                                    >{{ vouchers.shipping }}</span
                                >
                                <span
                                    class="text-[9px] leading-none font-semibold text-[#9090a0] sm:text-[10px]"
                                    >Bebas Ongkir</span
                                >
                            </div>

                            <!-- 3. Store Points -->
                            <div
                                class="flex cursor-pointer flex-col items-center gap-1 px-1 py-1 hover:opacity-80"
                                @click="
                                    toast.info(
                                        `Kumpulkan poin belanja ${storeName} Member!`,
                                    )
                                "
                            >
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-accent/20 text-xs font-black text-accent"
                                >
                                    <Gift class="h-4 w-4" />
                                </div>
                                <span
                                    class="text-xs font-extrabold text-accent"
                                    >1.250 pts</span
                                >
                                <span
                                    class="text-[9px] leading-none font-semibold text-[#9090a0] sm:text-[10px]"
                                    >{{ storeName }} Points</span
                                >
                            </div>

                            <!-- 4. Store Pass -->
                            <div
                                class="flex cursor-pointer flex-col items-center gap-1 px-1 py-1 hover:opacity-80"
                                @click="toast.info(`${storeName} Member Pass`)"
                            >
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-black text-xs font-black text-white"
                                >
                                    <QrCode class="h-4 w-4" />
                                </div>
                                <span class="text-xs font-extrabold text-black"
                                    >Aktif</span
                                >
                                <span
                                    class="text-[9px] leading-none font-semibold text-[#9090a0] sm:text-[10px]"
                                    >{{ storeName }} Pass</span
                                >
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- ── 3. Transaksi Section ── -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-extrabold text-[#1c1c22]">
                            Transaksi
                        </h2>
                        <button
                            @click="router.visit('/' + ((usePage().props.store as any)?.slug ?? '') + '/orders')"
                            class="flex items-center gap-0.5 text-xs font-bold text-[#e07c28] hover:underline"
                        >
                            <span>Lihat Riwayat</span>
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>

                    <Card
                        class="rounded-2xl border-black/6 bg-white p-4 shadow-xs"
                    >
                        <div class="grid grid-cols-5 text-center">
                            <!-- Bayar -->
                            <button
                                @click="router.visit('/' + ((usePage().props.store as any)?.slug ?? '') + '/orders')"
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-black/6 bg-[#faf9f6] text-[#1c1c22] transition-all group-hover:scale-105"
                                >
                                    <CreditCard class="h-5 w-5" />
                                    <span
                                        v-if="orderCounts.bayar > 0"
                                        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#e02020] text-[9px] font-black text-white"
                                    >
                                        {{ orderCounts.bayar }}
                                    </span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >Bayar</span
                                >
                            </button>

                            <!-- Diproses -->
                            <button
                                @click="router.visit('/' + ((usePage().props.store as any)?.slug ?? '') + '/orders')"
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-black/6 bg-[#faf9f6] text-[#1c1c22] transition-all group-hover:scale-105"
                                >
                                    <Package class="h-5 w-5" />
                                    <span
                                        v-if="orderCounts.diproses > 0"
                                        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#e07c28] text-[9px] font-black text-white"
                                    >
                                        {{ orderCounts.diproses }}
                                    </span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >Diproses</span
                                >
                            </button>

                            <!-- Dikirim -->
                            <button
                                @click="router.visit('/' + ((usePage().props.store as any)?.slug ?? '') + '/orders')"
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-black/6 bg-[#faf9f6] text-[#1c1c22] transition-all group-hover:scale-105"
                                >
                                    <Truck class="h-5 w-5" />
                                    <span
                                        v-if="orderCounts.dikirim > 0"
                                        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#3b82f6] text-[9px] font-black text-white"
                                    >
                                        {{ orderCounts.dikirim }}
                                    </span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >Dikirim</span
                                >
                            </button>

                            <!-- Sudah Tiba -->
                            <button
                                @click="router.visit('/' + ((usePage().props.store as any)?.slug ?? '') + '/orders')"
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-black/6 bg-[#faf9f6] text-[#1c1c22] transition-all group-hover:scale-105"
                                >
                                    <CheckCircle2
                                        class="h-5 w-5 text-emerald-600"
                                    />
                                    <span
                                        v-if="orderCounts.sudah_tiba > 0"
                                        class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500/100 text-[9px] font-black text-white"
                                    >
                                        {{ orderCounts.sudah_tiba }}
                                    </span>
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >Sudah Tiba</span
                                >
                            </button>

                            <!-- Ulasan -->
                            <button
                                @click="
                                    toast.info('Tidak ada ulasan tertunda!')
                                "
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl border border-black/6 bg-[#faf9f6] text-[#1c1c22] transition-all group-hover:scale-105"
                                >
                                    <Star class="h-5 w-5 text-accent" />
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >Ulasan</span
                                >
                            </button>
                        </div>
                    </Card>
                </div>

                <!-- ── 4. Menu Lainnya Section ── -->
                <div class="flex flex-col gap-3">
                    <h2 class="text-base font-extrabold text-[#1c1c22]">
                        Menu Lainnya
                    </h2>

                    <Card
                        class="rounded-2xl border-black/6 bg-white p-4 shadow-xs"
                    >
                        <div class="grid grid-cols-4 text-center">
                            <!-- Dashboard Seller / Nike Manager -->
                            <button
                                @click="router.visit('/dashboard')"
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-black text-white transition-all group-hover:scale-105"
                                >
                                    <Store class="h-6 w-6 text-accent" />
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >Dashboard</span
                                >
                            </button>

                            <!-- Store Club Affiliate -->
                            <button
                                @click="
                                    toast.info(
                                        `Program ${storeName} Club Affiliate!`,
                                    )
                                "
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/20 text-emerald-600 transition-all group-hover:scale-105"
                                >
                                    <Share2 class="h-6 w-6" />
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >{{ storeName }} Club</span
                                >
                            </button>

                            <!-- Wishlist -->
                            <button
                                @click="router.visit('/' + ((usePage().props.store as any)?.slug ?? '') + '/wishlist')"
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-destructive/20/70 text-destructive transition-all group-hover:scale-105"
                                >
                                    <Heart class="h-6 w-6" />
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >Wishlist</span
                                >
                            </button>

                            <!-- Panduan Ukuran -->
                            <button
                                @click="
                                    toast.info(
                                        `Panduan Ukuran Sepatu & Clothing ${storeName}`,
                                    )
                                "
                                class="group flex cursor-pointer flex-col items-center gap-2 p-1"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20 text-primary transition-all group-hover:scale-105"
                                >
                                    <Footprints class="h-6 w-6" />
                                </div>
                                <span
                                    class="text-[11px] font-semibold text-[#4a4a57]"
                                    >Size Guide</span
                                >
                            </button>
                        </div>
                    </Card>
                </div>

                <!-- ── 5. Rekomendasi Untuk Anda Section ── -->
                <div class="flex flex-col gap-4">
                    <h2 class="text-base font-extrabold text-[#1c1c22]">
                        Rekomendasi Untuk Anda
                    </h2>

                    <div
                        class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-6"
                    >
                        <ProductCard
                            v-for="p in recommendedProducts"
                            :key="p.id"
                            :product="p"
                            @click="openProductDetail(p)"
                            @add-to-cart="addToCart"
                        />
                    </div>
                </div>
            </div>
        </main>


        <!-- Product Detail Modal -->
        <ProductDetailModal
            :product="activeProductModal"
            @close="activeProductModal = null"
            @add-to-cart="addToCart"
        />
    </StorefrontLayout>
</template>
