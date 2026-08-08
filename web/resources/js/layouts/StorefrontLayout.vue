<script setup lang="ts">
import { router, usePage as useInertiaPage } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import {
    ShoppingCart,
    Heart,
    Search,
    LogIn,
    ReceiptText,
    X,
    Home,
    LayoutGrid,
    ClipboardList,
    UserCircle2,
    ShoppingBag,
} from 'lucide-vue-next';
import { LogOut, Store, Users as UsersIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Toaster } from '@/components/ui/sonner';
import { logoutUser } from '@/lib/firebase';
import { useActiveUser } from '@/lib/useActiveUser';
import { useCart } from '@/lib/useCart';
import { useWishlist } from '@/lib/useWishlist';

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

const { totalCount: dynamicCartCount } = useCart();
const effectiveCartCount = computed(
    () => props.cartCount || dynamicCartCount.value,
);

const { count: dynamicWishlistCount } = useWishlist();
const effectiveWishlistCount = computed(
    () => props.wishlistCount || dynamicWishlistCount.value,
);

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
    router.get(
        '/marketplace',
        { search: searchInput.value },
        { preserveState: true, preserveScroll: true },
    );
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

// Bottom nav active route detection
const currentPage = useInertiaPage();
const currentPath = computed(() => (currentPage.url as string).split('?')[0]);

const bottomNavItems = [
    {
        label: 'Beranda',
        icon: Home,
        href: '/marketplace',
        match: '/marketplace',
    },
    {
        label: 'Kategori',
        icon: LayoutGrid,
        href: '/marketplace',
        match: '__kategori',
    },
    { label: 'Keranjang', icon: ShoppingBag, href: null, match: '__cart' },
    {
        label: 'Pesanan',
        icon: ClipboardList,
        href: '/orders',
        match: '/orders',
    },
    { label: 'Akun', icon: UserCircle2, href: '/account', match: '/account' },
];
</script>

