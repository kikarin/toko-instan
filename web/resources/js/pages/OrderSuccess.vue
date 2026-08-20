<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Store,
    Calendar,
    User,
    Mail,
    ShoppingBag,
    Loader2,
    UploadCloud,
    Paperclip,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { useStoreName } from '@/composables/useStoreName';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import type { OrderInvoice } from '@/types/order';

interface PaymentPayload {
    id: number;
    provider: string;
    method: string;
    status: string;
    snap_token?: string | null;
    redirect_url?: string | null;
    proof_path?: string | null;
    proof_name?: string | null;
}

interface Props {
    invoice: OrderInvoice;
    payment?: PaymentPayload | null;
    midtransClientKey?: string | null;
    midtransSnapUrl?: string | null;
}

const props = defineProps<Props>();

const { storeName } = useStoreName();

const page = usePage();
const isGuest = computed(() => !(page.props.auth as any)?.user);
const storeSlug = computed(() => (page.props.store as any)?.slug ?? '');

const proofInput = ref<HTMLInputElement | null>(null);
const uploadingProof = ref(false);

function navigate(url: string) {
    router.visit(url);
}

function loadSnap(): Promise<void> {
    return new Promise((resolve, reject) => {
        if ((window as any).snap) {
            resolve();

            return;
        }

        if (!props.midtransClientKey || !props.midtransSnapUrl) {
            reject(new Error('Midtrans client key belum dikonfigurasi.'));

            return;
        }

        const script = document.createElement('script');
        script.src = props.midtransSnapUrl;
        script.setAttribute('data-client-key', props.midtransClientKey);
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Gagal memuat Midtrans Snap.'));
        document.body.appendChild(script);
    });
}

async function payWithSnap() {
    if (!props.payment?.snap_token) {
        if (props.payment?.redirect_url) {
            window.location.href = props.payment.redirect_url;

            return;
        }

        toast.error('Token pembayaran tidak tersedia. Silakan buat pesanan ulang.');

        return;
    }

    try {
        await loadSnap();
        (window as any).snap.pay(props.payment.snap_token, {
            onSuccess: () => {
                toast.success('Pembayaran berhasil.');
                syncPaymentStatus();
            },
            onPending: () => toast.message('Menunggu pembayaran...'),
            onError: () => toast.error('Pembayaran gagal / dibatalkan.'),
        });
    } catch (e) {
        toast.error(e instanceof Error ? e.message : 'Gagal membuka pembayaran.');
    }
}

function syncPaymentStatus() {
    router.post(`/${storeSlug.value}/orders/${props.invoice.order_number}/sync-payment`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Status pembayaran diperbarui.'),
        onError: () => toast.error('Gagal memperbarui status pembayaran.'),
    });
}

