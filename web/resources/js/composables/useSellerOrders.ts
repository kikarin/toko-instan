import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import type { ComputedRef } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { SellerOrder } from '@/types/order';

export const ORDER_STATUS_BADGE: Record<
    string,
    {
        label: string;
        variant: 'amber' | 'teal' | 'rose' | 'violetSolid' | 'outline';
    }
> = {
    pending: { label: 'Menunggu Bayar', variant: 'amber' },
    paid: { label: 'Sudah Dibayar', variant: 'teal' },
    processing: { label: 'Diproses Seller', variant: 'violetSolid' },
    packed: { label: 'Dikemas', variant: 'violetSolid' },
    shipped: { label: 'Dikirim', variant: 'teal' },
    completed: { label: 'Selesai', variant: 'teal' },
    cancelled: { label: 'Dibatalkan', variant: 'rose' },
};

export function useSellerOrders(orders: ComputedRef<SellerOrder[]>) {
    const searchQuery = ref('');
    const statusFilter = ref<string>('all');
    const currentPage = ref(1);
    const perPage = ref(5);
    const expandedOrders = ref<Record<number, boolean>>({});

    const totalOrdersCount = computed(() => orders.value.length);

    const pendingCount = computed(
        () =>
            orders.value.filter((o) => o.status.toLowerCase() === 'pending')
                .length,
    );

    const processingCount = computed(
        () =>
            orders.value.filter(
                (o) => o.status.toLowerCase() === 'processing',
            ).length,
    );

    const packedCount = computed(
        () =>
            orders.value.filter((o) => o.status.toLowerCase() === 'packed')
                .length,
    );

    const shippedCount = computed(
        () =>
            orders.value.filter((o) => o.status.toLowerCase() === 'shipped')
                .length,
    );

    const completedCount = computed(
        () =>
            orders.value.filter((o) => o.status.toLowerCase() === 'completed')
                .length,
    );

    const totalRevenueSum = computed(() => {
        return orders.value
            .filter((o) => o.status.toLowerCase() !== 'cancelled')
            .reduce((sum, o) => sum + (o.total_num || 0), 0);
    });

    function formatRupiah(val: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(val);
    }

    const filteredOrders = computed(() => {
        let list = orders.value;

        if (statusFilter.value !== 'all') {
            list = list.filter(
                (o) => o.status.toLowerCase() === statusFilter.value,
            );
        }

        if (searchQuery.value.trim()) {
            const q = searchQuery.value.toLowerCase();
            list = list.filter(
                (o) =>
                    o.order_number.toLowerCase().includes(q) ||
                    o.customer_name.toLowerCase().includes(q) ||
                    o.customer_email.toLowerCase().includes(q),
            );
        }

        return list;
    });

    const totalPages = computed(() =>
        Math.max(1, Math.ceil(filteredOrders.value.length / perPage.value)),
    );

    const paginatedOrders = computed(() => {
        const start = (currentPage.value - 1) * perPage.value;

        return filteredOrders.value.slice(start, start + perPage.value);
    });

    const paginationStart = computed(() => {
        if (filteredOrders.value.length === 0) {
            return 0;
        }

        return (currentPage.value - 1) * perPage.value + 1;
    });

    const paginationEnd = computed(() => {
        return Math.min(
            currentPage.value * perPage.value,
            filteredOrders.value.length,
        );
    });

    function prevPage() {
        if (currentPage.value > 1) {
            currentPage.value--;
        }
    }

    function nextPage() {
        if (currentPage.value < totalPages.value) {
            currentPage.value++;
        }
    }

    function goToPage(p: number) {
        currentPage.value = p;
    }

    function toggleExpand(id: number) {
        expandedOrders.value[id] = !expandedOrders.value[id];
    }

    watch([searchQuery, statusFilter, perPage], () => {
        currentPage.value = 1;
    });

    function updateOrderStatus(
        orderId: number,
        newStatus: string,
        trackingNumber?: string,
    ) {
        router.patch(
            `/orders/${orderId}/status`,
            {
                status: newStatus,
                ...(trackingNumber
                    ? { tracking_number: trackingNumber }
                    : {}),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success(
                        `Status pesanan #${orderId} berhasil diubah menjadi ${newStatus.toUpperCase()}!`,
                    );
                },
                onError: () => {
                    toast.error('Gagal memperbarui status pesanan.');
                },
            },
        );
    }

    function confirmPayment(paymentId: number) {
        router.post(
            `/payments/${paymentId}/confirm`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Pembayaran dikonfirmasi (Paid).');
                },
                onError: () => {
                    toast.error('Gagal konfirmasi pembayaran.');
                },
            },
        );
    }

    return {
        searchQuery,
        statusFilter,
        currentPage,
        perPage,
        expandedOrders,
        totalOrdersCount,
        pendingCount,
        processingCount,
        packedCount,
        shippedCount,
        completedCount,
        totalRevenueSum,
        formatRupiah,
        filteredOrders,
        totalPages,
        paginatedOrders,
        paginationStart,
        paginationEnd,
        prevPage,
        nextPage,
        goToPage,
        toggleExpand,
        updateOrderStatus,
        confirmPayment,
        statusBadgeMap: ORDER_STATUS_BADGE,
    };
}
