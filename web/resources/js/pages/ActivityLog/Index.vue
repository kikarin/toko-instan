<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Package,
    PackageX,
    TrendingDown,
    TrendingUp,
    ShoppingBag,
    Wallet,
    Store,
    ScrollText,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ActivityLogItem } from '@/types/activity';

interface Props {
    logs: ActivityLogItem[];
}

const props = defineProps<Props>();

function actionIcon(action: string) {
    switch (action) {
        case 'created':
            return Package;
        case 'deleted':
            return PackageX;
        case 'updated':
        case 'store_updated':
            return Store;
        case 'stock_in':
            return TrendingUp;
        case 'stock_out':
            return TrendingDown;
        case 'order_status':
            return ShoppingBag;
        case 'withdrawal_request':
        case 'withdrawal_status':
            return Wallet;
        default:
            return ScrollText;
    }
}

const entries = computed(() => props.logs ?? []);
</script>

<template>
    <Head title="Riwayat Aktivitas — Toko Instan" />

    <AppLayout title="Riwayat Aktivitas" activePage="Riwayat Aktivitas">
        <main class="mx-auto flex w-full max-w-3xl flex-col gap-5 p-4 sm:p-6">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-black text-accent"
                >
                    <ScrollText class="h-5 w-5" />
                </div>
                <div>
                    <p
                        class="text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                    >
                        Audit Trail
                    </p>
                    <h1
                        class="flex items-center gap-2 text-xl font-extrabold text-[#1c1c22]"
                    >
                        Riwayat Aktivitas
                    </h1>
                    <p class="text-[11px] text-[#9090a0]">
                        Catatan aksi di toko kamu — tidak bisa diubah atau
                        dihapus.
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-2.5">
                <div
                    v-for="log in entries"
                    :key="log.id"
                    class="flex items-start justify-between gap-3 rounded-2xl border border-border bg-white px-4 py-3 shadow-sm"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-muted text-muted-foreground"
                        >
                            <component :is="actionIcon(log.action)" class="h-4 w-4" />
                        </div>
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-1.5 text-xs font-bold text-[#1c1c22]">
                                {{ log.subject_type }}
                                <span
                                    v-if="log.subject_id"
                                    class="font-mono text-[10px] text-[#9090a0]"
                                >
                                    #{{ log.subject_id }}
                                </span>
                                <Badge
                                    class="bg-muted text-[9px] font-black text-muted-foreground uppercase"
                                >
                                    {{ log.action_label }}
                                </Badge>
                            </p>
                            <p class="mt-0.5 truncate text-[10px] text-[#9090a0]">
                                {{ log.user || 'Sistem' }}
                                <template v-if="log.ip"> • IP {{ log.ip }}</template>
                                <template v-if="log.created_at">
                                    • {{ log.created_at }} WIB
                                </template>
                            </p>
                            <p
                                v-if="log.properties?.changes"
                                class="mt-1 font-mono text-[10px] text-[#4a4a57]"
                            >
                                {{
                                    Object.entries(log.properties.changes)
                                        .map(([k, v]) => `${k}: ${JSON.stringify(v)}`)
                                        .join(', ')
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <Card v-if="!entries.length" class="py-14 text-center">
                    <CardContent>
                        <p class="text-sm font-semibold text-[#4a4a57]">
                            Belum ada aktivitas
                        </p>
                        <p class="mt-1 text-xs text-[#9090a0]">
                            Catatan aksi toko akan muncul di sini otomatis.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </main>
    </AppLayout>
</template>