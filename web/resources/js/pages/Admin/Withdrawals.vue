<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Check, Banknote, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { AdminWithdrawal } from '@/types/wallet';

interface Props {
    withdrawals?: AdminWithdrawal[];
}

defineProps<Props>();

const rejectId = ref<number | null>(null);
const rejectReason = ref('');

const statusLabel: Record<string, string> = {
    pending: 'Pending',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    transferred: 'Ditransfer',
};

const statusVariant: Record<string, 'amber' | 'teal' | 'rose' | 'violetSolid'> =
    {
        pending: 'amber',
        approved: 'violetSolid',
        rejected: 'rose',
        transferred: 'teal',
    };

function approve(w: AdminWithdrawal) {
    router.patch(`/admin/withdrawals/${w.id}/approve`, undefined, {
        onSuccess: () => toast.success('Penarikan disetujui.'),
        onError: () => toast.error('Gagal menyetujui penarikan.'),
    });
}

function transferred(w: AdminWithdrawal) {
    router.patch(`/admin/withdrawals/${w.id}/transferred`, undefined, {
        onSuccess: () => toast.success('Penarikan ditandai ditransfer.'),
        onError: () => toast.error('Gagal menandai penarikan.'),
    });
}

function submitReject() {
    if (rejectId.value === null || !rejectReason.value.trim()) {
        return;
    }

    router.patch(
        `/admin/withdrawals/${rejectId.value}/reject`,
        { reason: rejectReason.value },
        {
            onSuccess: () => {
                toast.success('Penarikan ditolak, dana dikembalikan.');
                rejectId.value = null;
                rejectReason.value = '';
            },
            onError: () => toast.error('Gagal menolak penarikan.'),
        },
    );
}
</script>

<template>
    <Head title="Penarikan - Toko Instan" />

    <AdminLayout activePage="Penarikan">
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
                    <Banknote class="h-6 w-6" /> Penarikan Saldo
                </h1>
            </div>

            <Card class="p-5">
                <CardTitle class="mb-3 text-sm">Daftar Penarikan</CardTitle>
                <div class="flex flex-col gap-2.5">
                    <div
                        v-for="w in withdrawals ?? []"
                        :key="w.id"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-black/5 bg-[#faf9f6] px-4 py-3"
                    >
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#1c1c22]">
                                {{ w.amount }}
                                <span class="font-normal text-[#9090a0]">
                                    (net {{ w.net_amount }})
                                </span>
                            </p>
                            <p class="text-[10px] text-[#9090a0]">
                                {{ w.store_name }} • {{ w.bank_name }} •
                                {{ w.account_number }} ({{ w.account_name }}) •
                                {{ w.created_at }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Badge
                                :variant="statusVariant[w.status] ?? 'amber'"
                                class="px-2.5 py-0.5 text-[9px] uppercase"
                            >
                                {{ statusLabel[w.status] ?? w.status }}
                            </Badge>

                            <template v-if="w.status === 'pending'">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-[10px] text-[#22a15a] hover:bg-emerald-50"
                                    @click="approve(w)"
                                >
                                    <Check class="h-3.5 w-3.5" /> Approve
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-[10px] text-red-500 hover:bg-red-50"
                                    @click="
                                        rejectId = w.id;
                                        rejectReason = '';
                                    "
                                >
                                    <X class="h-3.5 w-3.5" /> Tolak
                                </Button>
                            </template>

                            <Button
                                v-if="w.status === 'approved'"
                                variant="ghost"
                                size="sm"
                                class="text-[10px] text-[#0e9f8a] hover:bg-teal-50"
                                @click="transferred(w)"
                            >
                                <Check class="h-3.5 w-3.5" /> Sudah Transfer
                            </Button>
                        </div>
                    </div>

                    <p
                        v-if="!(withdrawals ?? []).length"
                        class="py-6 text-center text-xs text-[#9090a0]"
                    >
                        Belum ada penarikan.
                    </p>
                </div>
            </Card>

            <div
                v-if="rejectId !== null"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                @click.self="rejectId = null"
            >
                <Card class="w-full max-w-sm p-5">
                    <CardTitle class="mb-3 text-sm">Tolak penarikan</CardTitle>
                    <Textarea
                        v-model="rejectReason"
                        placeholder="Alasan penolakan..."
                        class="min-h-24"
                    />
                    <div class="mt-4 flex justify-end gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="rejectId = null"
                        >
                            Batal
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            :disabled="!rejectReason.trim()"
                            @click="submitReject"
                        >
                            Tolak & Kembalikan Dana
                        </Button>
                    </div>
                </Card>
            </div>
        </main>
    </AdminLayout>
</template>
