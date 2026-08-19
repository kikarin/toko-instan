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
import CartDrawer from '@/components/marketplace/CartDrawer.vue';
import StoreChatWidget from '@/components/StoreChatWidget.vue';
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
import { useActiveUser } from '@/composables/useActiveUser';
import { useCart } from '@/composables/useCart';
import { useStoreTheme } from '@/composables/useStoreTheme';
import { useWishlist } from '@/composables/useWishlist';
import { logoutUser } from '@/lib/firebase';

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

useStoreTheme();

const emit = defineEmits<{
    (e: 'open-cart'): void;
    (e: 'search', q: string): void;
}>();

const { 
    totalCount: dynamicCartCount, 
    items: cartItems,
    isOpen: isCartOpen,
    openCart,
    closeCart,
    updateQty: updateCartQty,
    removeItem: removeFromCart 
} = useCart();
const effectiveCartCount = computed(
    () => props.cartCount || dynamicCartCount.value,
);

function goCheckout() {
    closeCart();
    router.visit(`/${storeData.value?.slug ?? ''}/checkout`);
}

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
        `/${storeData.value?.slug ?? ''}`,
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
const storeData = computed(() => page.props.store as any);

const userRole = computed(() => {
    return (page.props.auth as any)?.user?.role ?? 'buyer';
});

async function handleLogout() {
    await logoutUser();
    router.post('/logout', { store_slug: storeData.value?.slug });
}

// Bottom nav active route detection
const currentPage = useInertiaPage();
const currentPath = computed(() => (currentPage.url as string).split('?')[0]);

