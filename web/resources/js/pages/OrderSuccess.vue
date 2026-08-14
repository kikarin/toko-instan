<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Store,
    Calendar,
    User,
    Mail,
    ShoppingBag,
    UploadCloud,
    Loader2,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { toast } from '@/components/ui/sonner';
import { useStoreName } from '@/composables/useStoreName';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import type { OrderInvoice } from '@/types/order';

interface PaymentInfo {
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
    payment?: PaymentInfo | null;
    storeSlug?: string;
    midtransClientKey?: string | null;
    midtransSnapUrl?: string | null;
}

const props = defineProps<Props>();
const { storeName } = useStoreName();
const uploadingProof = ref(false);
const proofInput = ref<HTMLInputElement | null>(null);

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

        toast.error('Token pembayaran tidak tersedia.');

        return;
    }

    try {
        await loadSnap();
        (window as any).snap.pay(props.payment.snap_token, {
            onSuccess: () => {
                toast.success('Pembayaran berhasil!');
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
    if (!props.storeSlug) {
        router.reload();

        return;
    }

    router.post(
        `/${props.storeSlug}/orders/${props.invoice.order_number}/sync-payment`,
        {},
        {
            preserveScroll: true,
            onError: () => router.reload(),
        },
    );
}

function uploadProof(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file || !props.storeSlug) {
        return;
    }

    uploadingProof.value = true;
    const form = new FormData();
    form.append('proof', file);

    router.post(
        `/${props.storeSlug}/orders/${props.invoice.order_number}/payment-proof`,
        form,
        {
            forceFormData: true,
            onSuccess: () => toast.success('Bukti transfer terkirim.'),
            onError: () => toast.error('Gagal upload bukti transfer.'),
            onFinish: () => {
                uploadingProof.value = false;
                target.value = '';
            },
        },
    );
}

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const gatewayStatus = params.get('transaction_status') ?? params.get('status');
    const alreadySettled = ['settlement', 'capture'].includes(gatewayStatus ?? '');

    if (
        props.payment?.provider === 'midtrans' &&
        props.payment.status === 'pending' &&
        alreadySettled
    ) {
        syncPaymentStatus();

        return;
    }

    if (
        props.payment?.provider === 'midtrans' &&
        props.payment.status === 'pending' &&
        props.payment.snap_token &&
        !alreadySettled
    ) {
        payWithSnap();
    }
});
</script>

<template>
    <Head :title="`Pesanan Berhasil — ${storeName}`" />

    <StorefrontLayout>
        <main
            class="mx-auto flex w-full max-w-2xl flex-col items-center px-4 pt-6 pb-28 font-sans sm:p-6 sm:py-12"
        >
            <div
                class="mb-4 flex h-16 w-16 items-center justify-center rounded-3xl border border-emerald-500/30 bg-emerald-50 text-emerald-600 shadow-md shadow-emerald-500/10"
            >
                <CheckCircle2 class="h-8 w-8" />
            </div>

            <h1
                class="text-center text-2xl font-black tracking-tight text-[#1c1c22]"
            >
                Pesanan {{ storeName }} Berhasil Dibuat!
            </h1>
            <p class="mt-1 mb-8 max-w-md text-center text-xs text-[#9090a0]">
                {{
                    payment?.provider === 'midtrans'
                        ? 'Selesaikan pembayaran melalui Midtrans untuk mengaktifkan pesanan.'
                        : payment?.provider === 'manual'
                          ? 'Transfer sesuai total, lalu upload bukti pembayaran.'
                          : payment?.provider === 'cod'
                            ? 'Pesanan COD. Bayar ke kurir saat barang sampai.'
                            : 'Terima kasih telah berbelanja.'
                }}
            </p>

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
                    <Badge
                        variant="default"
                        class="border-none bg-black px-2.5 py-1 text-xs font-bold text-accent uppercase"
                    >
                        {{ invoice.status }}
                    </Badge>
                </div>

                <div
                    v-if="payment"
                    class="rounded-xl border border-border bg-muted/30 p-4 text-xs"
                >
                    <p class="font-bold text-foreground">
                        Pembayaran:
                        {{ payment.method.toUpperCase() }} ·
                        {{ payment.status }}
                    </p>

                    <div
                        v-if="payment.provider === 'midtrans' && payment.status === 'pending'"
                        class="mt-3"
                    >
                        <Button
                            class="h-10 w-full text-xs font-bold"
                            @click="payWithSnap"
                        >
                            Bayar Sekarang (Midtrans)
                        </Button>
                        <Button
                            variant="outline"
                            class="mt-2 h-10 w-full text-xs font-bold"
                            @click="syncPaymentStatus"
                        >
                            Sudah bayar? Perbarui status
                        </Button>
                    </div>

                    <div
                        v-else-if="payment.provider === 'manual' && payment.status !== 'paid'"
                        class="mt-3 flex flex-col gap-2"
                    >
                        <input
                            ref="proofInput"
                            type="file"
                            class="hidden"
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            @change="uploadProof"
                        />
                        <Button
                            variant="outline"
                            class="h-10 w-full text-xs font-bold"
                            :disabled="uploadingProof"
                            @click="proofInput?.click()"
                        >
                            <Loader2
                                v-if="uploadingProof"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <UploadCloud v-else class="mr-2 h-4 w-4" />
                            {{
                                payment.proof_name
                                    ? 'Ganti Bukti Transfer'
                                    : 'Upload Bukti Transfer'
                            }}
                        </Button>
                        <p
                            v-if="payment.proof_name"
                            class="text-[11px] text-muted-foreground"
                        >
                            File: {{ payment.proof_name }}
                        </p>
                    </div>

                    <p
                        v-else-if="payment.provider === 'cod'"
                        class="mt-2 text-muted-foreground"
                    >
                        Siapkan uang pas. Seller akan konfirmasi setelah COD
                        diterima.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs">
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

                <div
                    class="mt-2 flex items-center justify-between rounded-2xl border border-border bg-card p-4 text-card-foreground"
                >
                    <span class="text-xs font-bold text-muted-foreground"
                        >Total Pembayaran</span
                    >
                    <span class="font-mono text-xl font-black text-accent">
                        {{ invoice.total_amount }}
                    </span>
                </div>

                <div class="mt-2 flex flex-col items-center gap-3 sm:flex-row">
                    <Button
                        variant="default"
                        class="flex h-11 w-full items-center justify-center gap-2 bg-foreground text-xs font-bold text-accent shadow-md hover:bg-foreground/90"
                        @click="
                            navigate(
                                '/' +
                                    ((usePage().props.store as any)?.slug ??
                                        storeSlug) +
                                    '/orders',
                            )
                        "
                    >
                        <ShoppingBag class="h-4 w-4" />
                        <span>Lihat Riwayat Pesanan Saya</span>
                    </Button>
                    <Button
                        variant="outline"
                        class="flex h-11 w-full items-center justify-center gap-2 border-black/12 text-xs font-bold"
                        @click="
                            navigate(
                                '/' +
                                    ((usePage().props.store as any)?.slug ??
                                        storeSlug),
                            )
                        "
                    >
                        <span>Kembali Belanja</span>
                    </Button>
                </div>
            </Card>
        </main>
    </StorefrontLayout>
</template>
