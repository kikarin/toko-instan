<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Gift } from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    summary: {
        code: string;
        url: string;
        clicks: number;
        signups: number;
        earned: number;
        commissions: Array<{ amount: number; status: string; created_at: string | null }>;
    } | null;
}>();
</script>

<template>
    <Head title="Referral" />
    <AppLayout title="Referral" activePage="Referral">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 sm:p-8">
            <h1 class="text-xl font-extrabold">Program referral</h1>
            <p class="text-sm text-muted-foreground">Bagikan kode. Komisi 5% (maks Rp 50.000) dari order berbayar seller yang daftar pakai kode Anda.</p>
            <template v-if="summary">
                <Card>
                    <CardContent class="flex flex-col gap-3 p-6">
                        <div class="flex items-center gap-2 text-sm font-bold"><Gift class="h-4 w-4" /> Kode: {{ summary.code }}</div>
                        <Input :model-value="summary.url" readonly />
                        <p class="text-xs text-muted-foreground">Klik {{ summary.clicks }} · Daftar {{ summary.signups }} · Komisi Rp {{ summary.earned.toLocaleString('id-ID') }}</p>
                    </CardContent>
                </Card>
                <Card v-for="(c, i) in summary.commissions" :key="i">
                    <CardContent class="p-4 text-sm">Rp {{ c.amount.toLocaleString('id-ID') }} · {{ c.status }} · {{ c.created_at }}</CardContent>
                </Card>
            </template>
        </div>
    </AppLayout>
</template>