const bottomNavItems = computed(() => [
    {
        label: 'Beranda',
        icon: Home,
        href: `/${storeData.value?.slug ?? ''}`,
        match: `/${storeData.value?.slug ?? ''}`,
    },
    {
        label: 'Kategori',
        icon: LayoutGrid,
        href: `/${storeData.value?.slug ?? ''}`,
        match: '__kategori',
    },
    { label: 'Keranjang', icon: ShoppingBag, href: null, match: '__cart' },
    {
        label: 'Pesanan',
        icon: ClipboardList,
        href: storeData.value?.slug ? `/${storeData.value.slug}/orders` : '/orders',
        match: '/orders',
    },
    { label: 'Akun', icon: UserCircle2, href: storeData.value?.slug ? `/${storeData.value.slug}/account` : '/account', match: '/account' },
]);
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col overflow-x-clip font-sans bg-background"
    >
        <!-- ── Top Buyer Header Bar ── -->
        <header
            class="sticky top-0 z-50 border-b border-border bg-[var(--header)] text-[var(--header-foreground)] shadow-md select-none"
        >
            <div
                class="mx-auto flex max-w-[1600px] items-center gap-3 px-3 py-2.5 sm:gap-4 sm:px-6 sm:py-3"
            >
                <!-- Brand Logo -->
                <div
                    class="flex shrink-0 cursor-pointer items-center gap-2.5"
                    @click="navigate(`/${storeData?.slug ?? ''}`)"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl text-lg font-black shadow-md shadow-black/20 sm:h-10 sm:w-10 sm:text-xl bg-[var(--header-foreground)] text-[var(--header)]"
                    >
                        {{ storeData?.name ? storeData.name.charAt(0).toUpperCase() : 'S' }}
                    </div>
                    <!-- Hide full name on very small screens -->
                    <div class="hidden sm:block">
                        <div class="flex items-center gap-1.5">
                            <span
                                class="text-base leading-none font-black tracking-wider uppercase sm:text-lg"
                            >
                                {{ storeData?.name ?? 'Store' }}
                            </span>
                            <Badge
                                class="border-none px-1.5 py-0 text-[9px] font-black uppercase shadow-xs bg-accent text-accent-foreground"
                            >
                                OFFICIAL STORE
                            </Badge>
                        </div>
                        <p class="mt-0.5 text-[10px] font-bold opacity-80">
                            100% Original Guaranteed
                        </p>
                    </div>
                    <!-- Short name on mobile -->
                    <div class="flex items-center gap-1 sm:hidden">
                        <span
                            class="text-base font-black tracking-wider uppercase"
                            >{{ storeData?.name ?? 'Store' }}</span
                        >
                        <Badge
                            class="px-1 py-0 text-[8px] font-black bg-accent text-accent-foreground"
                            >OFFICIAL</Badge
                        >
                    </div>
                </div>

                <!-- Search Bar — Desktop (center) -->
                <form
                    @submit.prevent="handleSearch"
                    class="hidden max-w-xl flex-1 items-center gap-2 rounded-2xl border border-transparent bg-[var(--header-foreground)]/10 px-4 py-2 shadow-inner transition-all focus-within:bg-[var(--header-foreground)]/20 focus-within:ring-2 focus-within:ring-[var(--header-foreground)]/20 md:flex"
                >
                    <Search class="h-4 w-4 shrink-0 opacity-70" />
                    <Input
                        v-model="searchInput"
                        placeholder="Cari produk pilihan, baju, elektronik, makanan..."
                        class="flex-1 border-none bg-transparent text-xs shadow-none outline-none placeholder:opacity-70 focus-visible:ring-0 text-inherit"
                    />
                    <button
                        v-if="searchInput"
                        type="button"
                        @click="clearSearch"
                        class="cursor-pointer text-xs opacity-70 hover:opacity-100"
                    >
                        <X class="h-3.5 w-3.5" />
                    </button>
                    <Button
                        type="submit"
                        size="sm"
                        class="h-7 rounded-xl px-3 text-[11px] font-bold bg-secondary text-secondary-foreground hover:bg-secondary/90 shadow-xs border-0"
                    >
                        Cari
                    </Button>
                </form>
                <button
                    v-if="storeData?.slug"
                    type="button"
                    class="hidden shrink-0 text-xs font-bold uppercase tracking-wide opacity-90 hover:opacity-100 md:inline"
                    @click="navigate(`/${storeData.slug}/blog`)"
                >
                    Blog
                </button>

                <!-- Right Actions -->
                <div class="ml-auto flex shrink-0 items-center gap-2">
                    <!-- Search toggle — Mobile only -->
                    <button
                        title="Cari produk"
                        @click="mobileSearchOpen = !mobileSearchOpen"
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl bg-[var(--header-foreground)]/10 transition-all hover:bg-[var(--header-foreground)]/20 md:hidden"
                    >
                        <Search class="h-4 w-4" />
                    </button>

                    <!-- My Orders — Desktop only -->
                    <Button
                        variant="ghost"
                        size="sm"
                        class="hidden items-center gap-1.5 text-xs font-semibold hover:bg-[var(--header-foreground)]/10 hover:text-[var(--header-foreground)] opacity-90 hover:opacity-100 lg:flex"
                        @click="navigate(`/${storeData?.slug ?? ''}/orders`)"
                    >
                        <ReceiptText
                            class="h-4 w-4"
                        />
                        <span>Pesanan Saya</span>
                    </Button>

                    <!-- Wishlist — hide on mobile -->
                    <button
                        title="Wishlist Saya"
                        @click="navigate(`/${storeData?.slug ?? ''}/wishlist`)"
                        class="relative hidden h-9 w-9 cursor-pointer items-center justify-center rounded-xl bg-[var(--header-foreground)]/10 transition-all hover:bg-[var(--header-foreground)]/20 sm:flex"
                    >
                        <Heart
                            class="h-4 w-4"
                            :class="
                                effectiveWishlistCount > 0
                                    ? 'fill-accent text-accent'
                                    : ''
                            "
                        />
                        <span
                            v-if="effectiveWishlistCount > 0"
                            class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full border-2 border-[var(--header)] text-[9px] font-black shadow-xs bg-accent text-accent-foreground"
                        >
                            {{ effectiveWishlistCount }}
                        </span>
                    </button>

                    <!-- Cart Button -->
                    <button
                        title="Keranjang Belanja"
                        @click="openCart()"
                        class="relative flex min-h-[44px] cursor-pointer touch-manipulation items-center gap-1.5 rounded-xl bg-[var(--header-foreground)] px-2.5 py-2 text-xs font-bold text-[var(--header)] shadow-sm transition-all hover:brightness-95 active:scale-95 sm:gap-2 sm:px-3"
                    >
                        <ShoppingCart class="h-4 w-4" />
                        <span class="hidden sm:inline">Keranjang</span>
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-black bg-destructive text-destructive-foreground shadow-xs"
                        >
                            {{ effectiveCartCount }}
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
                                    class="cursor-pointer transition-all ring-2 ring-transparent hover:ring-[var(--header-foreground)]/50"
                                />
                            </DropdownMenuTrigger>
                            <DropdownMenuContent class="w-52" align="end">
                                <DropdownMenuLabel class="font-normal">
                                    <div class="flex flex-col space-y-1">
                                        <p
                                            class="text-xs leading-none font-bold text-foreground"
                                        >
                                            {{ userDisplayName }}
                                        </p>
                                        <p
                                            class="max-w-[180px] truncate text-[10px] leading-none text-muted-foreground"
                                        >
                                            {{ activeUser.email }}
                                        </p>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="navigate(`/${storeData?.slug ?? ''}/account`)">
                                    <UserCircle2 class="mr-2 h-3.5 w-3.5" />
                                    <span>Akun Saya</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="navigate(`/${storeData?.slug ?? ''}/orders`)">
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
                                    class="text-destructive focus:text-destructive"
                                >
                                    <LogOut class="mr-2 h-3.5 w-3.5" />
                                    <span>Keluar</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div v-else class="border-l border-[var(--header-foreground)]/20 pl-2 sm:pl-3">
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 text-xs font-bold border-none bg-[var(--header-foreground)] text-[var(--header)] hover:brightness-95 hover:bg-[var(--header-foreground)]"
                            @click="navigate(`/${storeData?.slug ?? ''}/login`)"
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
                    class="border-t border-border bg-card px-3 py-2.5 md:hidden"
                >
                    <form
                        @submit.prevent="handleSearch"
                        class="flex items-center gap-2 rounded-xl border border-border bg-background px-3 py-2 focus-within:border-primary focus-within:bg-card"
                    >
                        <Search class="h-4 w-4 shrink-0 text-muted-foreground" />
                        <Input
                            v-model="searchInput"
                            placeholder="Cari produk..."
                            autofocus
                            class="flex-1 border-none bg-transparent p-0 text-sm shadow-none focus-visible:ring-0 placeholder:text-muted-foreground"
                        />
                        <button
                            v-if="searchInput"
                            type="button"
                            @click="clearSearch"
                            class="text-muted-foreground"
                        >
                            <X class="h-4 w-4" />
                        </button>
                        <Button
                            type="submit"
                            size="sm"
                            class="h-7 px-3 text-xs font-bold bg-primary hover:bg-primary/90 border-0 text-primary-foreground"
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
            class="bg-[var(--footer)] py-8 text-xs text-[var(--footer-foreground)]/70 shadow-inner"
        >
            <div
                class="mx-auto flex max-w-[1600px] flex-col items-center justify-between gap-3 px-4 sm:flex-row sm:px-6"
            >
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-[var(--footer-foreground)]"
                        >{{ storeData?.name ?? 'Official Store' }}</span
                    >
                    <span>· 100% Original Guaranteed</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-[var(--footer-foreground)] transition-colors">Tentang Kami</a>
                    <a href="#" class="hover:text-[var(--footer-foreground)] transition-colors">Syarat & Ketentuan</a>
                    <a href="#" class="hover:text-[var(--footer-foreground)] transition-colors">Bantuan Pembeli</a>
                </div>
            </div>
        </footer>

        <!-- ── Dynamic 4-Hex Theme Mobile Bottom Navigation ── -->
        <nav
            class="fixed right-0 bottom-0 left-0 z-50 border-t border-border bg-card/95 backdrop-blur-md md:hidden"
            style="padding-bottom: env(safe-area-inset-bottom, 0px)"
        >
            <!-- Accent line top of mobile bottom nav -->
            <div
                class="h-0.5 w-full bg-primary"
            />
            <div class="flex items-stretch">
                <button
                    v-for="item in bottomNavItems"
                    :key="item.label"
                    @click="
                        item.match === '__cart'
                            ? openCart()
                            : navigate(item.href!)
                    "
                    class="group relative flex flex-1 touch-manipulation flex-col items-center justify-center gap-1 py-2.5 transition-all active:scale-95"
                    :class="(item.match !== '__cart' && currentPath === item.match) || (item.match === '__store' && currentPath.startsWith('/store/')) ? 'text-primary' : 'text-muted-foreground'"
                >
                    <!-- Cart badge -->
                    <div v-if="item.match === '__cart'" class="relative">
                        <component :is="item.icon" class="h-5 w-5" />
                        <span
                            v-if="effectiveCartCount > 0"
                            class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full text-[9px] font-black text-destructive-foreground shadow-sm bg-destructive"
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
                                ? 'font-black'
                                : 'font-semibold'
                        "
                        >{{ item.label }}</span
                    >

                    <span
                        v-if="
                            (item.match !== '__cart' &&
                                currentPath === item.match) ||
                            (item.match === '__store' &&
                                currentPath.startsWith('/store/'))
                        "
                        class="absolute bottom-0 left-1/2 h-1 w-6 -translate-x-1/2 rounded-full shadow-xs bg-primary"
                    />
                </button>
            </div>
        </nav>

        <Toaster richColors position="top-right" />

        <StoreChatWidget v-if="storeData?.slug" :store-slug="storeData.slug" />
        
        <!-- Cart Drawer -->
        <CartDrawer
            :isOpen="isCartOpen"
            :items="cartItems"
            @close="closeCart()"
            @update-qty="updateCartQty"
            @remove-item="removeFromCart"
            @checkout="goCheckout"
        />
    </div>
</template>
