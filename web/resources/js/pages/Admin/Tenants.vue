<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface TenantRow {
    id: number;
    name: string;
    slug: string;
    plan: string | null;
    status: string | null;
    owner: string | null;
    owner_email: string | null;
    store_name: string | null;
    store_slug: string | null;
    store_active: boolean;
    created_at: string | null;
}

defineProps<{
    tenants?: TenantRow[];
}>();
</script>

<template>
    <Head title="Tenants - Admin" />

    <AdminLayout activePage="Tenants">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6">
            <div>
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#6d4fc2] uppercase"
                >
                    Admin
                </p>
                <h1 class="text-2xl font-extrabold text-[#1c1c22]">Tenants</h1>
            </div>

            <Card class="overflow-hidden p-0">
                <div
                    class="grid grid-cols-12 gap-2 border-b border-black/5 bg-[#faf9f6] px-4 py-3 text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                >
                    <div class="col-span-3">Tenant</div>
                    <div class="col-span-3">Owner</div>
                    <div class="col-span-3">Toko</div>
                    <div class="col-span-1">Plan</div>
                    <div class="col-span-2">Status</div>
                </div>
                <div
                    v-for="t in tenants ?? []"
                    :key="t.id"
                    class="grid grid-cols-12 items-center gap-2 border-b border-black/5 px-4 py-3 text-xs last:border-0"
                >
                    <div class="col-span-3 min-w-0">
                        <p class="truncate font-bold text-[#1c1c22]">
                            {{ t.name }}
                        </p>
                        <p class="truncate font-mono text-[10px] text-[#9090a0]">
                            {{ t.slug }}
                        </p>
                    </div>
                    <div class="col-span-3 min-w-0">
                        <p class="truncate text-[#1c1c22]">{{ t.owner ?? '—' }}</p>
                        <p class="truncate text-[10px] text-[#9090a0]">
                            {{ t.owner_email }}
                        </p>
                    </div>
                    <div class="col-span-3 min-w-0">
                        <p class="truncate font-semibold text-[#1c1c22]">
                            {{ t.store_name ?? '—' }}
                        </p>
                        <Link
                            v-if="t.store_slug"
                            :href="`/${t.store_slug}`"
                            class="font-mono text-[10px] text-[#e07c28] hover:underline"
                        >
                            /{{ t.store_slug }}
                        </Link>
                    </div>
                    <div class="col-span-1">
                        <Badge variant="outline" class="text-[9px] uppercase">{{
                            t.plan ?? 'free'
                        }}</Badge>
                    </div>
                    <div class="col-span-2 flex flex-wrap gap-1">
                        <Badge
                            :variant="t.store_active ? 'teal' : 'rose'"
                            class="text-[9px]"
                        >
                            {{ t.store_active ? 'Toko aktif' : 'Toko tutup' }}
                        </Badge>
                    </div>
                </div>
                <p
                    v-if="!(tenants ?? []).length"
                    class="px-4 py-8 text-center text-xs text-[#9090a0]"
                >
                    Belum ada tenant.
                </p>
            </Card>
        </main>
    </AdminLayout>
</template>
