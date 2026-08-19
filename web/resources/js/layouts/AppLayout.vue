<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Package,
    ShoppingCart,
    Users,
    Wallet,
    Ticket,
    Gift,
    CircleHelp,
    LifeBuoy,
    Globe,
    Newspaper,
    MessageCircle,
    KeyRound,
    BookOpen,
    Crown,
    BarChart3,
    FileSpreadsheet,
    LogOut,
    ChevronRight,
    ChevronsUpDown,
    Store,
    Bell,
    X,
    Settings,
    Tags,
    Boxes,
    Palette,
    ScrollText,
    ExternalLink,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    SidebarProvider,
    SidebarRail,
    SidebarSeparator,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { Toaster } from '@/components/ui/sonner';
import { useActiveUser } from '@/composables/useActiveUser';
import { useStoreName } from '@/composables/useStoreName';
import { useStoreTheme } from '@/composables/useStoreTheme';
import { useSellerNotifications } from '@/composables/useSellerNotifications';
import { logoutUser } from '@/lib/firebase';

interface NavItem {
    icon: any;
    label: string;
    route: string;
    badge?: string | number;
    children?: { label: string; route: string }[];
}

interface Props {
    title?: string;
    activePage?:
        | 'Dashboard'
        | 'Katalog'
        | 'Produk'
        | 'Stok & Inventory'
        | 'Pesanan'
        | 'Pengaturan Toko'
        | 'Pelanggan'
        | 'Dompet'
        | 'Langganan'
        | 'Voucher'
        | 'Analitik'
        | 'Tampilan & Konten'
        | 'Riwayat Aktivitas'
        | 'Laporan Pajak';
    period?: 'Hari' | 'Minggu' | 'Bulan';
}

const props = withDefaults(defineProps<Props>(), {
    activePage: 'Dashboard',
    period: 'Bulan',
});

const emit = defineEmits<{
    (e: 'update:period', val: 'Hari' | 'Minggu' | 'Bulan'): void;
}>();

const activeUser = useActiveUser();

useStoreTheme();

const { storeName } = useStoreName();
const notices = useSellerNotifications();
const noticeRoot = ref<HTMLElement | null>(null);
onClickOutside(noticeRoot, () => notices.close());

const activeNavClass =
    'bg-sidebar-accent text-sidebar-accent-foreground font-black border border-sidebar-border shadow-md shadow-black/10 group-data-[collapsible=icon]:bg-sidebar-primary group-data-[collapsible=icon]:text-sidebar-primary-foreground group-data-[collapsible=icon]:border-none';

const userDisplayName = computed(() => {
    return (
        activeUser.value?.displayName ??
        activeUser.value?.email ??
        storeName.value
    );
});

const userInitial = computed(() => {
    const name = userDisplayName.value;

    return name ? name.substring(0, 2).toUpperCase() : 'NK';
});

const userEmail = computed(() => {
    return activeUser.value?.email ?? 'seller@toko.com';
});

// Nav groups
const mainNavItems: NavItem[] = [
    { icon: LayoutDashboard, label: 'Dashboard', route: '/dashboard' },
    { icon: Tags, label: 'Katalog', route: '/catalog' },
    { icon: Package, label: 'Produk', route: '/products' },
    { icon: Boxes, label: 'Stok & Inventory', route: '/inventory' },
    {
        icon: ShoppingCart,
        label: 'Pesanan',
        route: '/orders',
        badge: 3,
    },
    { icon: Settings, label: 'Pengaturan Toko', route: '/store-settings' },
    { icon: Palette, label: 'Tampilan & Konten', route: '/store-cms' },
    { icon: ScrollText, label: 'Riwayat Aktivitas', route: '/activity-log' },
    { icon: Users, label: 'Pelanggan', route: '/customers' },
];

const financeNavItems: NavItem[] = [
    { icon: Wallet, label: 'Dompet', route: '/wallet' },
    { icon: Crown, label: 'Langganan', route: '/subscription' },
    { icon: Ticket, label: 'Voucher', route: '/vouchers' },
    { icon: Gift, label: 'Referral', route: '/referral' },
    { icon: Newspaper, label: 'Blog', route: '/blog' },
    { icon: CircleHelp, label: 'FAQ', route: '/faqs' },
    { icon: LifeBuoy, label: 'Tiket', route: '/support' },
    { icon: BookOpen, label: 'Bantuan', route: '/help' },
    { icon: Globe, label: 'Domain', route: '/store-domain' },
    { icon: MessageCircle, label: 'Chat', route: '/chats' },
    { icon: KeyRound, label: 'API', route: '/developer' },
];

