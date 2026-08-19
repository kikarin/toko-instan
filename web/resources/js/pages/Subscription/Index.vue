<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Check, Crown, Loader2 } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { toast } from '@/components/ui/sonner';
import AppLayout from '@/layouts/AppLayout.vue';

interface PlanCard {
    code: string;
    name: string;
    price: number;
    price_label: string;
    withdraw_fee: number;
    settlement_mode: string;
    features: string[];
}

interface Props {
    subscription: {
        plan_code: string;
        plan_name: string;
        is_premium: boolean;
        settlement_mode: string;
        withdraw_fee: number;
        ends_at: string | null;
        status: string | null;
    };
    plans: PlanCard[];
    payment?: {
        id: number;
        status: string;
        snap_token?: string | null;
        redirect_url?: string | null;
        amount: number;
    } | null;
    midtransClientKey?: string | null;
    midtransSnapUrl?: string | null;
}

const props = defineProps<Props>();
const paying = ref(false);

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

function syncStatus() {
    router.post('/subscription/sync', {}, { preserveScroll: true });
}

async function payWithSnap() {
    if (!props.payment?.snap_token) {
        if (props.payment?.redirect_url) {
            window.location.href = props.payment.redirect_url;

            return;
        }

        toast.error('Token pembayaran tidak tersedia. Klik Upgrade lagi.');

        return;
    }

    paying.value = true;

    try {
        await loadSnap();
        (window as any).snap.pay(props.payment.snap_token, {
            onSuccess: () => {
                toast.success('Pembayaran Premium berhasil.');
                syncStatus();
            },
            onPending: () => toast.message('Menunggu pembayaran...'),
            onError: () => toast.error('Pembayaran gagal / dibatalkan.'),
        });
    } catch (e) {
        toast.error(e instanceof Error ? e.message : 'Gagal membuka pembayaran.');
    } finally {
        paying.value = false;
    }
}

function upgrade() {
    router.post('/subscription/upgrade', {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.message('Membuka pembayaran...');
        },
        onError: () => toast.error('Gagal memulai upgrade.'),
    });
}

onMounted(() => {
    if (props.payment?.snap_token && !props.subscription.is_premium) {
        payWithSnap();
    }
});
</script>

<template>
    <Head title="Langganan - Toko Instan" />

    <AppLayout title="Langganan" activePage="Langganan">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-5 p-4 sm:p-6">
            <div>
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                >
                    Paket Toko
                </p>
                <h1
                    class="flex items-center gap-2 text-2xl font-extrabold text-[#1c1c22]"
                >
                    <Crown class="h-6 w-6" /> Langganan
                </h1>
                <p class="mt-1 text-xs text-[#9090a0]">
                    Paket aktif:
                    <span class="font-bold text-[#1c1c22]">{{
                        subscription.plan_name
                    }}</span>
                    <template v-if="subscription.ends_at">
                        · berlaku sampai {{ subscription.ends_at }}
                    </template>
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card
                    v-for="plan in plans"
                    :key="plan.code"
                    class="flex flex-col gap-4 p-5"
                    :class="
                        subscription.plan_code === plan.code
                            ? 'border-foreground shadow-md'
                            : ''
                    "
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-lg font-black">{{ plan.name }}</p>
                            <p class="text-sm font-bold text-[#e07c28]">
                                {{ plan.price_label }}
                            </p>
                        </div>
                        <Badge
                            v-if="subscription.plan_code === plan.code"
                            class="bg-black text-accent"
                        >
                            Aktif
                        </Badge>
                    </div>
                    <ul class="flex flex-col gap-2 text-xs text-[#1c1c22]">
                        <li
                            v-for="feature in plan.features"
                            :key="feature"
                            class="flex items-start gap-2"
                        >
                            <Check class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            {{ feature }}
                        </li>
                    </ul>
                    <Button
                        v-if="plan.code === 'premium'"
                        class="mt-auto h-10 text-xs font-bold"
                        :disabled="paying"
                        @click="payment ? payWithSnap() : upgrade()"
                    >
                        <Loader2
                            v-if="paying"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{
                            subscription.is_premium
                                ? 'Perpanjang Premium'
                                : payment
                                  ? 'Lanjut Bayar'
                                  : 'Upgrade ke Premium'
                        }}
                    </Button>
                    <Button
                        v-if="plan.code === 'premium' && payment && !subscription.is_premium"
                        variant="outline"
                        class="h-10 text-xs font-bold"
                        @click="syncStatus"
                    >
                        Sudah bayar? Perbarui status
                    </Button>
                </Card>
            </div>
        </main>
    </AppLayout>
</template>
