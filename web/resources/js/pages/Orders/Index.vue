<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ReceiptText, Package, Inbox, Download } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useStoreName } from '@/composables/useStoreName';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import type { BuyerOrder } from '@/types/order';

interface Props {
    orders?: BuyerOrder[];
    storeSlug?: string;
}

const props = defineProps<Props>();

const { storeName } = useStoreName();

const statusVariant: Record<string, 'amber' | 'teal' | 'rose' | 'violetSolid'> =
    {
        pending: 'amber',
        paid: 'teal',
        processing: 'violetSolid',
        packed: 'violetSolid',
        shipped: 'teal',
        completed: 'teal',
        cancelled: 'rose',
    };

function openInvoice(orderNumber: string) {
    router.get(`/${props.storeSlug}/orders/${orderNumber}/invoice`);
}
</script>

<template>
    <Head :title="`Pesanan Saya — ${storeName}`" />

    <StorefrontLayout
        @open-cart="router.visit('/' + ((usePage().props.store as any)?.slug ?? ''))"
    >
        <div
            class="mx-auto flex w-full max-w-5xl flex-col gap-5 px-4 pt-4 pb-28 sm:p-6"
        >
            <div class="flex items-center gap-2.5">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand text-brand-foreground shadow-xs"
                >
                    <ReceiptText class="h-4.5 w-4.5" />
                </div>
                <div>
                    <h1
                        class="text-lg leading-none font-extrabold text-foreground"
                    >
                        Pesanan Saya
                    </h1>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">
                        Riwayat pesanan produk {{ storeName }} kamu
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <div
                    v-for="o in orders ?? []"
                    :key="o.order_number"
                    class="rounded-2xl border border-black/8 bg-white p-4 shadow-2xs"
                >
                    <!-- header -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-2 border-b border-border pb-3"
                    >
                        <div class="flex items-center gap-2">
                            <p
                                class="font-mono text-xs font-bold text-foreground"
                            >
                                {{ o.order_number }}
                            </p>
                            <Badge
                                :variant="
                                    statusVariant[o.status?.toLowerCase()] ??
                                    'amber'
                                "
                                class="px-2 py-0 text-[9px] uppercase"
                            >
                                {{ o.status }}
                            </Badge>
                        </div>
                        <p class="text-[10px] text-muted-foreground">
                            {{ o.store_name }} · {{ o.created_at ?? '-' }}
                        </p>
                        <p
                            v-if="o.tracking_number"
                            class="mt-1 font-mono text-[10px] font-bold text-brand"
                        >
                            Resi {{ o.shipping_courier ? o.shipping_courier + ' · ' : '' }}{{ o.tracking_number }}
                        </p>
                    </div>

                    <!-- items -->
                    <div class="flex flex-col gap-2 py-3">
                        <div
                            v-for="it in o.items ?? []"
                            :key="`${it.id ?? it.product_name}-${it.sku ?? ''}`"
                            class="flex items-center gap-2.5"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-muted/40"
                            >
                                <Package class="h-4 w-4 text-brand" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-xs font-semibold text-foreground"
                                >
                                    {{ it.product_name }}
                                </p>
                                <p
                                    v-if="it.sku"
                                    class="truncate font-mono text-[10px] text-muted-foreground"
                                >
                                    SKU {{ it.sku }}
                                </p>
                            </div>
                            <a
                                v-if="it.can_review && it.product_slug"
                                :href="`/${storeSlug}/p/${it.product_slug}`"
                                class="text-[10px] font-bold text-brand"
                            >
                                Ulas
                            </a>
                            <a
                                v-if="it.download_url"
                                :href="it.download_url"
                                class="inline-flex items-center gap-1 rounded-lg bg-brand/10 px-2 py-1 text-[10px] font-bold text-brand hover:bg-brand/15"
                            >
                                <Download class="h-3 w-3" /> Unduh
                            </a>
                            <p v-else class="text-xs text-muted-foreground">
                                {{ it.qty }}x
                            </p>
                        </div>
                        <p
                            v-if="!(o.items ?? []).length"
                            class="text-xs text-muted-foreground italic"
                        >
                            Tidak ada rincian item untuk pesanan ini.
                        </p>
                    </div>

                    <!-- tracking -->
                    <div
                        v-if="o.tracking_number"
                        class="mt-3 flex items-center justify-between rounded-xl border border-border bg-muted/30 px-3 py-2"
                    >
                        <div>
                            <p class="text-[10px] text-muted-foreground">
                                Nomor Resi ({{ o.tracking_courier }})
                            </p>
                            <p
                                class="font-mono text-xs font-bold text-foreground"
                            >
                                {{ o.tracking_number }}
                            </p>
                        </div>
                        <a
                            v-if="o.tracking_url"
                            :href="o.tracking_url"
                            target="_blank"
                            rel="noopener"
                        >
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-8 text-[10px] font-bold"
                            >
                                Lacak
                            </Button>
                        </a>
                    </div>

                    <!-- footer -->
                    <div
                        class="flex items-center justify-between border-t border-border pt-3"
                    >
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-[11px] font-bold text-brand"
                            @click="openInvoice(o.order_number)"
                        >
                            <ReceiptText class="mr-1 h-3.5 w-3.5" /> Invoice
                        </Button>
                        <div class="text-right">
                            <p
                                class="text-[9px] tracking-wide text-muted-foreground uppercase"
                            >
                                Total
                            </p>
                            <p
                                class="font-mono text-sm font-extrabold text-foreground"
                            >
                                {{ o.total_amount }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- empty state -->
                <div
                    v-if="!(orders ?? []).length"
                    class="flex flex-col items-center gap-2 py-14 text-center"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-border bg-muted/30"
                    >
                        <Inbox class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <p class="text-sm font-bold text-foreground">
                        Belum ada pesanan
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Pesanan yang kamu buat akan muncul di sini.
                    </p>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