<template>
    <div
        class="flex min-h-screen flex-col overflow-x-clip bg-[#f5f4f0] font-sans"
    >
        <!-- ── Top Buyer Header Bar ── -->
        <header
            class="sticky top-0 z-50 border-b border-black/8 bg-white/95 shadow-xs backdrop-blur-md select-none"
        >
            <div
                class="mx-auto flex max-w-[1600px] items-center gap-3 px-3 py-2.5 sm:gap-4 sm:px-6 sm:py-3"
            >
                <!-- Brand Logo -->
                <div
                    class="flex shrink-0 cursor-pointer items-center gap-2.5"
                    @click="navigate('/marketplace')"
                >
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-2xl bg-black text-lg font-black text-white shadow-md shadow-black/20 sm:h-10 sm:w-10 sm:text-xl"
                    >
                        N
                    </div>
                    <!-- Hide full name on very small screens -->
                    <div class="hidden sm:block">
                        <div class="flex items-center gap-1.5">
                            <span
                                class="text-base leading-none font-black tracking-wider text-[#1c1c22] uppercase sm:text-lg"
                            >
                                NIKE
                            </span>
                            <Badge
                                variant="amber"
                                class="border-none bg-emerald-600 px-1.5 py-0 text-[9px] font-black text-white uppercase"
                            >
                                OFFICIAL STORE
                            </Badge>
                        </div>
                        <p class="mt-0.5 text-[10px] font-bold text-[#9090a0]">
                            100% Original Guaranteed
                        </p>
                    </div>
                    <!-- Short name on mobile -->
                    <div class="flex items-center gap-1 sm:hidden">
                        <span
                            class="text-base font-black tracking-wider text-[#1c1c22] uppercase"
                            >NIKE</span
                        >
                        <Badge
                            class="bg-emerald-600 px-1 py-0 text-[8px] font-black text-white"
                            >OFFICIAL</Badge
                        >
                    </div>
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
                        class="flex-1 border-none bg-transparent text-xs shadow-none outline-none placeholder:text-[#9090a0] focus-visible:ring-0"
                    />
                    <button
                        v-if="searchInput"
                        type="button"
                        @click="clearSearch"
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
                        @click="navigate('/wishlist')"
                        class="relative hidden h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-black/7 bg-[#f5f4f0] text-[#4a4a57] transition-all hover:bg-black/5 sm:flex"
                    >
                        <Heart
                            class="h-4 w-4"
                            :class="
                                effectiveWishlistCount > 0
                                    ? 'fill-[#e0405a] text-[#e0405a]'
                                    : ''
                            "
                        />
                        <span
                            v-if="effectiveWishlistCount > 0"
                            class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-white bg-[#e0405a] text-[9px] font-bold text-white shadow-xs"
                        >
                            {{ effectiveWishlistCount }}
                        </span>
                    </button>

                    <!-- Cart Button -->
                    <button
                        title="Keranjang Belanja"
                        @click="emit('open-cart')"
                        class="relative flex min-h-[44px] cursor-pointer touch-manipulation items-center gap-1.5 rounded-xl border border-[#e07c2830] bg-[#e07c2815] px-2.5 py-2 text-xs font-bold text-[#e07c28] shadow-2xs transition-all hover:bg-[#e07c2825] active:scale-95 sm:gap-2 sm:px-3"
                    >
                        <ShoppingCart class="h-4 w-4" />
                        <span class="hidden sm:inline">Keranjang</span>
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full bg-[#e07c28] text-[10px] font-extrabold text-white shadow-xs"
                        >
                            {{ cartCount }}
                        </span>
                    </button>

                    <!-- User Dropdown Menu with Logout -->
                    <div
                        v-if="activeUser"
                        class="border-l border-black/10 pl-2 sm:pl-3"
                    >
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Avatar
                                    :src="activeUser.photoURL || undefined"
                                    :fallback="userInitial"
                                    :hue="220"
                                    size="sm"
                                    class="cursor-pointer transition-all hover:ring-2 hover:ring-[#e07c28]"
                                />
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="w-52" align="end">
                                <DropdownMenuLabel class="font-normal">
                                    <div class="flex flex-col space-y-1">
                                        <p
                                            class="text-xs leading-none font-bold text-[#1c1c22]"
                                        >
                                            {{ userDisplayName }}
                                        </p>
                                        <p
                                            class="max-w-[180px] truncate text-[10px] leading-none text-[#9090a0]"
                                        >
                                            {{ activeUser.email }}
                                        </p>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="navigate('/account')">
                                    <UserCircle2 class="mr-2 h-3.5 w-3.5" />
                                    <span>Akun Saya</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="navigate('/orders')">
                                    <ReceiptText class="mr-2 h-3.5 w-3.5" />
                                    <span>Pesanan Saya</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="
                                        userRole === 'seller' ||
                                        userRole === 'admin'
                                    "
                                    @click="navigate('/dashboard')"
                                >
                                    <Store class="mr-2 h-3.5 w-3.5" />
                                    <span>Dashboard Seller</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="userRole === 'admin'"
                                    @click="navigate('/admin')"
                                >
                                    <UsersIcon class="mr-2 h-3.5 w-3.5" />
                                    <span>Dashboard Admin</span>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                    @click="handleLogout"
                                    class="text-red-600 focus:text-red-600"
                                >
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
                <div
                    v-if="mobileSearchOpen"
                    class="border-t border-black/5 bg-white px-3 py-2.5 md:hidden"
                >
                    <form
                        @submit.prevent="handleSearch"
                        class="flex items-center gap-2 rounded-xl border border-black/10 bg-[#f5f4f0] px-3 py-2 focus-within:border-[#e07c28] focus-within:bg-white"
                    >
                        <Search class="h-4 w-4 shrink-0 text-[#9090a0]" />
                        <Input
                            v-model="searchInput"
                            placeholder="Cari produk..."
                            autofocus
                            class="flex-1 border-none bg-transparent p-0 text-sm shadow-none focus-visible:ring-0"
                        />
                        <button
                            v-if="searchInput"
                            type="button"
                            @click="clearSearch"
                            class="text-[#9090a0]"
                        >
                            <X class="h-4 w-4" />
                        </button>
                        <Button
                            type="submit"
                            variant="amber"
                            size="sm"
                            class="h-7 px-3 text-xs font-bold"
                        >
                            Cari
                        </Button>
                    </form>
                </div>
            </Transition>
        </header>

        <!-- ── Slot Content ── -->
        <div class="flex-1">
            <slot />
        </div>

        <!-- ── Standard Buyer Footer ── -->
        <footer
            class="border-t border-black/8 bg-white py-6 text-xs text-[#9090a0]"
        >
            <div
                class="mx-auto flex max-w-[1600px] flex-col items-center justify-between gap-3 px-4 sm:flex-row sm:px-6"
            >
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-[#1c1c22]"
                        >Nike Official Store</span
                    >
                    <span>· 100% Original Guaranteed</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:underline">Tentang Kami</a>
                    <a href="#" class="hover:underline">Syarat & Ketentuan</a>
                    <a href="#" class="hover:underline">Bantuan Pembeli</a>
                </div>
            </div>
        </footer>

        <!-- ── Mobile Bottom Navigation ── -->
        <nav
            class="fixed right-0 bottom-0 left-0 z-50 border-t border-black/8 bg-white/95 backdrop-blur-md md:hidden"
            style="padding-bottom: env(safe-area-inset-bottom, 0px)"
        >
            <div class="flex items-stretch">
                <button
                    v-for="item in bottomNavItems"
                    :key="item.label"
                    @click="
                        item.match === '__cart'
                            ? emit('open-cart')
                            : navigate(item.href!)
                    "
                    class="group relative flex flex-1 touch-manipulation flex-col items-center justify-center gap-1 py-2.5 transition-colors active:scale-95"
                    :class="
                        (item.match !== '__cart' &&
                            currentPath === item.match) ||
                        (item.match === '__store' &&
                            currentPath.startsWith('/store/'))
                            ? 'font-extrabold text-black'
                            : 'text-[#9090a0] hover:text-[#4a4a57]'
                    "
                >
                    <!-- Cart badge -->
                    <div v-if="item.match === '__cart'" class="relative">
                        <component :is="item.icon" class="h-5 w-5" />
                        <span
                            v-if="effectiveCartCount > 0"
                            class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-black text-[9px] font-black text-amber-400 shadow-sm"
                            >{{
                                effectiveCartCount > 9
                                    ? '9+'
                                    : effectiveCartCount
                            }}</span
                        >
                    </div>
                    <component v-else :is="item.icon" class="h-5 w-5" />

                    <span
                        class="text-[9px] leading-none"
                        :class="
                            item.match !== '__cart' &&
                            currentPath === item.match
                                ? 'font-black text-black'
                                : 'font-semibold'
                        "
                        >{{ item.label }}</span
                    >

                    <!-- Active indicator line -->
                    <span
                        v-if="
                            (item.match !== '__cart' &&
                                currentPath === item.match) ||
                            (item.match === '__store' &&
                                currentPath.startsWith('/store/'))
                        "
                        class="absolute bottom-0 left-1/2 h-0.5 w-6 -translate-x-1/2 rounded-full bg-black"
                    />
                </button>
            </div>
        </nav>

        <Toaster richColors position="top-right" />
    </div>
</template>
