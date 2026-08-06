<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Users, UserX } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardTitle } from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Input } from '@/components/ui/input';
import { toast } from 'vue-sonner';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Search } from 'lucide-vue-next';

interface AdminUser {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string | null;
}

interface Props {
    users?: AdminUser[];
}

const props = defineProps<Props>();

const searchQ = ref('');

const filtered = computed(() => {
    const q = searchQ.value.toLowerCase();

    if (!q) {
        return props.users ?? [];
    }

    return (props.users ?? []).filter(
        (u) =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q),
    );
});

const roleLabel: Record<string, string> = {
    buyer: 'Pembeli',
    seller: 'Seller',
    admin: 'Admin',
};

const roleVariant: Record<string, 'teal' | 'amber' | 'violetSolid'> = {
    buyer: 'teal',
    seller: 'amber',
    admin: 'violetSolid',
};

interface PendingAction {
    kind: 'delete' | 'role';
    user: AdminUser;
    role?: string;
}

const dialog = ref<PendingAction | null>(null);

const dialogTitle = computed(() => {
    if (!dialog.value) {
        return '';
    }

    return dialog.value.kind === 'delete'
        ? `Hapus user "${dialog.value.user.name}"?`
        : `Ubah role ${dialog.value.user.name}?`;
});

const dialogDescription = computed(() => {
    if (!dialog.value) {
        return '';
    }

    return dialog.value.kind === 'delete'
        ? 'Aksi ini permanen dan tidak dapat dibatalkan.'
        : `Role akan diubah menjadi "${roleLabel[dialog.value.role ?? '']}".`;
});

function confirmRoleChange(user: AdminUser, role: string) {
    if (role === user.role) {
        return;
    }

    dialog.value = { kind: 'role', user, role };
}

function confirmDelete(user: AdminUser) {
    dialog.value = { kind: 'delete', user };
}

function runAction() {
    if (!dialog.value) {
        return;
    }

    const { kind, user, role } = dialog.value;

    if (kind === 'delete') {
        router.delete(`/admin/users/${user.id}`, {
            onSuccess: () => toast.success(`${user.name} dihapus.`),
            onError: (errors) =>
                toast.error(errors.message || 'Gagal menghapus user.'),
        });
    } else if (kind === 'role') {
        router.patch(
            `/admin/users/${user.id}/role`,
            { role },
            {
                onSuccess: () =>
                    toast.success(
                        `Role ${user.name} diubah ke ${roleLabel[role ?? '']}.`,
                    ),
                onError: () => toast.error('Gagal mengubah role.'),
            },
        );
    }

    dialog.value = null;
}
</script>

<template>
    <Head title="Manajemen Users - Toko Instan" />

    <AdminLayout activePage="Users">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 sm:p-6">
            <div>
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#6d4fc2] uppercase"
                >
                    Admin Master
                </p>
                <h1
                    class="flex items-center gap-2 text-2xl font-extrabold text-[#1c1c22]"
                >
                    <Users class="h-6 w-6" /> Manajemen Users
                </h1>
            </div>

            <div class="relative max-w-sm">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-[#9090a0]" />
                <Input
                    v-model="searchQ"
                    placeholder="Cari nama atau email..."
                    class="pl-9"
                />
            </div>

            <Card class="p-5">
                <CardTitle class="mb-3 text-sm"
                    >Daftar User ({{ filtered.length }})</CardTitle
                >
                <div class="flex flex-col gap-2.5">
                    <div
                        v-for="u in filtered"
                        :key="u.id"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-black/5 bg-[#faf9f6] px-4 py-3"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#6d4fc230] bg-[#6d4fc21a] text-xs font-bold text-[#6d4fc2]"
                            >
                                {{ u.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-xs font-bold text-[#1c1c22]"
                                >
                                    {{ u.name }}
                                </p>
                                <p class="truncate text-[10px] text-[#9090a0]">
                                    {{ u.email }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Badge
                                :variant="roleVariant[u.role] ?? 'teal'"
                                class="px-2.5 py-0.5 text-[9px] normal-case uppercase"
                            >
                                {{ roleLabel[u.role] ?? u.role }}
                            </Badge>

                            <!-- Role switcher -->
                            <div
                                class="flex items-center gap-1 rounded-xl border border-black/10 bg-white p-0.5"
                            >
                                <button
                                    v-for="r in ['buyer', 'seller', 'admin']"
                                    :key="r"
                                    @click="confirmRoleChange(u, r)"
                                    class="cursor-pointer rounded-lg px-2 py-1 text-[10px] font-bold transition-all"
                                    :class="
                                        u.role === r
                                            ? 'bg-[#6d4fc2] text-white'
                                            : 'text-[#9090a0] hover:text-[#1c1c22]'
                                    "
                                >
                                    {{ roleLabel[r] }}
                                </button>
                            </div>

                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-[10px] text-red-500 hover:bg-red-50 hover:text-red-600"
                                @click="confirmDelete(u)"
                            >
                                <UserX class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>

                    <p
                        v-if="!filtered.length"
                        class="py-6 text-center text-xs text-[#9090a0]"
                    >
                        Tidak ada user ditemukan.
                    </p>
                </div>
            </Card>
        </main>

        <ConfirmDialog
            :open="dialog !== null"
            :title="dialogTitle"
            :description="dialogDescription"
            :confirm-label="dialog?.kind === 'delete' ? 'Hapus' : 'Ubah Role'"
            :tone="dialog?.kind === 'delete' ? 'danger' : 'primary'"
            @confirm="runAction"
            @cancel="dialog = null"
        />
    </AdminLayout>
</template>
