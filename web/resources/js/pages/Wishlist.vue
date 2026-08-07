<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Heart, ShoppingBag, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import CartDrawer from '@/components/marketplace/CartDrawer.vue';
import ProductCard from '@/components/marketplace/ProductCard.vue';
import ProductDetailModal from '@/components/marketplace/ProductDetailModal.vue';
import type { ProductDetail } from '@/components/marketplace/ProductDetailModal.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { toast } from '@/components/ui/sonner';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useCart } from '@/lib/useCart';
import { useWishlist } from '@/lib/useWishlist';
import type { WishlistItem } from '@/stores/useWishlistStore';

interface Props {
    products?: WishlistItem[];
}

const props = defineProps<Props>();

const { items: wishlistItems, count: wishlistCount, clear: clearWishlist, replaceItems } = useWishlist();

if (props.products) {
    replaceItems(props.products);
}

// Cart
const {
    items: cartItems,
    totalCount: totalCartCount,
    addItem,
    updateQty,
    removeItem: removeCartItem,
} = useCart();
const isCartOpen = ref(false);

// Modal
const activeProductModal = ref<ProductDetail | null>(null);

function addToCart(product: any, addQty = 1) {
    const rawPrice =
        product.priceNum ||
        parseInt(product.price.replace(/[^\d]/g, ''), 10) ||
        100000;

    addItem(
        {
            id: product.id,
            name: product.name,
            price: rawPrice,
            formattedPrice: product.price,
            img: product.img,
            store: product.store,
            qty: addQty,
        },
        addQty,
    );

    isCartOpen.value = true;
    toast.success(`${product.name} ditambahkan ke keranjang!`);
}

function updateCartQty(id: number, delta: number) {
 updateQty(id, delta); 
}
function removeFromCart(id: number) {
 removeCartItem(id); 
}
function goCheckout() {
 isCartOpen.value = false; router.visit('/checkout'); 
}
function openProductDetail(product: any) {
 activeProductModal.value = product; 
}
</script>

<template>
    <Head title="Wishlist Saya — Toko Instan" />

    <StorefrontLayout
        :cartCount="totalCartCount"
        :wishlistCount="wishlistCount"
        @open-cart="isCartOpen = true"
        @search="(q: string) => router.visit('/marketplace', { data: { search: q } })"
    >
        <main class="mx-auto w-full max-w-[1200px] p-3 sm:p-6 lg:p-8">
            <div class="flex flex-col gap-6">

                <!-- ── Header ── -->
                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center gap-3">
                        <button
                            @click="router.visit('/account')"
                            class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-white shadow-xs border border-black/6 text-[#1c1c22] hover:bg-black/5 transition-colors"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </button>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl font-extrabold text-[#1c1c22]">Wishlist Saya</h1>
                                <Heart class="h-5 w-5 fill-[#e0405a] text-[#e0405a]" />
                            </div>
                            <p class="text-xs text-[#9090a0]">
                                {{ wishlistCount }} produk tersimpan di barang impianmu
                            </p>
                        </div>
                    </div>

                    <!-- Clear Wishlist button -->
                    <Button
                        v-if="wishlistCount > 0"
                        variant="outline"
                        size="sm"
                        class="h-8 border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold gap-1.5"
                        @click="() => { clearWishlist(); toast.info('Wishlist telah dikosongkan'); }"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                        Kosongkan
                    </Button>
                </div>

                <!-- ── Wishlist Product Grid ── -->
                <div
                    v-if="wishlistCount > 0"
                    class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6"
                >
                    <ProductCard
                        v-for="p in wishlistItems"
                        :key="p.id"
                        :product="p"
                        @click="openProductDetail(p)"
                        @add-to-cart="addToCart"
                    />
                </div>

                <!-- ── Empty State ── -->
                <Card v-else class="rounded-2xl border-black/6 shadow-xs bg-white">
                    <CardContent class="flex flex-col items-center justify-center py-20 text-center gap-3">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-rose-50 border border-rose-100">
                            <Heart class="h-10 w-10 text-rose-400" />
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#1c1c22]">Wishlist Kamu Masih Kosong</h2>
                            <p class="text-xs text-[#9090a0] mt-1 max-w-sm">
                                Simpan produk barang impianmu dengan menekan ikon hati pada produk pilihanmu.
                            </p>
                        </div>
                        <Button
                            variant="amber"
                            size="sm"
                            class="mt-2 rounded-xl text-xs font-bold px-6 shadow-md gap-2"
                            @click="router.visit('/marketplace')"
                        >
                            <ShoppingBag class="h-4 w-4" />
                            Mulai Belanja
                        </Button>
                    </CardContent>
                </Card>

            </div>
        </main>

        <!-- Cart Drawer -->
        <CartDrawer
            :isOpen="isCartOpen"
            :items="cartItems"
            @close="isCartOpen = false"
            @update-qty="updateCartQty"
            @remove-item="removeFromCart"
            @checkout="goCheckout"
        />

        <!-- Product Detail Modal -->
        <ProductDetailModal
            :product="activeProductModal"
            @close="activeProductModal = null"
            @add-to-cart="addToCart"
        />
    </StorefrontLayout>
</template>
