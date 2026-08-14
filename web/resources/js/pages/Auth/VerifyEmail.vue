<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { AlertCircle, Loader2, Mail, RefreshCw } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast, Toaster } from '@/components/ui/sonner';
import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{
    status?: string | null;
    email?: string | null;
}>();

const page = usePage();
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);

const email = computed(
    () => props.email ?? (page.props.auth as any)?.user?.email ?? '',
);

function resend() {
    errorMessage.value = null;
    isLoading.value = true;

    router.post(
        '/email/verification-notification',
        {},
        {
            onSuccess: () => {
                toast.success('Link verifikasi dikirim ulang.');
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: () => {
                errorMessage.value = 'Gagal mengirim ulang. Coba lagi.';
                toast.error(errorMessage.value);
            },
        },
    );
}
</script>

<template>
    <Head title="Verifikasi Email - Toko Instan" />

    <AuthLayout>
        <div class="mb-7 flex flex-col items-center text-center">
            <div
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand/70 text-xl font-extrabold text-brand-foreground shadow-lg ring-1 shadow-brand/30 ring-white/20"
            >
                S
            </div>
            <h1
                class="text-[26px] leading-tight font-extrabold tracking-tight text-foreground"
            >
                Verifikasi Email
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                Kami telah mengirim link verifikasi ke
                <span class="font-semibold text-foreground">{{ email }}</span>
            </p>
        </div>

        <div
            class="rounded-3xl border border-border bg-card p-6 shadow-xl shadow-black/5 md:p-8"
        >
            <div
                v-if="status"
                class="mb-5 rounded-xl border border-brand/20 bg-brand/10 p-3 text-xs font-medium text-brand"
            >
                {{ status }}
            </div>

            <div
                v-if="errorMessage"
                class="mb-5 flex items-start gap-2.5 rounded-xl border border-destructive/20 bg-destructive/10 p-3 text-xs font-medium text-destructive"
            >
                <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <p class="mb-6 text-sm leading-relaxed text-muted-foreground">
                Buka email Anda dan klik tautan verifikasi. Setelah terverifikasi,
                Anda bisa mengakses dashboard dan fitur lengkap.
            </p>

            <button
                type="button"
                :disabled="isLoading"
                class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand text-sm font-bold text-brand-foreground shadow-lg shadow-brand/25 transition-all hover:brightness-110 active:scale-[0.99] disabled:pointer-events-none disabled:opacity-60"
                @click="resend"
            >
                <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                <template v-else>
                    <RefreshCw class="h-4 w-4" />
                    <span>Kirim Ulang Link</span>
                </template>
            </button>

            <button
                type="button"
                class="mt-3 flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-border text-sm font-semibold text-foreground transition-colors hover:bg-muted/60"
                @click="router.post('/logout')"
            >
                <Mail class="h-4 w-4 text-muted-foreground" />
                Keluar & ganti akun
            </button>
        </div>
        <Toaster position="top-right" />
    </AuthLayout>
</template>
