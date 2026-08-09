import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { toast } from '@/components/ui/sonner';

export const WITHDRAW_STATUS_LABEL: Record<string, string> = {
    pending: 'Pending',
    approved: 'Disetujui',
    rejected: 'Ditolak',
    transferred: 'Ditransfer',
};

export const WITHDRAW_STATUS_VARIANT: Record<
    string,
    'amber' | 'teal' | 'rose' | 'violetSolid'
> = {
    pending: 'amber',
    approved: 'violetSolid',
    rejected: 'rose',
    transferred: 'teal',
};

export const TRANSACTION_TYPE_LABEL: Record<string, string> = {
    order_escrow: 'Escrow penjualan',
    order_release_pending: 'Lepas escrow',
    order_release_available: 'Dana tersedia',
    order_refund: 'Refund',
    withdraw_hold: 'Withdraw di-hold',
    withdraw_fee: 'Biaya penarikan',
    withdraw_release: 'Dana dikembalikan',
    withdraw_paid: 'Withdraw ditransfer',
    adjustment: 'Penyesuaian',
};

export function useWalletWithdraw() {
    const form = reactive({
        amount: '',
        bank_name: '',
        account_number: '',
        account_name: '',
    });

    const submitting = ref(false);

    function resetForm() {
        form.amount = '';
        form.bank_name = '';
        form.account_number = '';
        form.account_name = '';
    }

    function submitWithdraw() {
        submitting.value = true;

        router.post(
            '/wallet/withdraw',
            { ...form },
            {
                preserveScroll: true,
                onSuccess: () => {
                    resetForm();
                    toast.success('Permintaan penarikan diajukan.');
                },
                onError: (errors) => {
                    toast.error(errors.amount || 'Gagal mengajukan penarikan.');
                },
                onFinish: () => {
                    submitting.value = false;
                },
            },
        );
    }

    function goPage(page: number) {
        if (page < 1) {
            return;
        }

        router.get(
            '/wallet',
            { page },
            {
                preserveState: true,
                preserveScroll: true,
                only: ['transactions'],
            },
        );
    }

    function goWithdrawPage(page: number) {
        if (page < 1) {
            return;
        }

        router.get(
            '/wallet',
            { wpage: page },
            {
                preserveState: true,
                preserveScroll: true,
                only: ['withdrawals'],
            },
        );
    }

    return {
        form,
        submitting,
        submitWithdraw,
        goPage,
        goWithdrawPage,
    };
}