<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Package,
    ShoppingCart,
    Users,
    Wallet,
    Ticket,
    BarChart3,
    LogOut,
    ChevronRight,
    ChevronsUpDown,
    Store,
    Bell,
    Settings,
    Tags,
    Boxes,
    Palette,
    ExternalLink,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
import { logoutUser } from '@/lib/firebase';
import { useActiveUser } from '@/lib/useActiveUser';
import { useStoreTheme } from '@/lib/useStoreTheme';

interface NavItem {
    icon: any;
    label: string;
    route: string;
    badge?: string | number;
    children?: { label: string; route: string }[];
}

interface Props {
    title?: string;
    activePage?: 'Dashboard' | 'Katalog' | 'Produk' | 'Stok & Inventory' | 'Pesanan' | 'Pengaturan Toko' | 'Pelanggan' | 'Dompet' | 'Voucher' | 'Analitik' | 'Tampilan & Konten';
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

const activeNavClass = 'bg-[var(--brand-soft)] text-[var(--brand)] font-black border border-[var(--brand)]/40 shadow-md shadow-black/10 group-data-[collapsible=icon]:bg-(--brand) group-data-[collapsible=icon]:text-white group-data-[collapsible=icon]:border-none';

const userDisplayName = computed(() => {
    return activeUser.value?.displayName ?? activeUser.value?.email ?? 'Nike Official Manager';
});

const userInitial = computed(() => {
    const name = userDisplayName.value;

    return name ? name.substring(0, 2).toUpperCase() : 'NK';
});

const userEmail = computed(() => {
    return activeUser.value?.email ?? 'seller@nike.com';
});

// Nav groups
const mainNavItems: NavItem[] = [
    { icon: LayoutDashboard, label: 'Dashboard', route: '/dashboard' },
    { icon: Tags, label: 'Katalog', route: '/catalog' },
    {
        icon: Package,
        label: 'Produk',
        route: '/products',
        children: [
            { label: 'Katalog Produk', route: '/products' },
            { label: '+ Tambah Produk', route: '/products/create' },
        ],
    },
    { icon: Boxes, label: 'Stok & Inventory', route: '/inventory' },
    {
        icon: ShoppingCart,
        label: 'Pesanan',
        route: '/orders',
        badge: 3,
    },
    { icon: Settings, label: 'Pengaturan Toko', route: '/store-settings' },
    { icon: Palette, label: 'Tampilan & Konten', route: '/store-cms' },
    { icon: Users, label: 'Pelanggan', route: '#' },
];

const financeNavItems: NavItem[] = [
    { icon: Wallet, label: 'Dompet', route: '/wallet' },
    { icon: Ticket, label: 'Voucher', route: '#' },
];

const analyticsNavItems: NavItem[] = [
    { icon: BarChart3, label: 'Analitik', route: '#' },
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
        <Sidebar collapsible="icon" class="border-r border-white/10 bg-[#18181c] text-white font-sans">
            <!-- ── Header: Brand / Store ── -->
            <SidebarHeader class="p-3 group-data-[collapsible=icon]:p-1.5">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            tooltip="Nike Official Store"
                            class="cursor-pointer rounded-2xl transition-all hover:bg-white/5 p-2 group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:justify-center"
                            @click="navigate('/dashboard')"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl text-base font-black text-white shrink-0"
                                :style="{ background: 'linear-gradient(135deg, var(--brand), var(--brand-secondary))' }"
                            >
                                <Store class="h-5 w-5" />
                            </div>
                            <div class="grid flex-1 text-left text-xs leading-tight group-data-[collapsible=icon]:hidden">
                                <div class="flex items-center gap-1.5">
                                    <span class="truncate font-black text-white">Nike Official Store</span>
                                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" title="Toko Online" />
                                </div>
                                <span class="truncate text-[10px] text-[var(--brand)] font-bold">Seller Command Center</span>
                            </div>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarSeparator class="bg-white/10" />

            <SidebarContent class="px-2">
                <!-- ── Main Menu ── -->
                <SidebarGroup class="py-2">
                    <SidebarGroupLabel class="text-[10px] font-black uppercase text-zinc-500 tracking-wider px-3 group-data-[collapsible=icon]:hidden">
                        Navigasi Utama
                    </SidebarGroupLabel>
                    <SidebarGroupContent class="mt-1">
                        <SidebarMenu class="gap-1">
                            <SidebarMenuItem v-for="item in mainNavItems" :key="item.label">
                                <!-- Item with sub-menu -->
                                <template v-if="item.children">
                                    <SidebarMenuButton
                                        :is-active="isActive(item)"
                                        :tooltip="item.label"
                                        class="cursor-pointer rounded-xl font-bold text-xs h-10 transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:justify-center"
                                        :class="
                                            isActive(item)
                                                ? activeNavClass
                                                : 'text-zinc-400 hover:bg-white/5 hover:text-white'
                                        "
                                        @click="toggleSubMenu(item.label)"
                                    >
                                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                                        <span class="group-data-[collapsible=icon]:hidden">{{ item.label }}</span>
                                        <ChevronRight
                                            class="ml-auto h-4 w-4 transition-transform duration-200 group-data-[collapsible=icon]:hidden"
                                            :class="{ 'rotate-90': openSubMenus[item.label] }"
                                        />
                                    </SidebarMenuButton>

                                    <SidebarMenuSub v-if="openSubMenus[item.label]" class="ml-4 border-l border-[var(--brand)]/30 pl-2 group-data-[collapsible=icon]:hidden">
                                        <SidebarMenuSubItem
                                            v-for="child in item.children"
                                            :key="child.label"
                                        >
                                            <SidebarMenuSubButton
                                                class="cursor-pointer rounded-lg text-xs font-semibold py-1.5 text-zinc-400 hover:text-(--brand) transition-colors"
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
                                        class="cursor-pointer rounded-xl font-bold text-xs h-10 transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:justify-center"
                                        :class="
                                            isActive(item)
                                                ? activeNavClass
                                                : 'text-zinc-400 hover:bg-white/5 hover:text-white'
                                        "
                                        @click="navigate(item.route)"
                                    >
                                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                                        <span class="group-data-[collapsible=icon]:hidden">{{ item.label }}</span>
                                        <SidebarMenuBadge
                                            v-if="item.badge"
                                            class="bg-(--brand) text-white font-black text-[9px] px-1.5 py-0.5 rounded-full shadow-xs group-data-[collapsible=icon]:hidden"
                                        >
                                            {{ item.badge }}
                                        </SidebarMenuBadge>
                                    </SidebarMenuButton>
                                </template>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>

                <SidebarSeparator class="bg-white/10" />

                <!-- ── Finance Menu ── -->
                <SidebarGroup class="py-2">
                    <SidebarGroupLabel class="text-[10px] font-black uppercase text-zinc-500 tracking-wider px-3 group-data-[collapsible=icon]:hidden">
                        Dompet & Keuangan
                    </SidebarGroupLabel>
                    <SidebarGroupContent class="mt-1">
                        <SidebarMenu class="gap-1">
                            <SidebarMenuItem v-for="item in financeNavItems" :key="item.label">
                                <SidebarMenuButton
                                    :is-active="isActive(item)"
                                    :tooltip="item.label"
                                    class="cursor-pointer rounded-xl font-bold text-xs h-10 transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:justify-center"
                                    :class="
                                        isActive(item)
                                            ? activeNavClass
                                            : 'text-zinc-400 hover:bg-white/5 hover:text-white'
                                    "
                                    @click="navigate(item.route)"
                                >
                                    <component :is="item.icon" class="h-4 w-4 shrink-0" />
                                    <span class="group-data-[collapsible=icon]:hidden">{{ item.label }}</span>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>

                <SidebarSeparator class="bg-white/10" />

                <!-- ── Analytics Menu ── -->
                <SidebarGroup class="py-2">
                    <SidebarGroupLabel class="text-[10px] font-black uppercase text-zinc-500 tracking-wider px-3 group-data-[collapsible=icon]:hidden">
                        Laporan Performa
                    </SidebarGroupLabel>
                    <SidebarGroupContent class="mt-1">
                        <SidebarMenu class="gap-1">
                            <SidebarMenuItem v-for="item in analyticsNavItems" :key="item.label">
                                <SidebarMenuButton
                                    :is-active="isActive(item)"
                                    :tooltip="item.label"
                                    class="cursor-pointer rounded-xl font-bold text-xs h-10 transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:justify-center"
                                    :class="
                                        isActive(item)
                                            ? activeNavClass
                                            : 'text-zinc-400 hover:bg-white/5 hover:text-white'
                                    "
                                    @click="navigate(item.route)"
                                >
                                    <component :is="item.icon" class="h-4 w-4 shrink-0" />
                                    <span class="group-data-[collapsible=icon]:hidden">{{ item.label }}</span>
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
                                    class="cursor-pointer rounded-2xl border border-white/10 bg-white/5 p-2 hover:bg-white/10 transition-all group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:justify-center"
                                >
                                    <Avatar class="h-8 w-8 rounded-xl border border-white/20 shrink-0">
                                        <AvatarImage v-if="activeUser.photoURL" :src="activeUser.photoURL" :alt="userDisplayName" />
                                        <AvatarFallback class="bg-(--brand) text-white font-black text-xs rounded-xl">
                                            {{ userInitial }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="grid flex-1 text-left text-xs leading-tight group-data-[collapsible=icon]:hidden">
                                        <span class="truncate font-extrabold text-white">
                                            {{ userDisplayName }}
                                        </span>
                                        <span class="truncate text-[10px] text-zinc-400 font-mono">
                                            {{ userEmail }}
                                        </span>
                                    </div>
                                    <ChevronsUpDown class="ml-auto h-4 w-4 text-zinc-400 group-data-[collapsible=icon]:hidden" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent
                                class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-2xl p-2 shadow-2xl border-white/10 bg-zinc-900 text-white"
                                side="right"
                                align="end"
                                :side-offset="8"
                            >
                                <DropdownMenuLabel class="p-1 font-normal">
                                    <div class="flex items-center gap-2.5 px-2 py-1.5 text-left text-xs">
                                        <Avatar class="h-8 w-8 rounded-xl border border-white/20 shrink-0">
                                            <AvatarImage v-if="activeUser.photoURL" :src="activeUser.photoURL" :alt="userDisplayName" />
                                            <AvatarFallback class="bg-(--brand) text-white font-black text-xs rounded-xl">
                                                {{ userInitial }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="grid flex-1 text-left leading-tight">
                                            <span class="truncate font-black text-white">{{ userDisplayName }}</span>
                                            <span class="truncate text-[10px] text-zinc-400 font-mono">{{ userEmail }}</span>
                                        </div>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator class="bg-white/10" />
                                <DropdownMenuItem class="cursor-pointer gap-2 rounded-xl text-xs font-semibold hover:bg-white/10 focus:bg-white/10 focus:text-white" @click="navigate('/store-settings')">
                                    <Store class="h-4 w-4 text-(--brand)" />
                                    Pengaturan Toko
                                </DropdownMenuItem>
                                <DropdownMenuItem class="cursor-pointer gap-2 rounded-xl text-xs font-semibold hover:bg-white/10 focus:bg-white/10 focus:text-white" @click="navigate('/marketplace')">
                                    <ExternalLink class="h-4 w-4 text-indigo-400" />
                                    Lihat Webstore Toko
                                </DropdownMenuItem>
                                <DropdownMenuSeparator class="bg-white/10" />
                                <DropdownMenuItem
                                    class="cursor-pointer gap-2 rounded-xl text-xs font-bold text-rose-400 hover:bg-rose-500/10 focus:bg-rose-500/10 focus:text-rose-400"
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
                            class="cursor-pointer rounded-2xl bg-white/5 border border-white/10 p-2 group-data-[collapsible=icon]:size-10 group-data-[collapsible=icon]:p-0 group-data-[collapsible=icon]:justify-center"
                            @click="navigate('/login')"
                        >
                            <Avatar class="h-8 w-8 rounded-xl border border-white/20 shrink-0">
                                <AvatarFallback class="bg-(--brand) text-white font-black text-xs rounded-xl">
                                    NK
                                </AvatarFallback>
                            </Avatar>
                            <div class="grid flex-1 text-left text-xs leading-tight group-data-[collapsible=icon]:hidden">
                                <span class="truncate font-black text-white">Nike Official</span>
                                <span class="truncate text-[10px] text-zinc-400">Merchant Active</span>
                            </div>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>

        <!-- ── Main Content Area ── -->
        <SidebarInset class="bg-[#faf9f6]">
            <!-- Top Header Bar (Mobile Responsive) -->
            <header
                class="sticky top-0 z-20 flex h-14 sm:h-16 shrink-0 items-center justify-between gap-2 border-b border-black/8 bg-white/90 px-3 sm:px-6 backdrop-blur-md transition-all"
            >
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <SidebarTrigger class="-ml-1 shrink-0" />
                    <SidebarSeparator class="mr-1 sm:mr-2 h-4 shrink-0" orientation="vertical" />

                    <!-- Breadcrumb current page label -->
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-400 uppercase tracking-wider hidden xs:inline">Merchant Hub</span>
                        <span class="text-zinc-300 hidden xs:inline">/</span>
                        <span class="text-xs sm:text-sm font-black text-[#1c1c22] truncate">{{ activePage }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Period Switcher (only on Dashboard) -->
                    <div
                        v-if="activePage === 'Dashboard'"
                        class="hidden sm:flex gap-1 rounded-2xl border border-black/8 bg-[#faf9f6] p-1"
                    >
                        <button
                            v-for="p in ['Hari', 'Minggu', 'Bulan'] as const"
                            :key="p"
                            @click="emit('update:period', p)"
                            class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-extrabold transition-all"
                            :class="[
                                period === p
                                    ? 'bg-white text-[#1c1c22] shadow-xs border border-black/8'
                                    : 'text-zinc-400 hover:text-black',
                            ]"
                        >
                            {{ p }}
                        </button>
                    </div>

                    <!-- Bell notification -->
                    <Button variant="ghost" size="sm" class="relative h-8 w-8 sm:h-9 sm:w-9 p-0 rounded-xl hover:bg-zinc-100 shrink-0" @click="navigate('#')">
                        <Bell class="h-4 w-4 sm:h-4.5 sm:w-4.5 text-zinc-600" />
                        <span
                            class="absolute right-1.5 sm:right-2 top-1.5 sm:top-2 h-2 w-2 rounded-full bg-(--brand) animate-ping"
                        />
                        <span
                            class="absolute right-1.5 sm:right-2 top-1.5 sm:top-2 h-2 w-2 rounded-full bg-[var(--brand)]"
                        />
                    </Button>

                    <!-- User info chip (Responsive on mobile) -->
                    <div
                        class="hidden sm:flex items-center gap-2 rounded-2xl border border-black/8 bg-[#faf9f6] px-3.5 py-1.5 text-xs font-medium shrink-0"
                    >
                        <span class="inline-block h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                        <span class="max-w-28 sm:max-w-32 truncate font-extrabold text-[#1c1c22]">
                            {{ userDisplayName }}
                        </span>
                        <Badge variant="amber" class="px-2 py-0.5 text-[9px] font-black tracking-wider uppercase shadow-2xs">
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

        <Toaster richColors position="top-right" />
    </SidebarProvider>
</template>
