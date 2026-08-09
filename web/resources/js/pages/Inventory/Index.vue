<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Boxes,
    History,
    PackageX,
    ArrowDownToLine,
    ArrowUpFromLine,
    SlidersHorizontal,
    Minus,
    Plus,
    AlertTriangle,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { useInventoryActions } from '@/lib/useInventoryActions';
import type { InventoryProduct } from '@/types/inventory';

interface Props {
    products?: InventoryProduct[];
    lowStockThreshold?: number;
    totalProducts?: number;
    lowStockCount?: number;
    outOfStockCount?: number;
}

defineProps<Props>();

const {
    activeAction,
    qty,
    newStock,
    reason,
    actionLabels,
    openAction,
    submit,
} = useInventoryActions();
</script>

<template>
    <Head title="Stok & Inventory — Toko Instan" />

    <AppLayout title="Stok & Inventory" activePage="Stok & Inventory">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 sm:p-6">
            <div>
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                >
                    Seller · Inventory
                </p>
                <h1
                    class="flex items-center gap-2 text-2xl font-extrabold text-[#1c1c22]"
                >
                    <Boxes class="h-6 w-6" /> Manajemen Stok
                </h1>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <Card class="p-4">
                    <p
                        class="text-[10px] font-bold tracking-wide text-[#9090a0] uppercase"
                    >
                        Total Produk
                    </p>
                    <p class="mt-1 text-2xl font-extrabold text-[#1c1c22]">
                        {{ totalProducts }}
                    </p>
                </Card>
                <Card class="p-4">
                    <p
                        class="text-[10px] font-bold tracking-wide text-[#9090a0] uppercase"
                    >
                        Stok Menipis
                    </p>
                    <p class="mt-1 text-2xl font-extrabold text-[#e07c28]">
                        {{ lowStockCount }}
                    </p>
                </Card>
                <Card class="p-4">
                    <p
                        class="text-[10px] font-bold tracking-wide text-[#9090a0] uppercase"
                    >
                        Habis
                    </p>
                    <p class="mt-1 text-2xl font-extrabold text-red-500">
                        {{ outOfStockCount }}
                    </p>
                </Card>
                <Card class="p-4">
                    <p
                        class="text-[10px] font-bold tracking-wide text-[#9090a0] uppercase"
                    >
                        Ambang Stok
                    </p>
                    <p class="mt-1 text-2xl font-extrabold text-[#6d4fc2]">
                        {{ lowStockThreshold }}
                    </p>
                </Card>
            </div>

            <!-- Product list -->
            <Card class="overflow-hidden py-0">
                <div class="flex flex-col">
                    <div
                        v-for="p in products ?? []"
                        :key="p.id"
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-black/5 px-4 py-3.5 last:border-b-0 sm:px-5"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <img
                                v-if="p.img"
                                :src="p.img"
                                :alt="p.name"
                                class="h-12 w-12 shrink-0 rounded-xl border border-black/5 object-cover"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#f5f4f0]"
                            >
                                <Boxes class="h-5 w-5 text-[#c8c8d5]" />
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-bold text-[#1c1c22]"
                                >
                                    {{ p.name }}
                                </p>
                                <p class="text-[10px] text-[#9090a0]">
                                    {{ p.sku || 'Tanpa SKU' }} •
                                    {{ p.formatted_price }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span v-if="p.stock === 0" class="mr-1">
                                <Badge
                                    variant="rose"
                                    class="px-2 py-0 text-[9px] uppercase"
                                    >Habis</Badge
                                >
                            </span>
                            <span v-else-if="p.low_stock" class="mr-1">
                                <Badge
                                    variant="amber"
                                    class="px-2 py-0 text-[9px] uppercase"
                                >
                                    <AlertTriangle class="h-2.5 w-2.5" />
                                    Menipis
                                </Badge>
                            </span>
                            <span
                                class="mr-2 font-mono text-sm font-extrabold text-[#1c1c22]"
                            >
                                {{ p.stock }}
                            </span>
                            <div class="flex gap-1.5">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-8 px-2.5 text-[11px]"
                                    @click="openAction(p, 'in')"
                                    :title="'Stok masuk ' + p.name"
                                >
                                    <ArrowDownToLine class="mr-1 h-3.5 w-3.5" />
                                    Masuk
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-8 px-2.5 text-[11px]"
                                    @click="openAction(p, 'out')"
                                    :title="'Stok keluar ' + p.name"
                                >
                                    <ArrowUpFromLine class="mr-1 h-3.5 w-3.5" />
                                    Keluar
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="h-8 px-2.5 text-[11px]"
                                    @click="openAction(p, 'adjust')"
                                    :title="'Sesuaikan stok ' + p.name"
                                >
                                    <SlidersHorizontal
                                        class="mr-1 h-3.5 w-3.5"
                                    />
                                    Sesuaikan
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-8 px-2 text-[11px] text-[#6d4fc2]"
                                    @click="
                                        router.visit(
                                            `/inventory/${p.id}/history`,
                                        )
                                    "
                                    :title="'Riwayat ' + p.name"
                                >
                                    <History class="mr-1 h-3.5 w-3.5" /> Riwayat
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!(products ?? []).length"
                        class="py-14 text-center"
                    >
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f5f4f0]"
                        >
                            <PackageX class="h-6 w-6 text-[#c8c8d5]" />
                        </div>
                        <p class="mt-3 text-sm font-semibold text-[#4a4a57]">
                            Belum ada produk
                        </p>
                        <p class="mt-1 text-xs text-[#9090a0]">
                            Tambahkan produk untuk mulai mengelola stok.
                        </p>
                    </div>
                </div>
            </Card>

            <!-- Action Modal -->
            <Teleport to="body">
                <div
                    v-if="activeAction"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                        @click="activeAction = null"
                    />
                    <div
                        class="relative z-10 w-full max-w-sm rounded-2xl border border-black/8 bg-white p-5 shadow-2xl"
                    >
                        <h2 class="text-sm font-extrabold text-[#1c1c22]">
                            {{ actionLabels[activeAction.action].title }}
                        </h2>
                        <p class="mt-1 text-xs text-[#9090a0]">
                            {{ activeAction.product.name }} — stok saat ini
                            <span class="font-bold text-[#1c1c22]">{{
                                activeAction.product.stock
                            }}</span>
                        </p>

                        <form
                            class="mt-4 flex flex-col gap-3"
                            @submit.prevent="submit"
                        >
                            <template v-if="activeAction.action === 'adjust'">
                                <div class="flex flex-col gap-1.5">
                                    <Label>Stok baru</Label>
                                    <Input
                                        v-model="newStock"
                                        type="number"
                                        min="0"
                                        required
                                    />
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex flex-col gap-1.5">
                                    <Label>Jumlah</Label>
                                    <div class="flex items-center gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            class="h-9 w-9"
                                            @click="
                                                qty = String(
                                                    Math.max(
                                                        1,
                                                        parseInt(qty || '1') -
                                                            1,
                                                    ),
                                                )
                                            "
                                        >
                                            <Minus class="h-4 w-4" />
                                        </Button>
                                        <Input
                                            v-model="qty"
                                            type="number"
                                            min="1"
                                            required
                                            class="text-center font-bold"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            class="h-9 w-9"
                                            @click="
                                                qty = String(
                                                    Math.max(
                                                        1,
                                                        parseInt(qty || '1') +
                                                            1,
                                                    ),
                                                )
                                            "
                                        >
                                            <Plus class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </template>

                            <div class="flex flex-col gap-1.5">
                                <Label>Alasan (opsional)</Label>
                                <Input
                                    v-model="reason"
                                    placeholder="cth: Restock dari distributor"
                                />
                            </div>

                            <div class="mt-2 flex justify-end gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="activeAction = null"
                                    >Batal</Button
                                >
                                <Button type="submit" size="sm">
                                    {{
                                        actionLabels[activeAction.action].button
                                    }}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </Teleport>
        </main>
    </AppLayout>
</template>
