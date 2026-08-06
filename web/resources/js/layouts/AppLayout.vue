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
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
    useSidebar,
} from '@/components/ui/sidebar';
import { Toaster } from '@/components/ui/sonner';
import { logoutUser } from '@/lib/firebase';
import { useActiveUser } from '@/lib/useActiveUser';

interface NavItem {
    icon: any;
    label: string;
    route: string;
    badge?: string | number;
    children?: { label: string; route: string }[];
}

interface Props {
    title?: string;
    activePage?: 'Dashboard' | 'Produk' | 'Pesanan' | 'Pelanggan' | 'Dompet' | 'Voucher' | 'Analitik';
    period?: 'Hari' | 'Minggu' | 'Bulan';
}

withDefaults(defineProps<Props>(), {
    activePage: 'Dashboard',
    period: 'Bulan',
});

const emit = defineEmits<{
    (e: 'update:period', val: 'Hari' | 'Minggu' | 'Bulan'): void;
}>();

const activeUser = useActiveUser();

const userDisplayName = computed(() => {
    return activeUser.value?.displayName ?? activeUser.value?.email ?? 'Pengguna';
});

const userInitial = computed(() => {
    const name = userDisplayName.value;
    return name ? name.substring(0, 2).toUpperCase() : 'TB';
});

const userEmail = computed(() => {
    return activeUser.value?.email ?? '';
});

// Nav groups
const mainNavItems: NavItem[] = [
    { icon: LayoutDashboard, label: 'Dashboard', route: '/' },
    {
        icon: Package,
        label: 'Produk',
        route: '/products',
        children: [
            { label: 'Semua Produk', route: '/products' },
            { label: 'Tambah Produk', route: '/products/create' },
        ],
    },
    {
        icon: ShoppingCart,
        label: 'Pesanan',
        route: '#',
        badge: 3,
    },
    { icon: Users, label: 'Pelanggan', route: '#' },
];

const financeNavItems: NavItem[] = [
    { icon: Wallet, label: 'Dompet', route: '#' },
    { icon: Ticket, label: 'Voucher', route: '#' },
];

const analyticsNavItems: NavItem[] = [
    { icon: BarChart3, label: 'Analitik', route: '#' },
];

// Track which sub-menus are open
const openSubMenus = ref<Record<string, boolean>>({});

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

// Helper: is this nav item "active" (current page matches)
function isActive(item: NavItem, activePage: string): boolean {
    return activePage === item.label;
}
</script>