function uploadProof(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploadingProof.value = true;

    const form = new FormData();
    form.append('proof', file);

    router.post(`/${storeSlug.value}/orders/${props.invoice.order_number}/payment-proof`, form, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => toast.success('Bukti transfer berhasil diunggah. Menunggu konfirmasi seller.'),
        onError: () => toast.error('Gagal mengunggah bukti transfer.'),
        onFinish: () => {
            uploadingProof.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`Pesanan Berhasil — ${storeName}`" />

    <StorefrontLayout>
        <main
            class="mx-auto flex w-full max-w-2xl flex-col items-center px-4 pt-6 pb-28 font-sans sm:p-6 sm:py-12"
        >
            <!-- Success Icon Animation (Mengikuti warna brand/tema) -->
            <div
                class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-brand/30 bg-brand-soft text-brand shadow-md shadow-brand/10"
            >
                <CheckCircle2 class="h-8 w-8" />
            </div>

            <h1
                class="text-center text-2xl font-black tracking-tight text-foreground"
            >
                Pesanan {{ storeName }} Berhasil Dibuat!
            </h1>
            <p class="mt-1 mb-8 max-w-md text-center text-xs text-muted-foreground">
                Terima kasih telah berbelanja di {{ storeName }}. Pesanan
                Anda sedang diproses dengan garansi 100% keaslian.
                <span v-if="isGuest" class="mt-2 block">
                    Link lacak pesanan juga dikirim ke email Anda — gunakan
                    tombol <strong>Cek Pesanan</strong> di header jika perlu
                    membuka status lagi nanti.
                </span>
            </p>

            <!-- Invoice Card -->
            <Card
                class="flex w-full flex-col gap-5 rounded-2xl border-black/10 bg-white p-6 shadow-md"
            >
                <div
                    class="flex items-center justify-between border-b border-black/8 pb-4"
                >
                    <div>
                        <p
                            class="text-[10px] font-bold tracking-wider text-[#9090a0] uppercase"
                        >
                            Nomor Invoice
                        </p>
                        <p
                            class="mt-0.5 font-mono text-base font-extrabold text-[#1c1c22]"
                        >
                            {{ invoice.order_number }}
                        </p>
                    </div>
                    <!-- Badge Status PENDING (Menyesuaikan aksen tema atau warna brand) -->
                    <Badge
                        variant="amber"
                        class="border-none bg-black px-2.5 py-1 text-xs font-bold text-amber-400 uppercase"
                    >
                        {{ invoice.status }}
                    </Badge>
                </div>

                <!-- Payment action section -->
                <div v-if="payment" class="p-5">
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <span class="inline-flex h-2 w-2 rounded-full"
                            :class="{
                                'bg-emerald-500': payment.status === 'paid',
                                'bg-primary animate-pulse': payment.status === 'pending',
                                'bg-destructive': payment.status === 'failed',
                            }"
                        />
                        <span class="font-semibold uppercase tracking-wide">
                            {{ payment.method }} · {{ payment.status }}
                        </span>
                    </div>

                    <!-- Midtrans pending -->
                    <div
                        v-if="payment.provider === 'midtrans' && payment.status === 'pending'"
                        class="mt-4 flex flex-col gap-2.5"
                    >
                        <button
                            class="group relative flex h-12 w-full items-center justify-center gap-2.5 overflow-hidden rounded-xl bg-foreground px-5 text-sm font-bold text-background shadow-md transition-all hover:shadow-lg active:scale-[0.98]"
                            @click="payWithSnap"
                        >
                            <span class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent opacity-0 transition-opacity group-hover:opacity-100" />
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                            Bayar Sekarang (Midtrans)
                        </button>
                        <button
                            class="flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 text-xs font-semibold text-card-foreground transition-all hover:bg-muted active:scale-[0.98]"
                            @click="syncPaymentStatus"
                        >
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Sudah bayar? Perbarui status
                        </button>
                    </div>

                    <!-- Manual transfer -->
                    <div
                        v-else-if="payment.provider === 'manual' && payment.status !== 'paid'"
                        class="mt-4 flex flex-col gap-2.5"
                    >
                        <input
                            ref="proofInput"
                            type="file"
                            class="hidden"
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            @change="uploadProof"
                        />
                        <button
                            class="flex h-12 w-full items-center justify-center gap-2.5 rounded-xl border border-border bg-card px-5 text-sm font-semibold text-card-foreground transition-all hover:bg-muted disabled:opacity-50 active:scale-[0.98]"
                            :disabled="uploadingProof"
                            @click="proofInput?.click()"
                        >
                            <Loader2 v-if="uploadingProof" class="h-4 w-4 animate-spin" />
                            <UploadCloud v-else class="h-4 w-4 shrink-0" />
                            {{ payment.proof_name ? 'Ganti Bukti Transfer' : 'Upload Bukti Transfer' }}
                        </button>
                        <p v-if="payment.proof_name" class="text-center text-[11px] text-muted-foreground">
                            <Paperclip class="inline h-3 w-3" /> {{ payment.proof_name }}
                        </p>
                    </div>

                    <!-- COD -->
                    <p v-else-if="payment.provider === 'cod'" class="mt-3 text-xs text-muted-foreground">
                        Siapkan uang pas. Seller akan konfirmasi setelah COD diterima.
                    </p>
                </div>

                <div v-if="payment" class="mx-5 border-t border-border" />

                <!-- Info grid -->
                <div class="grid grid-cols-2 gap-x-4 gap-y-4 p-5 text-xs">
                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Store class="h-3.5 w-3.5 text-black" /> Toko
                            Penjual
                        </span>
                        <span class="font-bold text-[#1c1c22]">{{
                            invoice.store_name
                        }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Calendar class="h-3.5 w-3.5 text-black" />
                            Waktu Transaksi
                        </span>
                        <span class="font-mono font-bold text-[#1c1c22]">{{
                            invoice.created_at
                        }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <User class="h-3.5 w-3.5 text-black" /> Nama Pembeli
                        </span>
                        <span class="font-bold text-[#1c1c22]">{{
                            invoice.customer_name
                        }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="flex items-center gap-1.5 text-[#9090a0]">
                            <Mail class="h-3.5 w-3.5 text-black" /> Email
                            Pembeli
                        </span>
                        <span class="truncate font-bold text-[#1c1c22]">{{
                            invoice.customer_email
                        }}</span>
                    </div>
                </div>

                <!-- Total Amount Banner -->
                <div
                    class="mt-2 flex items-center justify-between rounded-2xl border border-black/10 bg-zinc-900 p-4 text-white"
                >
                    <span class="text-xs font-bold text-zinc-300"
                        >Total Pembayaran</span
                    >
                    <span class="font-mono text-xl font-black text-amber-400">
                        {{ invoice.total_amount }}
                    </span>
                </div>

                <!-- Tracking Info -->
                <div
                    v-if="invoice.tracking_number"
                    class="mt-2 flex flex-col gap-2 rounded-2xl border border-border bg-card p-4"
                >
                    <p class="text-xs font-bold text-muted-foreground">
                        Nomor Resi Pengiriman
                    </p>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-mono text-base font-black text-foreground">
                                {{ invoice.tracking_number }}
                            </p>
                            <p class="text-[10px] text-muted-foreground">
                                {{ invoice.tracking_courier }} · Dikirim pada
                                {{ invoice.shipped_at }}
                            </p>
                        </div>
                        <a
                            v-if="invoice.tracking_url"
                            :href="invoice.tracking_url"
                            target="_blank"
                            rel="noopener"
                        >
                            <Button
                                variant="outline"
                                class="h-10 border-black/12 text-xs font-bold"
                            >
                                Lacak Paket
                            </Button>
                        </a>
                    </div>
                </div>

                <!-- Buttons (Tombol Utama menggunakan warna Brand, Tombol Kedua menggunakan Outline konsisten) -->
                <div class="mt-2 flex flex-col gap-3">
                    <Button
                        variant="amber"
                        class="flex h-11 w-full items-center justify-center gap-2 bg-black text-xs font-bold text-amber-400 shadow-md hover:bg-zinc-800"
                        @click="navigate('/' + (usePage().props.store as any)?.slug + '/orders')"
                    >
                        <ShoppingBag class="h-4 w-4" />
                        <span>Lihat Riwayat Pesanan Saya</span>
                    </Button>
                    <Button
                        variant="outline"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-2xl border-border text-xs font-bold text-foreground hover:bg-muted"
                        @click="navigate('/' + (usePage().props.store as any)?.slug)"
                    >
                        Kembali Belanja
                    </button>
                </div>
            </Card>
        </main>
    </StorefrontLayout>
</template>
