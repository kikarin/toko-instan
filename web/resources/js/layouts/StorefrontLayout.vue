<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    ShoppingCart,
    Heart,
    Search,
    LogIn,
    ReceiptText,
    X,
    Menu,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Toaster } from '@/components/ui/sonner';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { logoutUser } from '@/lib/firebase';
import { usePage } from '@inertiajs/vue3';
import { useActiveUser } from '@/lib/useActiveUser';
import { LogOut, Store, Users as UsersIcon } from 'lucide-vue-next';

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
const mobileSearchOpen = ref(false);

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
    mobileSearchOpen.value = false;
}

function clearSearch() {
    searchInput.value = '';
    handleSearch();
}

function navigate(url: string) {
    if (url) {
        router.visit(url);
    }
}

const page = usePage();
const userRole = computed(() => {
    return (page.props.auth as any)?.user?.role ?? 'buyer';
});

async function handleLogout() {
    await logoutUser();
    router.post('/logout');
}
</script>

<template>
    <div class="flex min-h-screen flex-col overflow-x-hidden bg-[#f5f4f0] font-sans">
        <!-- ── Top Buyer Header Bar ── -->
        <header class="sticky top-0 z-30 border-b border-black/8 bg-white shadow-xs backdrop-blur-md select-none">
            <div class="mx-auto flex max-w-[1600px] items-center gap-3 px-3 py-2.5 sm:gap-4 sm:px-6 sm:py-3">

                <!-- Brand Logo -->
                <div
                    class="flex shrink-0 cursor-pointer items-center gap-2"
                    @click="navigate('/marketplace')"
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-[#e07c28] to-[#c2500a] text-lg font-extrabold text-white shadow-md shadow-[#e07c28]/30 sm:h-10 sm:w-10 sm:text-xl">
                        S
                    </div>
                    <!-- Hide full name on very small screens -->
                    <div class="hidden sm:block">
                        <div class="flex items-center gap-1.5">
                            <span class="text-base leading-none font-extrabold tracking-tight text-[#1c1c22] sm:text-lg">
                                Toko Instan
                            </span>
                            <Badge variant="amber" class="px-1.5 py-0 text-[9px] uppercase">
                                MARKETPLACE
                            </Badge>
                        </div>
                        <p class="mt-0.5 text-[10px] font-medium text-[#9090a0]">
                            Platform Belanja Online Terpercaya
                        </p>
                    </div>
                    <!-- Short name on mobile -->
                    <span class="text-base font-extrabold text-[#1c1c22] sm:hidden">Toko Instan</span>
                </div>

                <!-- Search Bar — Desktop (center) -->
                <form
                    @submit.prevent="handleSearch"
                    class="hidden max-w-xl flex-1 items-center gap-2 rounded-2xl border border-black/10 bg-[#f5f4f0] px-4 py-2 shadow-2xs transition-all focus-within:border-[#e07c28] focus-within:bg-white focus-within:ring-2 focus-within:ring-[#e07c28]/20 md:flex"
                >
                    <Search class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    <Input
                        v-model="searchInput"
                        placeholder="Cari produk pilihan, baju, elektronik, makanan..."
                        class="flex-1 border-none bg-transparent text-xs shadow-none outline-none focus-visible:ring-0 placeholder:text-[#9090a0]"
                    />
                    <button
                        v-if="searchInput"
                        type="button"
                        @click="clearSearch"
                        class="cursor-pointer text-xs text-[#9090a0] hover:text-[#1c1c22]"
                    >
                        <X class="h-3.5 w-3.5" />
                    </button>
                    <Button type="submit" variant="amber" size="sm" class="h-7 rounded-xl px-3 text-[11px] font-bold">
                        Cari
                    </Button>
                </form>

                <!-- Right Actions -->
                <div class="ml-auto flex shrink-0 items-center gap-2">
                    <!-- Search toggle — Mobile only -->
                    <button
                        title="Cari produk"
                        @click="mobileSearchOpen = !mobileSearchOpen"
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-black/8 bg-[#f5f4f0] text-[#4a4a57] transition-all hover:bg-black/5 md:hidden"
                    >
                        <Search class="h-4 w-4" />
                    </button>

                    <!-- My Orders — Desktop only -->
                    <Button
                        variant="ghost"
                        size="sm"
                        class="hidden items-center gap-1.5 text-xs font-semibold text-[#4a4a57] hover:text-[#1c1c22] lg:flex"
                        @click="navigate('/orders')"
                    >
                        <ReceiptText class="h-4 w-4 text-[#e07c28]" />
                        <span>Pesanan Saya</span>
                    </Button>

                    <!-- Wishlist — hide on mobile -->
                    <button
                        title="Wishlist Saya"
                        class="relative hidden h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-black/7 bg-[#f5f4f0] text-[#4a4a57] transition-all hover:bg-black/5 sm:flex"
                    >
                        <Heart class="h-4 w-4" />
                        <span
                            v-if="wishlistCount > 0"
                            class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-[#e0405a] text-[9px] font-bold text-white"
                        >
                            {{ wishlistCount }}
                        </span>
                    </button>

                    <!-- Cart Button -->
                    <button
                        title="Keranjang Belanja"
                        @click="emit('open-cart')"
                        class="relative flex cursor-pointer items-center gap-1.5 rounded-xl border border-[#e07c2830] bg-[#e07c2815] px-2.5 py-2 text-xs font-bold text-[#e07c28] shadow-2xs transition-all hover:bg-[#e07c2825] sm:gap-2 sm:px-3"
                    >
                        <ShoppingCart class="h-4 w-4" />
                        <span class="hidden sm:inline">Keranjang</span>
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#e07c28] text-[10px] font-extrabold text-white shadow-xs">
                            {{ cartCount }}
                        </span>
                    </button>

                    <!-- User Dropdown Menu with Logout -->
                    <div v-if="activeUser" class="border-l border-black/10 pl-2 sm:pl-3">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Avatar
                                    :src="activeUser.photoURL || undefined"
                                    :fallback="userInitial"
                                    :hue="220"
                                    size="sm"
                                    class="cursor-pointer hover:ring-2 hover:ring-[#e07c28] transition-all"
                                />
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="w-52" align="end">
                                <DropdownMenuLabel class="font-normal">
                                    <div class="flex flex-col space-y-1">
                                        <p class="text-xs font-bold leading-none text-[#1c1c22]">
                                            {{ userDisplayName }}
                                        </p>
                                        <p class="text-[10px] leading-none text-[#9090a0] truncate max-w-[180px]">
                                            {{ activeUser.email }}
                                        </p>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="navigate('/orders')">
                                    <ReceiptText class="mr-2 h-3.5 w-3.5" />
                                    <span>Pesanan Saya</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="userRole === 'seller' || userRole === 'admin'"
                                    @click="navigate('/')"
                                >
                                    <Store class="mr-2 h-3.5 w-3.5" />
                                    <span>Dashboard Seller</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="userRole === 'admin'"
                                    @click="navigate('/admin/users')"
                                >
                                    <UsersIcon class="mr-2 h-3.5 w-3.5" />
                                    <span>Dashboard Admin</span>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="handleLogout" class="text-red-600 focus:text-red-600">
                                    <LogOut class="mr-2 h-3.5 w-3.5" />
                                    <span>Keluar</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div v-else class="border-l border-black/10 pl-2 sm:pl-3">
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs font-bold"
                            @click="navigate('/login')"
                        >
                            <LogIn class="mr-1 h-3.5 w-3.5" /> Masuk
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Mobile Search Expandable Bar -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="mobileSearchOpen" class="border-t border-black/5 bg-white px-3 py-2.5 md:hidden">
                    <form @submit.prevent="handleSearch" class="flex items-center gap-2 rounded-xl border border-black/10 bg-[#f5f4f0] px-3 py-2 focus-within:border-[#e07c28] focus-within:bg-white">
                        <Search class="h-4 w-4 shrink-0 text-[#9090a0]" />
                        <Input
                            v-model="searchInput"
                            placeholder="Cari produk..."
                            autofocus
                            class="flex-1 border-none bg-transparent p-0 text-sm shadow-none focus-visible:ring-0"
                        />
                        <button v-if="searchInput" type="button" @click="clearSearch" class="text-[#9090a0]">
                            <X class="h-4 w-4" />
                        </button>
                        <Button type="submit" variant="amber" size="sm" class="h-7 px-3 text-xs font-bold">
                            Cari
                        </Button>
                    </form>
                </div>
            </Transition>
        </header>

        <!-- Main Buyer Page Slot -->
        <div class="flex-1">
            <slot />
        </div>

        <!-- Footer -->
        <footer class="mt-12 border-t border-black/8 bg-white py-6 text-center text-xs text-[#9090a0] sm:py-8">
            <div class="mx-auto flex max-w-[1600px] flex-col items-center justify-between gap-3 px-4 sm:gap-4 sm:px-6 md:flex-row">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-[#1c1c22]">Toko Instan SaaS</span>
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
