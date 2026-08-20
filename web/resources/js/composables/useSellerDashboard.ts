import { router } from '@inertiajs/vue3';
import {
    DollarSign,
    Landmark,
    Package,
    Receipt,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { ComputedRef } from 'vue';

export type DashboardPeriod = 'Hari' | 'Minggu' | 'Bulan';

interface DashboardProps {
    kpis?: any[];
    orderFlow?: any[];
    topSellers?: any[];
    wallet?: any;
    store?: any;
    charts?: Record<
        DashboardPeriod,
        {
            labels: string[];
            revenue: { i: number; v: number }[];
            orders: number[];
            visitors: number[];
        }
    >;
}

export function useSellerDashboard(props: ComputedRef<DashboardProps>) {
    const period = ref<DashboardPeriod>('Bulan');

    const revSeries = computed<Record<DashboardPeriod, { i: number; v: number }[]>>(() => ({
        Hari: props.value.charts?.Hari?.revenue ?? [],
        Minggu: props.value.charts?.Minggu?.revenue ?? [],
        Bulan: props.value.charts?.Bulan?.revenue ?? [],
    }));

    const revLabels = computed<Record<DashboardPeriod, string[]>>(() => ({
        Hari: props.value.charts?.Hari?.labels ?? [],
        Minggu: props.value.charts?.Minggu?.labels ?? [],
        Bulan: props.value.charts?.Bulan?.labels ?? [],
    }));

    const kpiIcons = [
        DollarSign,
        Package,
        Users,
        TrendingUp,
        Receipt,
        Landmark,
    ];

    const displayKpis = computed(() =>
        (props.value.kpis ?? []).map((k, i) => ({
            ...k,
            icon: kpiIcons[i % kpiIcons.length],
        })),
    );

    const displayOrderFlow = computed(() => props.value.orderFlow ?? []);

    const displayTopSellers = computed(() => props.value.topSellers ?? []);

    const totalOrdersThisMonth = computed(() =>
        displayOrderFlow.value.reduce((s, d) => s + d.n, 0),
    );

    function navigate(url: string) {
        router.visit(url);
    }

    return {
        period,
        revSeries,
        revLabels,
        displayKpis,
        displayOrderFlow,
        displayTopSellers,
        totalOrdersThisMonth,
        navigate,
    };
}