<template>
    <SidebarProvider>
        <Sidebar collapsible="icon" class="border-r-0">
            <!-- ── Header: Brand / Store ── -->
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            class="cursor-pointer data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            @click="navigate('/')"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#e07c28] to-[#c2500a] text-sm font-extrabold text-white shadow-md shadow-[#e07c28]/30 select-none"
                            >
                                S
                            </div>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold text-[#1c1c22]">Toko Instan</span>
                                <span class="truncate text-[10px] text-[#9090a0]">Seller Dashboard</span>
                            </div>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <!-- ── Main Menu ── -->
                <SidebarGroup>
                    <SidebarGroupLabel>Menu Utama</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem v-for="item in mainNavItems" :key="item.label">
                                <!-- Item with sub-menu -->
                                <template v-if="item.children">
                                    <SidebarMenuButton
                                        :is-active="isActive(item, activePage)"
                                        :tooltip="item.label"
                                        class="cursor-pointer"
                                        @click="toggleSubMenu(item.label)"
                                    >
                                        <component :is="item.icon" class="h-4 w-4" />
                                        <span>{{ item.label }}</span>
                                        <ChevronRight
                                            class="ml-auto h-4 w-4 transition-transform duration-200"
                                            :class="{ 'rotate-90': openSubMenus[item.label] }"
                                        />
                                    </SidebarMenuButton>
                                    <SidebarMenuSub v-if="openSubMenus[item.label]">
                                        <SidebarMenuSubItem
                                            v-for="child in item.children"
                                            :key="child.label"
                                        >
                                            <SidebarMenuSubButton
                                                :is-active="false"
                                                class="cursor-pointer"
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
                                        :is-active="isActive(item, activePage)"
                                        :tooltip="item.label"
                                        class="cursor-pointer"
                                        @click="navigate(item.route)"
                                    >
                                        <component :is="item.icon" class="h-4 w-4" />
                                        <span>{{ item.label }}</span>
                                        <SidebarMenuBadge v-if="item.badge">
                                            {{ item.badge }}
                                        </SidebarMenuBadge>
                                    </SidebarMenuButton>
                                </template>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>

                <SidebarSeparator />

                <!-- ── Finance Menu ── -->
                <SidebarGroup>
                    <SidebarGroupLabel>Keuangan</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem v-for="item in financeNavItems" :key="item.label">
                                <SidebarMenuButton
                                    :is-active="isActive(item, activePage)"
                                    :tooltip="item.label"
                                    class="cursor-pointer"
                                    @click="navigate(item.route)"
                                >
                                    <component :is="item.icon" class="h-4 w-4" />
                                    <span>{{ item.label }}</span>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>

                <SidebarSeparator />

                <!-- ── Analytics Menu ── -->
                <SidebarGroup>
                    <SidebarGroupLabel>Performa</SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem v-for="item in analyticsNavItems" :key="item.label">
                                <SidebarMenuButton
                                    :is-active="isActive(item, activePage)"
                                    :tooltip="item.label"
                                    class="cursor-pointer"
                                    @click="navigate(item.route)"
                                >
                                    <component :is="item.icon" class="h-4 w-4" />
                                    <span>{{ item.label }}</span>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>

            <SidebarRail />

            <!-- ── Footer: User Profile ── -->
            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <DropdownMenu v-if="activeUser">
                            <DropdownMenuTrigger as-child>
                                <SidebarMenuButton
                                    size="lg"
                                    class="cursor-pointer data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                                >
                                    <Avatar
                                        :src="activeUser.photoURL || undefined"
                                        :fallback="userInitial"
                                        :hue="270"
                                        size="sm"
                                    />
                                    <div class="grid flex-1 text-left text-sm leading-tight">
                                        <span class="truncate font-semibold text-[#1c1c22]">
                                            {{ userDisplayName }}
                                        </span>
                                        <span class="truncate text-[10px] text-[#9090a0]">
                                            {{ userEmail }}
                                        </span>
                                    </div>
                                    <ChevronsUpDown class="ml-auto h-4 w-4 text-[#9090a0]" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                                side="bottom"
                                align="end"
                                :side-offset="4"
                            >
                                <DropdownMenuLabel class="p-0 font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                        <Avatar
                                            :src="activeUser.photoURL || undefined"
                                            :fallback="userInitial"
                                            :hue="270"
                                            size="sm"
                                        />
                                        <div class="grid flex-1 text-left text-sm leading-tight">
                                            <span class="truncate font-semibold">{{ userDisplayName }}</span>
                                            <span class="truncate text-xs text-[#9090a0]">{{ userEmail }}</span>
                                        </div>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem class="cursor-pointer gap-2" @click="navigate('/store')">
                                    <Store class="h-4 w-4" />
                                    Profil Toko
                                </DropdownMenuItem>
                                <DropdownMenuItem class="cursor-pointer gap-2" @click="navigate('#')">
                                    <Bell class="h-4 w-4" />
                                    Notifikasi
                                </DropdownMenuItem>
                                <DropdownMenuItem class="cursor-pointer gap-2" @click="navigate('#')">
                                    <Settings class="h-4 w-4" />
                                    Pengaturan
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem
                                    class="cursor-pointer gap-2 text-red-500 focus:bg-red-50 focus:text-red-600"
                                    @click="handleLogout"
                                >
                                    <LogOut class="h-4 w-4" />
                                    Keluar
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- Not logged in state -->
                        <SidebarMenuButton
                            v-else
                            size="lg"
                            class="cursor-pointer"
                            @click="navigate('/login')"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#f5f4f0] text-[#9090a0]"
                            >
                                <Users class="h-4 w-4" />
                            </div>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold text-[#9090a0]">Belum Masuk</span>
                                <span class="truncate text-[10px] text-[#c8c8d5]">Klik untuk login</span>
                            </div>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>

        <!-- ── Main Content Area ── -->
        <SidebarInset>
            <!-- Top Header Bar -->
            <header
                class="sticky top-0 z-10 flex h-14 shrink-0 items-center justify-between gap-4 border-b border-black/7 bg-white/95 px-4 backdrop-blur-md transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12!"
            >
                <div class="flex items-center gap-3">
                    <SidebarTrigger class="-ml-1" />
                    <SidebarSeparator class="mr-2 h-4" orientation="vertical" />

                    <!-- Breadcrumb current page label -->
                    <span class="text-sm font-semibold text-[#1c1c22]">{{ activePage }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Period Switcher (only on Dashboard) -->
                    <div
                        v-if="activePage === 'Dashboard'"
                        class="flex gap-1 rounded-xl border border-black/7 bg-[#f5f4f0] p-1"
                    >
                        <button
                            v-for="p in ['Hari', 'Minggu', 'Bulan'] as const"
                            :key="p"
                            @click="emit('update:period', p)"
                            class="cursor-pointer rounded-lg px-3 py-1 text-xs font-semibold transition-all duration-150"
                            :class="[
                                period === p
                                    ? 'border border-black/10 bg-white text-[#1c1c22] shadow-xs'
                                    : 'text-[#9090a0] hover:text-[#1c1c22]',
                            ]"
                        >
                            {{ p }}
                        </button>
                    </div>

                    <!-- Bell notification -->
                    <Button variant="ghost" size="sm" class="relative h-8 w-8 p-0" @click="navigate('#')">
                        <Bell class="h-4 w-4 text-[#4a4a57]" />
                        <span
                            class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full bg-[#e07c28]"
                        />
                    </Button>

                    <!-- User info chip -->
                    <div
                        v-if="activeUser"
                        class="flex items-center gap-2 rounded-xl border border-black/7 bg-[#f5f4f0] px-3 py-1.5 text-xs font-medium text-[#4a4a57]"
                    >
                        <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-[#22a15a]" />
                        <span class="max-w-28 truncate font-bold text-[#1c1c22]">
                            {{ userDisplayName }}
                        </span>
                        <Badge variant="amber" class="px-1.5 py-0 text-[9px] font-bold">
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
