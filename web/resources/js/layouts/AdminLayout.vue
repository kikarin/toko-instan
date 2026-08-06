<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    ShieldCheck,
    LogOut,
    LogIn,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import { logoutUser } from '@/lib/firebase';
import { useActiveUser } from '@/lib/useActiveUser';

interface Props {
    activePage?: 'Admin' | 'Users';
}

withDefaults(defineProps<Props>(), {
    activePage: 'Admin',
});

const activeUser = useActiveUser();

const userDisplayName = computed(() => {
    return activeUser.value?.displayName ?? activeUser.value?.email ?? '';
});

const userInitial = computed(() => {
    const name = userDisplayName.value;

    return name ? name.substring(0, 2).toUpperCase() : 'AD';
});

const navItems = [
    { icon: LayoutDashboard, label: 'Dashboard', route: '/admin' },
    { icon: Users, label: 'Users', route: '/admin/users' },
];

function navigate(url: string) {
    router.visit(url);
}

async function handleLogout() {
    await logoutUser();
    router.post('/logout');
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-[#f5f4f0] font-sans">
        <!-- ── Sidebar ── -->
        <aside
            class="z-20 flex w-16 shrink-0 flex-col items-center gap-2 border-r border-white/5 bg-[#1e1c2a] py-4 select-none"
        >
            <div
                @click="navigate('/admin')"
                class="mb-4 flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl bg-gradient-to-br from-[#6d4fc2] to-[#4a3790] text-base font-extrabold text-white shadow-md shadow-[#6d4fc2]/30"
            >
                S
            </div>

            <button
                v-for="item in navItems"
                :key="item.label"
                :title="item.label"
                @click="navigate(item.route)"
                class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl transition-all duration-150"
                :class="[
                    activePage === item.label
                        ? 'border border-[#6d4fc260] bg-[#6d4fc233] text-[#a78bfa]'
                        : 'border border-transparent text-white/40 hover:bg-white/5 hover:text-white/80',
                ]"
            >
                <component :is="item.icon" class="h-4 w-4" />
            </button>

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
                        class="cursor-pointer hover:ring-2 hover:ring-[#6d4fc2]"
                    />
                </template>
                <template v-else>
                    <button
                        title="Masuk"
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
            <header
                class="bg-opacity-95 sticky top-0 z-10 flex items-center justify-between border-b border-black/7 bg-[#faf9f6] px-6 py-3 backdrop-blur-md"
            >
                <div
                    class="flex items-center gap-1.5 rounded-xl bg-[#00000008] p-1"
                >
                    <Button
                        :variant="activePage === 'Admin' ? 'amber' : 'ghost'"
                        size="sm"
                        class="rounded-lg text-xs"
                        @click="navigate('/admin')"
                    >
                        <LayoutDashboard class="mr-1.5 h-3.5 w-3.5" /> Dashboard
                    </Button>
                    <Button
                        :variant="activePage === 'Users' ? 'amber' : 'ghost'"
                        size="sm"
                        class="rounded-lg text-xs"
                        @click="navigate('/admin/users')"
                    >
                        <Users class="mr-1.5 h-3.5 w-3.5" /> Users
                    </Button>
                </div>

                <div v-if="activeUser" class="flex items-center gap-2.5">
                    <div
                        class="flex items-center gap-2 rounded-xl border border-black/7 bg-[#f5f4f0] px-3 py-1.5 text-xs font-medium text-[#4a4a57]"
                    >
                        <span
                            class="inline-block h-2 w-2 animate-pulse rounded-full bg-[#6d4fc2]"
                        />
                        <span
                            class="max-w-32 truncate font-bold text-[#1c1c22]"
                            >{{ userDisplayName }}</span
                        >
                        <Badge
                            variant="violetSolid"
                            class="px-1.5 py-0 text-[9px] font-bold uppercase"
                        >
                            <ShieldCheck class="mr-1 h-2.5 w-2.5" /> Admin
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
            </header>

            <slot />
        </div>

        <Toaster richColors position="top-right" />
    </div>
</template>