const analyticsNavItems: NavItem[] = [
    { icon: BarChart3, label: 'Analitik', route: '#' },
    { icon: FileSpreadsheet, label: 'Laporan Pajak', route: '/tax-reports' },
];

// Track open submenus (default open if active)
const openSubMenus = ref<Record<string, boolean>>({
    Produk: props.activePage === 'Produk',
});

function toggleSubMenu(label: string) {
    openSubMenus.value[label] = !openSubMenus.value[label];
}

function navigate(url: string) {
    if (url && url !== '#') {
        router.visit(url);
    }
}

async function handleLogout() {
    await logoutUser();
    router.post('/logout');
}

function isActive(item: NavItem): boolean {
    return props.activePage === item.label;
}
</script>

<template>
    <SidebarProvider>
        <!-- ── Dark Luxury Charcoal Sidebar ── -->
        <Sidebar
            collapsible="icon"
            class="border-r border-[var(--sidebar-border)] bg-[var(--sidebar)] font-sans text-[var(--sidebar-foreground)] transition-colors duration-500"
        >
            <!-- ── Header: Brand / Store ── -->
            <SidebarHeader class="p-3 group-data-[collapsible=icon]:p-1.5">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            :tooltip="storeName"
                            class="cursor-pointer rounded-2xl p-2 transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0 hover:bg-[var(--sidebar-accent)]"
                            @click="navigate('/dashboard')"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-base font-black text-[var(--sidebar-primary-foreground)] bg-[var(--sidebar-primary)]"
                            >
                                <Store class="h-5 w-5" />
                            </div>
                            <div
                                class="grid flex-1 text-left text-xs leading-tight group-data-[collapsible=icon]:hidden"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span class="truncate font-black text-[var(--sidebar-foreground)]"
                                        >{{ storeName }}</span
                                    >
                                    <span
                                        class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"
                                        title="Toko Online"
                                    />
                                </div>
                                <span
                                    class="truncate text-[10px] font-bold text-[var(--sidebar-accent-foreground)]"
                                    >Seller Command Center</span
                                >
                            </div>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarSeparator class="bg-[var(--sidebar-border)]" />

            <SidebarContent class="px-2">
                <!-- ── Main Menu ── -->
                <SidebarGroup class="py-2">
                    <SidebarGroupLabel
                        class="px-3 text-[10px] font-black tracking-wider text-[var(--sidebar-foreground)] opacity-70 uppercase group-data-[collapsible=icon]:hidden"
                    >
                        Navigasi Utama
                    </SidebarGroupLabel>
                    <SidebarGroupContent class="mt-1">
                        <SidebarMenu class="gap-1">
                            <SidebarMenuItem
                                v-for="item in mainNavItems"
                                :key="item.label"
                            >
                                <!-- Item with sub-menu -->
                                <template v-if="item.children">
                                    <SidebarMenuButton
                                        :is-active="isActive(item)"
                                        :tooltip="item.label"
                                        class="h-10 cursor-pointer rounded-xl text-xs font-bold transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0"
                                        :class="
                                            isActive(item)
                                                ? activeNavClass
                                                : 'text-[var(--sidebar-foreground)] opacity-70 hover:opacity-100 hover:bg-[var(--sidebar-accent)]'
                                        "
                                        @click="toggleSubMenu(item.label)"
                                    >
                                        <component
                                            :is="item.icon"
                                            class="h-4 w-4 shrink-0"
                                        />
                                        <span
                                            class="group-data-[collapsible=icon]:hidden"
                                            >{{ item.label }}</span
                                        >
                                        <ChevronRight
                                            class="ml-auto h-4 w-4 transition-transform duration-200 group-data-[collapsible=icon]:hidden"
                                            :class="{
                                                'rotate-90':
                                                    openSubMenus[item.label],
                                            }"
                                        />
                                    </SidebarMenuButton>

                                    <SidebarMenuSub
                                        v-if="openSubMenus[item.label]"
                                        class="ml-4 border-l border-[var(--sidebar-border)] pl-2 group-data-[collapsible=icon]:hidden"
                                    >
                                        <SidebarMenuSubItem
                                            v-for="child in item.children"
                                            :key="child.label"
                                        >
                                            <SidebarMenuSubButton
                                                class="cursor-pointer rounded-lg py-1.5 text-xs font-semibold text-[var(--sidebar-foreground)] opacity-70 transition-colors hover:opacity-100 hover:text-[var(--sidebar-primary)]"
                                                @click="navigate(child.route)"
                                            >
                                                {{ child.label }}
                                            </SidebarMenuSubButton>
                                        </SidebarMenuSubItem>
                                    </SidebarMenuSub>
                                </template>

                                <!-- Regular item -->
                                <template v-else>
                                    <SidebarMenuButton
                                        :is-active="isActive(item)"
                                        :tooltip="item.label"
                                        class="h-10 cursor-pointer rounded-xl text-xs font-bold transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0"
                                        :class="
                                            isActive(item)
                                                ? activeNavClass
                                                : 'text-[var(--sidebar-foreground)] opacity-70 hover:opacity-100 hover:bg-[var(--sidebar-accent)]'
                                        "
                                        @click="navigate(item.route)"
                                    >
                                        <component
                                            :is="item.icon"
                                            class="h-4 w-4 shrink-0"
                                        />
                                        <span
                                            class="group-data-[collapsible=icon]:hidden"
                                            >{{ item.label }}</span
                                        >
                                        <SidebarMenuBadge
                                            v-if="item.badge"
                                            class="rounded-full bg-[var(--sidebar-primary)] px-1.5 py-0.5 text-[9px] font-black text-[var(--sidebar-primary-foreground)] shadow-xs group-data-[collapsible=icon]:hidden"
                                        >
                                            {{ item.badge }}
                                        </SidebarMenuBadge>
                                    </SidebarMenuButton>
                                </template>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>

                <SidebarSeparator class="bg-[var(--sidebar-border)]" />

                <!-- ── Finance Menu ── -->
                <SidebarGroup class="py-2">
                    <SidebarGroupLabel
                        class="px-3 text-[10px] font-black tracking-wider text-[var(--sidebar-foreground)] opacity-70 uppercase group-data-[collapsible=icon]:hidden"
                    >
                        Dompet & Keuangan
                    </SidebarGroupLabel>
                    <SidebarGroupContent class="mt-1">
                        <SidebarMenu class="gap-1">
                            <SidebarMenuItem
                                v-for="item in financeNavItems"
                                :key="item.label"
                            >
                                <SidebarMenuButton
                                    :is-active="isActive(item)"
                                    :tooltip="item.label"
                                    class="h-10 cursor-pointer rounded-xl text-xs font-bold transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0"
                                    :class="
                                        isActive(item)
                                            ? activeNavClass
                                            : 'text-[var(--sidebar-foreground)] opacity-70 hover:opacity-100 hover:bg-[var(--sidebar-accent)]'
                                    "
                                    @click="navigate(item.route)"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-4 w-4 shrink-0"
                                    />
                                    <span
                                        class="group-data-[collapsible=icon]:hidden"
                                        >{{ item.label }}</span
                                    >
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>

                <SidebarSeparator class="bg-[var(--sidebar-border)]" />

                <!-- ── Analytics Menu ── -->
                <SidebarGroup class="py-2">
                    <SidebarGroupLabel
                        class="px-3 text-[10px] font-black tracking-wider text-[var(--sidebar-foreground)] opacity-70 uppercase group-data-[collapsible=icon]:hidden"
                    >
                        Laporan Performa
                    </SidebarGroupLabel>
                    <SidebarGroupContent class="mt-1">
                        <SidebarMenu class="gap-1">
                            <SidebarMenuItem
                                v-for="item in analyticsNavItems"
                                :key="item.label"
                            >
                                <SidebarMenuButton
                                    :is-active="isActive(item)"
                                    :tooltip="item.label"
                                    class="h-10 cursor-pointer rounded-xl text-xs font-bold transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0"
                                    :class="
                                        isActive(item)
                                            ? activeNavClass
                                            : 'text-[var(--sidebar-foreground)] opacity-70 hover:opacity-100 hover:bg-[var(--sidebar-accent)]'
                                    "
                                    @click="navigate(item.route)"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-4 w-4 shrink-0"
                                    />
                                    <span
                                        class="group-data-[collapsible=icon]:hidden"
                                        >{{ item.label }}</span
                                    >
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>

            <SidebarRail />

            <!-- ── Footer: User Profile Dropdown ── -->
            <SidebarFooter class="p-3 group-data-[collapsible=icon]:p-1.5">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <DropdownMenu v-if="activeUser">
                            <DropdownMenuTrigger as-child>
                                <SidebarMenuButton
                                    size="lg"
                                    tooltip="Profil Akun Seller"
                                    class="cursor-pointer rounded-2xl border border-[var(--sidebar-border)] bg-[var(--sidebar-accent)] p-2 transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0 hover:bg-[var(--sidebar-accent)]"
                                >
                                    <Avatar
                                        class="h-8 w-8 shrink-0 rounded-xl border border-[var(--sidebar-border)]"
                                    >
                                        <AvatarImage
                                            v-if="activeUser.photoURL"
                                            :src="activeUser.photoURL"
                                            :alt="userDisplayName"
                                        />
                                        <AvatarFallback
                                            class="rounded-xl bg-[var(--sidebar-primary)] text-xs font-black text-[var(--sidebar-primary-foreground)]"
                                        >
                                            {{ userInitial }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div
                                        class="grid flex-1 text-left text-xs leading-tight group-data-[collapsible=icon]:hidden"
                                    >
                                        <span
                                            class="truncate font-extrabold text-[var(--sidebar-foreground)]"
                                        >
                                            {{ userDisplayName }}
                                        </span>
                                        <span
                                            class="truncate font-mono text-[10px] text-[var(--sidebar-foreground)] opacity-70"
                                        >
                                            {{ userEmail }}
                                        </span>
                                    </div>
                                    <ChevronsUpDown
                                        class="ml-auto h-4 w-4 text-[var(--sidebar-foreground)] opacity-70 group-data-[collapsible=icon]:hidden"
                                    />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent
                                class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-2xl border-border bg-card p-2 text-foreground shadow-2xl"
                                side="right"
                                align="end"
                                :side-offset="8"
                            >
                                <DropdownMenuLabel class="p-1 font-normal">
                                    <div
                                        class="flex items-center gap-2.5 px-2 py-1.5 text-left text-xs"
                                    >
                                        <Avatar
                                            class="h-8 w-8 shrink-0 rounded-xl border border-border"
                                        >
                                            <AvatarImage
                                                v-if="activeUser.photoURL"
                                                :src="activeUser.photoURL"
                                                :alt="userDisplayName"
                                            />
                                            <AvatarFallback
                                                class="rounded-xl bg-primary text-xs font-black text-primary-foreground"
                                            >
                                                {{ userInitial }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div
                                            class="grid flex-1 text-left leading-tight"
                                        >
                                            <span
                                                class="truncate font-black text-foreground"
                                                >{{ userDisplayName }}</span
                                            >
                                            <span
                                                class="truncate font-mono text-[10px] text-muted-foreground"
                                                >{{ userEmail }}</span
                                            >
                                        </div>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator class="bg-border" />
                                <DropdownMenuItem
                                    class="cursor-pointer gap-2 rounded-xl text-xs font-semibold hover:bg-muted focus:bg-muted focus:text-foreground"
                                    @click="navigate('/store-settings')"
                                >
                                    <Store class="h-4 w-4 text-primary" />
                                    Pengaturan Toko
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="cursor-pointer gap-2 rounded-xl text-xs font-semibold hover:bg-muted focus:bg-muted focus:text-foreground"
                                    @click="navigate('/' + ((usePage().props.store as any)?.slug ?? ''))"
                                >
                                    <ExternalLink
                                        class="h-4 w-4 text-primary"
                                    />
                                    Lihat Webstore Toko
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="bg-border" />
                                <DropdownMenuItem
                                    class="cursor-pointer gap-2 rounded-xl text-xs font-bold text-destructive hover:bg-destructive/10 focus:bg-destructive/10 focus:text-destructive"
                                    @click="handleLogout"
                                >
                                    <LogOut class="h-4 w-4" />
                                    Keluar Akun
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- Fallback user state -->
                        <SidebarMenuButton
                            v-else
                            size="lg"
                            tooltip="Login Akun Seller"
                            class="cursor-pointer rounded-2xl border border-[var(--sidebar-border)] bg-[var(--sidebar-accent)] p-2 group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0"
                            @click="navigate('/login')"
                        >
                            <Avatar
                                class="h-8 w-8 shrink-0 rounded-xl border border-[var(--sidebar-border)]"
                            >
                                <AvatarFallback
                                    class="rounded-xl bg-[var(--sidebar-primary)] text-xs font-black text-[var(--sidebar-primary-foreground)]"
                                >
                                    NK
                                </AvatarFallback>
                            </Avatar>
                            <div
                                class="grid flex-1 text-left text-xs leading-tight group-data-[collapsible=icon]:hidden"
                            >
                                <span class="truncate font-black text-[var(--sidebar-foreground)]"
                                    >{{ storeName }}</span
                                >
                                <span class="truncate text-[10px] text-[var(--sidebar-foreground)] opacity-70"
                                    >Merchant Active</span
                                >
                            </div>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>

        <!-- ── Main Content Area ── -->
        <SidebarInset class="bg-background">
            <!-- Top Header Bar (Mobile Responsive) -->
            <header
                class="sticky top-0 z-20 flex h-14 shrink-0 items-center justify-between gap-2 border-b border-border bg-card/90 px-3 backdrop-blur-md transition-all sm:h-16 sm:px-6"
            >
                <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                    <SidebarTrigger class="-ml-1 shrink-0" />
                    <SidebarSeparator
                        class="mr-1 h-4 shrink-0 sm:mr-2"
                        orientation="vertical"
                    />

                    <!-- Breadcrumb current page label -->
                    <div class="flex items-center gap-1.5 truncate">
                        <span
                            class="xs:inline hidden text-[10px] font-bold tracking-wider text-muted-foreground uppercase sm:text-xs"
                            >Merchant Hub</span
                        >
                        <span class="xs:inline hidden text-muted-foreground/50">/</span>
                        <span
                            class="truncate text-xs font-black text-foreground sm:text-sm"
                            >{{ activePage }}</span
                        >
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <!-- Period Switcher (only on Dashboard) -->
                    <div
                        v-if="activePage === 'Dashboard'"
                        class="hidden gap-1 rounded-2xl border border-border bg-secondary p-1 sm:flex"
                    >
                        <button
                            v-for="p in ['Hari', 'Minggu', 'Bulan'] as const"
                            :key="p"
                            @click="emit('update:period', p)"
                            class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-extrabold transition-all"
                            :class="[
                                period === p
                                    ? 'border border-border bg-card text-card-foreground shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground',
                            ]"
                        >
                            {{ p }}
                        </button>
                    </div>

                    <!-- Preview Storefront Button -->
                    <a
                        :href="'/' + ((usePage().props.store as any)?.slug ?? '')"
                        target="_blank"
                        class="hidden shrink-0 items-center gap-1.5 rounded-xl border border-border bg-card px-3 py-1.5 text-xs font-bold text-foreground shadow-xs transition-colors hover:bg-secondary hover:text-primary sm:flex"
                        title="Lihat Web Toko"
                    >
                        <ExternalLink class="h-3.5 w-3.5" />
                        Lihat Toko
                    </a>

                    <!-- Bell notification -->
                    <div ref="noticeRoot" class="relative">
                    <Button
                        variant="ghost"
                        size="sm"
                        class="relative h-8 w-8 shrink-0 rounded-xl p-0 hover:bg-secondary sm:h-9 sm:w-9 text-muted-foreground hover:text-foreground"
                        @click="notices.toggle()"
                    >
                        <Bell class="h-4 w-4 sm:h-4.5 sm:w-4.5" />
                        <span
                            v-if="notices.unread > 0"
                            class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-primary sm:top-2 sm:right-2"
                        />
                    </Button>
                    <div
                        v-if="notices.open"
                        class="absolute right-0 z-50 mt-2 w-80 rounded-2xl border bg-card p-2 shadow-lg"
                    >
                        <div class="flex items-center justify-between px-2 py-1">
                            <p class="text-xs font-bold">Notifikasi</p>
                            <button type="button" class="rounded-md p-1 text-muted-foreground hover:bg-muted" @click="notices.close()">
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <button
                            v-for="n in notices.items"
                            :key="n.id"
                            type="button"
                            class="w-full rounded-xl px-2 py-2 text-left hover:bg-muted"
                            @click="notices.visit(n.url)"
                        >
                            <p class="text-xs font-bold">{{ n.title }}</p>
                            <p class="text-[11px] text-muted-foreground">{{ n.body }}</p>
                        </button>
                        <p v-if="!notices.items.length" class="px-2 py-4 text-xs text-muted-foreground">Belum ada notifikasi.</p>
                    </div>
                    </div>

                    <!-- User info chip (Responsive on mobile) -->
                    <div
                        class="hidden shrink-0 items-center gap-2 rounded-2xl border border-border bg-card px-3.5 py-1.5 text-xs font-medium sm:flex"
                    >
                        <span
                            class="inline-block h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                        />
                        <span
                            class="max-w-28 truncate font-extrabold text-foreground sm:max-w-32"
                        >
                            {{ userDisplayName }}
                        </span>
                        <Badge
                            variant="default"
                            class="px-2 py-0.5 text-[9px] font-black tracking-wider uppercase shadow-2xs"
                        >
                            SELLER
                        </Badge>
                    </div>
                </div>
            </header>

            <!-- Page Content Slot -->
            <div class="flex flex-1 flex-col overflow-y-auto">
                <slot />
            </div>
        </SidebarInset>

        <Toaster position="top-right" />
    </SidebarProvider>
</template>
