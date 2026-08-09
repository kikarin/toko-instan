import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { AdminUser } from '@/types/admin';
import type { AdminPendingAction } from '@/types/admin';

export const ADMIN_ROLE_LABEL: Record<string, string> = {
    buyer: 'Pembeli',
    seller: 'Seller',
    admin: 'Admin',
};

export const ADMIN_ROLE_VARIANT: Record<string, 'teal' | 'amber' | 'violetSolid'> =
    {
        buyer: 'teal',
        seller: 'amber',
        admin: 'violetSolid',
    };

export function useAdminUsers(users: AdminUser[] | undefined) {
    const searchQ = ref('');

    const filtered = computed(() => {
        const q = searchQ.value.toLowerCase();

        if (!q) {
            return users ?? [];
        }

        return (users ?? []).filter(
            (u) =>
                u.name.toLowerCase().includes(q) ||
                u.email.toLowerCase().includes(q),
        );
    });

    const dialog = ref<AdminPendingAction | null>(null);

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
            : `Role akan diubah menjadi "${
                  ADMIN_ROLE_LABEL[dialog.value.role ?? '']
              }".`;
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

    function impersonate(user: AdminUser) {
        router.post(
            `/admin/users/${user.id}/impersonate`,
            {},
            {
                onError: () =>
                    toast.error('Gagal login sebagai user. Coba lagi.'),
            },
        );
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
                            `Role ${user.name} diubah ke ${
                                ADMIN_ROLE_LABEL[role ?? '']
                            }.`,
                        ),
                    onError: () => toast.error('Gagal mengubah role.'),
                },
            );
        }

        dialog.value = null;
    }

    return {
        searchQ,
        filtered,
        roleLabel: ADMIN_ROLE_LABEL,
        roleVariant: ADMIN_ROLE_VARIANT,
        dialog,
        dialogTitle,
        dialogDescription,
        confirmRoleChange,
        confirmDelete,
        impersonate,
        runAction,
    };
}