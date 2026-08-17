<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    ShieldCheck,
    LogOut,
    LogIn,
    Banknote,
    LifeBuoy,
    Building2,
    ShoppingCart,
} from 'lucide-vue-next';
import { computed } from 'vue';
import ImpersonationBanner from '@/components/impersonation/ImpersonationBanner.vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarProvider,
    SidebarRail,
    SidebarSeparator,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { Toaster } from '@/components/ui/sonner';
import { useActiveUser } from '@/composables/useActiveUser';
import { useStoreTheme } from '@/composables/useStoreTheme';
import { logoutUser } from '@/lib/firebase';

interface Props {
    activePage?: 'Admin' | 'Users' | 'Tenants' | 'Orders' | 'Penarikan' | 'Tiket';
}

withDefaults(defineProps<Props>(), {
    activePage: 'Admin',
});

useStoreTheme();

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
    { icon: Building2, label: 'Tenants', route: '/admin/tenants' },
    { icon: ShoppingCart, label: 'Orders', route: '/admin/orders' },
    { icon: Banknote, label: 'Penarikan', route: '/admin/withdrawals' },
    { icon: LifeBuoy, label: 'Tiket', route: '/admin/tickets' },
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
    <SidebarProvider>
        <div
            class="flex h-screen w-full overflow-hidden bg-background font-sans"
        >
            <!-- ── Sidebar ── -->
            <Sidebar collapsible="icon">
                <SidebarHeader class="items-center justify-center py-3">
                    <div
                        @click="navigate('/admin')"
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl bg-primary text-base font-extrabold text-primary-foreground shadow-md select-none"
                    >
                        S
                    </div>
                </SidebarHeader>

                <SidebarContent>
                    <SidebarGroup>
                        <SidebarGroupLabel
                            class="text-[10px] tracking-wider text-muted-foreground uppercase select-none"
                        >
                            Admin
                        </SidebarGroupLabel>
                        <SidebarGroupContent>
                            <SidebarMenu>
                                <SidebarMenuItem
                                    v-for="item in navItems"
                                    :key="item.label"
                                >
                                    <SidebarMenuButton
                                        :is-active="activePage === item.label"
                                        :tooltip="item.label"
                                        size="lg"
                                        class="cursor-pointer"
                                        @click="navigate(item.route)"
                                    >
                                        <component
                                            :is="item.icon"
                                            class="h-4 w-4"
                                        />
                                        <span>{{ item.label }}</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarGroupContent>
                    </SidebarGroup>
                </SidebarContent>

                <SidebarRail />

                <SidebarFooter>
                    <SidebarSeparator class="bg-border" />
                    <div class="flex flex-col items-center gap-2 py-2">
                        <template v-if="activeUser">
                            <button
                                title="Keluar"
                                @click="handleLogout"
                                class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-muted-foreground transition-colors hover:bg-muted hover:text-destructive"
                            >
                                <LogOut class="h-4 w-4" />
                            </button>
                            <Avatar
                                :src="activeUser.photoURL || undefined"
                                :fallback="userInitial"
                                :hue="270"
                                size="md"
                                class="cursor-pointer hover:ring-2 hover:ring-primary"
                            />
                        </template>
                        <template v-else>
                            <button
                                title="Masuk"
                                @click="navigate('/login')"
                                class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                                <LogIn class="h-4 w-4" />
                            </button>
                        </template>
                    </div>
                </SidebarFooter>
            </Sidebar>

            <!-- ── Main Area ── -->
            <SidebarInset>
                <header
                    class="bg-opacity-95 sticky top-0 z-10 flex h-14 items-center justify-between gap-4 border-b border-border bg-card px-4 backdrop-blur-md transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12!"
                >
                    <div class="flex items-center gap-2">
                        <SidebarTrigger />
                        <div
                            class="flex items-center gap-1.5 rounded-xl bg-muted p-1"
                        >
                            <Button
                                :variant="
                                    activePage === 'Admin' ? 'default' : 'ghost'
                                "
                                size="sm"
                                class="rounded-lg text-xs"
                                @click="navigate('/admin')"
                            >
                                <LayoutDashboard class="mr-1.5 h-3.5 w-3.5" />
                                Dashboard
                            </Button>
                            <Button
                                :variant="
                                    activePage === 'Users' ? 'default' : 'ghost'
                                "
                                size="sm"
                                class="rounded-lg text-xs"
                                @click="navigate('/admin/users')"
                            >
                                <Users class="mr-1.5 h-3.5 w-3.5" /> Users
                            </Button>
                        </div>
                    </div>

                    <div v-if="activeUser" class="flex items-center gap-2.5">
                        <div
                            class="flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-1.5 text-xs font-medium text-muted-foreground"
                        >
                            <span
                                class="inline-block h-2 w-2 animate-pulse rounded-full bg-primary"
                            />
                            <span
                                class="max-w-32 truncate font-bold text-foreground"
                            >
                                {{ userDisplayName }}
                            </span>
                            <Badge
                                variant="default"
                                class="px-1.5 py-0 text-[9px] font-bold uppercase"
                            >
                                <ShieldCheck class="mr-1 h-2.5 w-2.5" /> Admin
                            </Badge>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-xs text-destructive hover:bg-destructive/10 hover:text-destructive"
                            @click="handleLogout"
                        >
                            Keluar
                        </Button>
                    </div>
                </header>

                <!-- Page Content -->
                <div class="flex flex-1 flex-col overflow-y-auto">
                    <ImpersonationBanner />
                    <div class="flex flex-1 overflow-y-auto">
                        <slot />
                    </div>
                </div>
            </SidebarInset>

            <Toaster richColors position="top-right" />
        </div>
    </SidebarProvider>
</template>
