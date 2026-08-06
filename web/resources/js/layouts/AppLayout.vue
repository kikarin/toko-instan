<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    ShoppingBag,
    Package,
    ShoppingCart,
    Users,
    Wallet,
    Ticket,
    BarChart3,
    LogIn,
    LogOut,
    UserPlus,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import { logoutUser } from '@/lib/firebase';
import { useActiveUser } from '@/lib/useActiveUser';

interface Props {
    title?: string;
    activePage?: 'Dashboard' | 'Marketplace' | 'Auth' | 'Produk';
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
    return activeUser.value?.displayName ?? activeUser.value?.email ?? '';
});

const userInitial = computed(() => {
    const name = userDisplayName.value;

    return name ? name.substring(0, 2).toUpperCase() : 'TB';
});

const navItems = [
    { icon: LayoutDashboard, label: 'Dashboard', route: '/' },
    { icon: ShoppingBag, label: 'Marketplace', route: '/marketplace' },
    { icon: Package, label: 'Produk', route: '/products' },
    { icon: ShoppingCart, label: 'Pesanan', route: '#' },
    { icon: Users, label: 'Pelanggan', route: '#' },
    { icon: Wallet, label: 'Dompet', route: '#' },
    { icon: Ticket, label: 'Voucher', route: '#' },
    { icon: BarChart3, label: 'Analitik', route: '#' },
];

function navigate(url: string) {
    if (url && url !== '#') {
        router.visit(url);
    }
}

async function handleLogout() {
    await logoutUser();
    router.post('/logout');
}
</script>

<template>
    <div class="flex min-h-screen bg-[#f5f4f0] font-sans">
        <!-- ── Sidebar ── -->
        <aside
            class="z-20 flex w-16 shrink-0 flex-col items-center gap-2 border-r border-white/5 bg-[#1e1c2a] py-4 select-none"
        >
            <!-- Logo Icon -->
            <div
                @click="navigate('/')"
                class="mb-4 flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl bg-gradient-to-br from-[#e07c28] to-[#c2500a] text-base font-extrabold text-white shadow-md shadow-[#e07c28]/30"
            >
                S
            </div>

            <!-- Nav Icons -->
            <button
                v-for="item in navItems"
                :key="item.label"
                :title="item.label"
                @click="navigate(item.route)"
                class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl transition-all duration-150"
                :class="[
                    activePage === item.label
                        ? 'border border-[#e07c2860] bg-[#e07c2833] text-[#e07c28]'
                        : 'border border-transparent text-white/40 hover:bg-white/5 hover:text-white/80',
                ]"
            >
                <component :is="item.icon" class="h-4 w-4" />
            </button>

            <!-- Bottom Profile / Auth Button -->
            <div class="mt-auto mb-2 flex flex-col items-center gap-2">
                <template v-if="activeUser">
                    <button
                        title="Keluar"
                        @click="handleLogout"
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-white/40 transition-colors hover:bg-white/5 hover:text-red-400"
                    >
                        <LogOut class="h-4 w-4" />
                    </button>
                    <Avatar
                        :src="activeUser.photoURL || undefined"
                        :fallback="userInitial"
                        :hue="270"
                        size="md"
                        class="cursor-pointer hover:ring-2 hover:ring-[#e07c28]"
                    />
                </template>
                <template v-else>
                    <button
                        title="Masuk / Login"
                        @click="navigate('/login')"
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-white/50 transition-colors hover:bg-white/10 hover:text-white"
                    >
                        <LogIn class="h-4 w-4" />
                    </button>
                </template>
            </div>
        </aside>

        <!-- ── Main Area ── -->
        <div class="flex min-w-0 flex-1 flex-col overflow-y-auto">
            <!-- Header Bar -->
            <header
                class="bg-opacity-95 sticky top-0 z-10 flex items-center justify-between border-b border-black/7 bg-[#faf9f6] px-6 py-3 backdrop-blur-md"
            >
                <div
                    class="flex items-center gap-1.5 rounded-xl bg-[#00000008] p-1"
                >
                    <Button
                        :variant="
                            activePage === 'Dashboard' ? 'amber' : 'ghost'
                        "
                        size="sm"
                        class="rounded-lg text-xs"
                        @click="navigate('/')"
                    >
                        Dashboard
                    </Button>
                    <Button
                        :variant="
                            activePage === 'Marketplace' ? 'amber' : 'ghost'
                        "
                        size="sm"
                        class="rounded-lg text-xs"
                        @click="navigate('/marketplace')"
                    >
                        Marketplace
                    </Button>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Period Switcher (if on Dashboard) -->
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

                    <!-- User Profile / Auth State -->
                    <div v-if="activeUser" class="flex items-center gap-2.5">
                        <div
                            class="flex items-center gap-2 rounded-xl border border-black/7 bg-[#f5f4f0] px-3 py-1.5 text-xs font-medium text-[#4a4a57]"
                        >
                            <span
                                class="inline-block h-2 w-2 animate-pulse rounded-full bg-[#22a15a]"
                            />
                            <span
                                class="max-w-32 truncate font-bold text-[#1c1c22]"
                            >
                                {{ userDisplayName }}
                            </span>
                            <Badge
                                variant="amber"
                                class="px-1.5 py-0 text-[9px] font-bold"
                            >
                                PREMIUM
                            </Badge>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-xs text-red-500 hover:bg-red-50 hover:text-red-600"
                            @click="handleLogout"
                        >
                            Keluar
                        </Button>
                    </div>
                    <div v-else class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            class="text-xs font-bold"
                            @click="navigate('/login')"
                        >
                            <LogIn class="mr-1 h-3.5 w-3.5" /> Masuk
                        </Button>
                        <Button
                            variant="amber"
                            size="sm"
                            class="text-xs font-bold shadow-sm"
                            @click="navigate('/register')"
                        >
                            <UserPlus class="mr-1 h-3.5 w-3.5" /> Daftar Toko
                        </Button>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <slot />
        </div>

        <Toaster richColors position="top-right" />
    </div>
</template>
