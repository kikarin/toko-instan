<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Wallet as WalletIcon,
    ArrowUpRight,
    Clock3,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    TRANSACTION_TYPE_LABEL as typeLabel,
    useWalletWithdraw,
    WITHDRAW_STATUS_LABEL as statusLabel,
    WITHDRAW_STATUS_VARIANT as statusVariant,
} from '@/composables/useWalletWithdraw';
import AppLayout from '@/layouts/AppLayout.vue';
import type { WalletSummary } from '@/types/wallet';
import type { Withdrawal } from '@/types/wallet';
import type { WalletTransaction } from '@/types/wallet';
import type { PagedList } from '@/types/wallet';

interface Props {
    wallet?: WalletSummary | null;
    withdrawals?: PagedList<Withdrawal> | null;
    transactions?: PagedList<WalletTransaction> | null;
}

defineProps<Props>();

const { form, submitting, submitWithdraw, goPage, goWithdrawPage } =
    useWalletWithdraw();
</script>

<template>
    <Head title="Dompet - Toko Instan" />

    <AppLayout title="Dompet" activePage="Dompet">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-5 p-4 sm:p-6">
            <div>
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                >
                    Seller Wallet
                </p>
                <h1
                    class="flex items-center gap-2 text-2xl font-extrabold text-[#1c1c22]"
                >
                    <WalletIcon class="h-6 w-6" /> Dompet Penjualan
                </h1>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card class="p-5">
                    <p class="text-[10px] font-bold text-[#9090a0] uppercase">
                        Saldo tersedia
                    </p>
                    <p class="mt-1 text-2xl font-extrabold text-[#22a15a]">
                        {{ wallet?.balance ?? 'Rp 0' }}
                    </p>
                </Card>
                <Card class="p-5">
                    <p class="text-[10px] font-bold text-[#9090a0] uppercase">
                        Pending escrow
                    </p>
                    <p
                        class="mt-1 flex items-center gap-1.5 text-2xl font-extrabold text-[#d97706]"
                    >
                        <Clock3 class="h-5 w-5" />
                        {{ wallet?.pending_balance ?? 'Rp 0' }}
                    </p>
                </Card>
            </div>

            <Card class="p-5">
                <CardTitle class="mb-3 flex items-center gap-1.5 text-sm">
                    <ArrowUpRight class="h-4 w-4" /> Tarik Saldo
                </CardTitle>
                <form
                    class="flex flex-col gap-3"
                    @submit.prevent="submitWithdraw"
                >
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <Label for="amount">Jumlah (Rp)</Label>
                            <Input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                min="6000"
                                step="1000"
                                placeholder="100000"
                                required
                            />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label for="bank_name">Bank</Label>
                            <Input
                                id="bank_name"
                                v-model="form.bank_name"
                                placeholder="BCA"
                                required
                            />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label for="account_number">No. Rekening</Label>
                            <Input
                                id="account_number"
                                v-model="form.account_number"
                                placeholder="1234567890"
                                required
                            />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label for="account_name">Atas Nama</Label>
                            <Input
                                id="account_name"
                                v-model="form.account_name"
                                placeholder="Nama Pemilik"
                                required
                            />
                        </div>
                    </div>
                    <p class="text-[10px] text-[#9090a0]">
                        Biaya penarikan Rp5.000 (plan Free) dipotong dari
                        jumlah.
                    </p>
                    <div>
                        <Button type="submit" :disabled="submitting">
                            {{
                                submitting ? 'Memproses...' : 'Ajukan Penarikan'
                            }}
                        </Button>
                    </div>
                </form>
            </Card>

            <Card class="p-5">
                <CardTitle class="mb-3 text-sm">
                    Riwayat Transaksi ({{
                        transactions?.pagination.total ?? 0
                    }})
                </CardTitle>
                <div class="flex flex-col gap-2.5">
                    <div
                        v-for="t in transactions?.data ?? []"
                        :key="t.id"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-black/5 bg-[#faf9f6] px-4 py-3"
                    >
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#1c1c22]">
                                {{ typeLabel[t.type] ?? t.type }}
                            </p>
                            <p class="text-[10px] text-[#9090a0]">
                                {{ t.description ?? '—' }} • {{ t.created_at }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-xs font-extrabold"
                                :class="
                                    t.direction === 'credit'
                                        ? 'text-[#22a15a]'
                                        : 'text-[#e0405a]'
                                "
                            >
                                {{ t.direction === 'credit' ? '+' : '−' }}
                                {{ t.amount }}
                            </p>
                            <p class="text-[10px] text-[#9090a0]">
                                Saldo {{ t.balance_after }} • Pending
                                {{ t.pending_after }}
                            </p>
                        </div>
                    </div>

                    <p
                        v-if="!(transactions?.data ?? []).length"
                        class="py-6 text-center text-xs text-[#9090a0]"
                    >
                        Belum ada transaksi.
                    </p>

                    <div
                        v-if="(transactions?.pagination.last_page ?? 1) > 1"
                        class="flex items-center justify-between pt-2"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="
                                (transactions?.pagination.current_page ?? 1) <=
                                1
                            "
                            @click="
                                goPage(
                                    (transactions?.pagination.current_page ??
                                        1) - 1,
                                )
                            "
                        >
                            <ChevronLeft class="h-4 w-4" /> Sebelumnya
                        </Button>
                        <span class="text-[10px] text-[#9090a0]">
                            Halaman
                            {{ transactions?.pagination.current_page ?? 1 }}
                            dari
                            {{ transactions?.pagination.last_page ?? 1 }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="
                                (transactions?.pagination.current_page ?? 1) >=
                                (transactions?.pagination.last_page ?? 1)
                            "
                            @click="
                                goPage(
                                    (transactions?.pagination.current_page ??
                                        1) + 1,
                                )
                            "
                        >
                            Berikutnya <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </Card>

            <Card class="p-5">
                <CardTitle class="mb-3 text-sm">
                    Riwayat Penarikan ({{ withdrawals?.pagination.total ?? 0 }})
                </CardTitle>
                <div class="flex flex-col gap-2.5">
                    <div
                        v-for="w in withdrawals?.data ?? []"
                        :key="w.id"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-black/5 bg-[#faf9f6] px-4 py-3"
                    >
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-[#1c1c22]">
                                {{ w.amount }}
                            </p>
                            <p class="text-[10px] text-[#9090a0]">
                                {{ w.bank_name }} • {{ w.account_number }} •
                                {{ w.created_at }}
                            </p>
                        </div>
                        <Badge
                            :variant="statusVariant[w.status] ?? 'amber'"
                            class="px-2.5 py-0.5 text-[9px] uppercase"
                        >
                            {{ statusLabel[w.status] ?? w.status }}
                        </Badge>
                    </div>

                    <p
                        v-if="!(withdrawals?.data ?? []).length"
                        class="py-6 text-center text-xs text-[#9090a0]"
                    >
                        Belum ada penarikan.
                    </p>

                    <div
                        v-if="(withdrawals?.pagination.last_page ?? 1) > 1"
                        class="flex items-center justify-between pt-2"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="
                                (withdrawals?.pagination.current_page ?? 1) <= 1
                            "
                            @click="
                                goWithdrawPage(
                                    (withdrawals?.pagination.current_page ??
                                        1) - 1,
                                )
                            "
                        >
                            <ChevronLeft class="h-4 w-4" /> Sebelumnya
                        </Button>
                        <span class="text-[10px] text-[#9090a0]">
                            Halaman
                            {{ withdrawals?.pagination.current_page ?? 1 }} dari
                            {{ withdrawals?.pagination.last_page ?? 1 }}
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="
                                (withdrawals?.pagination.current_page ?? 1) >=
                                (withdrawals?.pagination.last_page ?? 1)
                            "
                            @click="
                                goWithdrawPage(
                                    (withdrawals?.pagination.current_page ??
                                        1) + 1,
                                )
                            "
                        >
                            Berikutnya <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </Card>
        </main>
    </AppLayout>
</template>
