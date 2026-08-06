<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    ShoppingCart,
    Heart,
    Search,
    LogIn,
    ReceiptText,
    X,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import { useActiveUser } from '@/lib/useActiveUser';

interface Props {
    cartCount?: number;
    wishlistCount?: number;
    searchQuery?: string;
}

const props = withDefaults(defineProps<Props>(), {
    cartCount: 0,
    wishlistCount: 0,
    searchQuery: '',
});

const emit = defineEmits<{
    (e: 'open-cart'): void;
    (e: 'search', q: string): void;
}>();

const searchInput = ref(props.searchQuery);

const activeUser = useActiveUser();

const userDisplayName = computed(() => {
    return activeUser.value?.displayName ?? activeUser.value?.email ?? '';
});

const userInitial = computed(() => {
    const name = userDisplayName.value;

    return name ? name.substring(0, 2).toUpperCase() : 'B';
});

function handleSearch() {
    emit('search', searchInput.value);
}

function navigate(url: string) {
    if (url) {
        router.visit(url);
    }
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[#f5f4f0] font-sans">
        <!-- ── Top Buyer Header Bar ── -->
        <header
            class="bg-opacity-95 sticky top-0 z-30 border-b border-black/8 bg-white shadow-xs backdrop-blur-md select-none"
        >
            <div
                class="mx-auto flex max-w-[1600px] items-center justify-between gap-4 px-4 py-3 sm:px-6"
            >
                <!-- Brand Logo -->
                <div
                    class="flex shrink-0 cursor-pointer items-center gap-3"
                    @click="navigate('/marketplace')"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-[#e07c28] to-[#c2500a] text-xl font-extrabold text-white shadow-md shadow-[#e07c28]/30"
                    >
                        S
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span
                                class="text-lg leading-none font-extrabold tracking-tight text-[#1c1c22]"
                            >
                                Toko Instan
                            </span>
                            <Badge
                                variant="amber"
                                class="px-1.5 py-0 text-[9px] uppercase"
                            >
                                MARKETPLACE
                            </Badge>
                        </div>
                        <p
                            class="mt-0.5 text-[10px] font-medium text-[#9090a0]"
                        >
                            Platform Belanja Online Terpercaya
                        </p>
                    </div>
                </div>

                <!-- Search Input Bar (Center) -->
                <form
                    @submit.prevent="handleSearch"
                    class="hidden max-w-xl flex-1 items-center gap-2 rounded-2xl border border-black/10 bg-[#f5f4f0] px-4 py-2 shadow-2xs transition-all focus-within:border-[#e07c28] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#e07c28]/20 md:flex"
                >
                    <Search class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    <input
                        v-model="searchInput"
                        placeholder="Cari produk pilihan, baju, elektronik, makanan..."
                        class="flex-1 bg-transparent text-xs text-[#1c1c22] outline-none placeholder:text-[#9090a0]"
                    />
                    <button
                        v-if="searchInput"
                        type="button"
                        @click="
                            searchInput = '';
                            handleSearch();
                        "
                        class="cursor-pointer text-xs text-[#9090a0] hover:text-[#1c1c22]"
                    >
                        <X class="h-3.5 w-3.5" />
                    </button>
                    <Button
                        type="submit"
                        variant="amber"
                        size="sm"
                        class="h-7 rounded-xl px-3 text-[11px] font-bold"
                    >
                        Cari
                    </Button>
                </form>

                <!-- Right Actions: Wishlist, Cart & Auth -->
                <div class="flex shrink-0 items-center gap-3">
                    <!-- My Orders Button -->
                    <Button
                        variant="ghost"
                        size="sm"
                        class="hidden items-center gap-1.5 text-xs font-semibold text-[#4a4a57] hover:text-[#1c1c22] lg:flex"
                        @click="navigate('/orders')"
                    >
                        <ReceiptText class="h-4 w-4 text-[#e07c28]" />
                        <span>Pesanan Saya</span>
                    </Button>

                    <!-- Wishlist Button -->
                    <button
                        title="Wishlist Saya"
                        class="relative flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl border border-black/7 bg-[#f5f4f0] text-[#4a4a57] transition-all hover:bg-black/5"
                    >
                        <Heart class="h-4.5 w-4.5" />
                        <span
                            v-if="wishlistCount > 0"
                            class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-[#e0405a] text-[9px] font-bold text-white"
                        >
                            {{ wishlistCount }}
                        </span>
                    </button>

                    <!-- Cart Drawer Trigger Button -->
                    <button
                        title="Keranjang Belanja"
                        @click="emit('open-cart')"
                        class="relative flex cursor-pointer items-center gap-2 rounded-xl border border-[#e07c2830] bg-[#e07c2815] px-3 py-2 text-xs font-bold text-[#e07c28] shadow-2xs transition-all hover:bg-[#e07c2825]"
                    >
                        <ShoppingCart class="h-4.5 w-4.5" />
                        <span class="hidden sm:inline">Keranjang</span>
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-[#e07c28] text-[10px] font-extrabold text-white shadow-xs"
                        >
                            {{ cartCount }}
                        </span>
                    </button>

                    <!-- User Account / Login State -->
                    <div
                        v-if="activeUser"
                        class="flex items-center gap-2 border-l border-black/10 pl-3"
                    >
                        <Avatar
                            :src="activeUser.photoURL || undefined"
                            :fallback="userInitial"
                            :hue="220"
                            size="md"
                            class="cursor-pointer hover:ring-2 hover:ring-[#e07c28]"
                        />
                    </div>
                    <div
                        v-else
                        class="flex items-center gap-2 border-l border-black/10 pl-3"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            class="text-xs font-bold"
                            @click="navigate('/login')"
                        >
                            <LogIn class="mr-1 h-3.5 w-3.5" /> Masuk
                        </Button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Buyer Page Slot -->
        <div class="flex-1">
            <slot />
        </div>

        <!-- Footer -->
        <footer
            class="mt-12 border-t border-black/8 bg-white py-8 text-center text-xs text-[#9090a0]"
        >
            <div
                class="mx-auto flex max-w-[1600px] flex-col items-center justify-between gap-4 px-6 md:flex-row"
            >
                <div class="flex items-center gap-2">
                    <span class="font-bold text-[#1c1c22]"
                        >Toko Instan SaaS</span
                    >
                    <span>· Multi-tenant E-Commerce Platform</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:underline">Tentang Kami</a>
                    <a href="#" class="hover:underline">Syarat & Ketentuan</a>
                    <a href="#" class="hover:underline">Bantuan Pembeli</a>
                </div>
            </div>
        </footer>

        <Toaster richColors position="top-right" />
    </div>
</template>
