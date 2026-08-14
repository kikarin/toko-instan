<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { FileSpreadsheet } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

interface ReportRow {
    order_number: string;
    date: string;
    customer: string;
    dpp: number;
    ppn: number;
    shipping: number;
    discount: number;
    total: number;
}

const props = defineProps<{
    report: {
        rows: ReportRow[];
        totals: { dpp: number; ppn: number; total: number };
        year: number;
        month: number | null;
    };
    store: { is_pkp: boolean; npwp?: string | null; tax_name?: string | null } | null;
    ppn_rate: number;
}>();

const year = ref(props.report.year);
const month = ref(props.report.month ? String(props.report.month) : '');

function fmt(n: number) {
    return 'Rp ' + n.toLocaleString('id-ID');
}

function reload() {
    router.get('/tax-reports', {
        year: year.value,
        month: month.value || undefined,
    }, { preserveState: true });
}

function exportUrl(format: string) {
    const q = new URLSearchParams({ year: String(year.value), format });
    if (month.value) {
        q.set('month', month.value);
    }

    return `/tax-reports/export?${q.toString()}`;
}
</script>

<template>
    <Head title="Laporan Pajak" />
    <AppLayout title="Laporan Pajak" activePage="Laporan Pajak">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-xl font-extrabold">Laporan Pajak</h1>
                    <p class="text-sm text-muted-foreground">
                        PPN {{ ppn_rate }}% ·
                        {{ store?.is_pkp ? 'Toko PKP' : 'Non-PKP (PPN 0)' }}
                        <span v-if="store?.npwp"> · NPWP {{ store.npwp }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <input v-model.number="year" type="number" class="h-10 w-24 rounded-md border px-2 text-sm" />
                    <select v-model="month" class="h-10 rounded-md border bg-background px-2 text-sm">
                        <option value="">Tahunan</option>
                        <option v-for="m in 12" :key="m" :value="String(m)">Bulan {{ m }}</option>
                    </select>
                    <Button variant="outline" @click="reload">Tampilkan</Button>
                    <Button as-child variant="outline">
                        <a :href="exportUrl('csv')">CSV</a>
                    </Button>
                    <Button as-child>
                        <a :href="exportUrl('xls')" class="inline-flex items-center gap-1">
                            <FileSpreadsheet class="h-4 w-4" /> Excel
                        </a>
                    </Button>
                </div>
            </div>

            <Card>
                <CardContent class="overflow-x-auto p-0">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b bg-muted/40">
                            <tr>
                                <th class="p-3">Order</th>
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Pelanggan</th>
                                <th class="p-3 text-right">DPP</th>
                                <th class="p-3 text-right">PPN</th>
                                <th class="p-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in report.rows" :key="row.order_number" class="border-b">
                                <td class="p-3 font-mono">{{ row.order_number }}</td>
                                <td class="p-3">{{ row.date }}</td>
                                <td class="p-3">{{ row.customer }}</td>
                                <td class="p-3 text-right font-mono">{{ fmt(row.dpp) }}</td>
                                <td class="p-3 text-right font-mono">{{ fmt(row.ppn) }}</td>
                                <td class="p-3 text-right font-mono">{{ fmt(row.total) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="font-bold">
                                <td class="p-3" colspan="3">Total</td>
                                <td class="p-3 text-right font-mono">{{ fmt(report.totals.dpp) }}</td>
                                <td class="p-3 text-right font-mono">{{ fmt(report.totals.ppn) }}</td>
                                <td class="p-3 text-right font-mono">{{ fmt(report.totals.total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    <p v-if="!report.rows.length" class="p-6 text-sm text-muted-foreground">Belum ada transaksi berstatus dibayar pada periode ini.</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
