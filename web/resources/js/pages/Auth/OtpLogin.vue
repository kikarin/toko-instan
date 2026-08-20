<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowRight, KeyRound, Loader2, Mail } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast, Toaster } from '@/components/ui/sonner';
import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{
    store?: any;
    intent?: 'platform' | 'storefront';
    status?: string | null;
}>();

const email = ref('');
const code = ref('');
const step = ref<'email' | 'code'>('email');
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);

const baseUrl = computed(() =>
    props.store ? `/${props.store.slug}/otp-login` : '/otp-login',
);
const loginUrl = computed(() =>
    props.store ? `/${props.store.slug}/login` : '/login',
);

function sendCode() {
    if (!email.value) {
        errorMessage.value = 'Silakan isi email Anda.';
        toast.error(errorMessage.value);

        return;
    }

    errorMessage.value = null;
    isLoading.value = true;

    router.post(
        baseUrl.value,
        { email: email.value },
        {
            onSuccess: () => {
                step.value = 'code';
                toast.success('Jika email terdaftar, kode OTP telah dikirim.');
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                const msg = errors.email || 'Gagal mengirim OTP.';
                errorMessage.value = String(msg);
                toast.error(String(msg));
            },
        },
    );
}

function verifyCode() {
    if (!code.value || code.value.length !== 6) {
        errorMessage.value = 'Masukkan kode OTP 6 digit.';
        toast.error(errorMessage.value);

        return;
    }

    errorMessage.value = null;
    isLoading.value = true;

    router.post(
        `${baseUrl.value}/verify`,
        { email: email.value, code: code.value },
        {
            onSuccess: () => {
                toast.success('Berhasil masuk!');
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                const msg =
                    errors.code || errors.email || 'Kode OTP tidak valid.';
                errorMessage.value = String(msg);
                toast.error(String(msg));
            },
        },
    );
}
</script>

<template>
    <Head title="Masuk dengan OTP - Toko Instan" />

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
                Masuk dengan OTP
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{
                    step === 'email'
                        ? 'Kami kirim kode sekali pakai ke email Anda'
                        : 'Masukkan kode 6 digit dari email'
                }}
            </p>
        </div>

        <div
            class="rounded-3xl border border-border bg-card p-6 shadow-xl shadow-black/5 md:p-8"
        >
            <div
                v-if="status && step === 'code'"
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

            <form
                v-if="step === 'email'"
                class="flex flex-col gap-5"
                @submit.prevent="sendCode"
            >
                <div>
                    <label class="mb-1.5 block text-xs font-bold text-foreground">
                        Email
                    </label>
                    <div class="relative">
                        <Mail
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            v-model="email"
                            type="email"
                            placeholder="nama@email.com"
                            required
                            class="auth-field"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="isLoading"
                    class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand text-sm font-bold text-brand-foreground shadow-lg shadow-brand/25 transition-all hover:brightness-110 active:scale-[0.99] disabled:pointer-events-none disabled:opacity-60"
                >
                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                    <template v-else>
                        <span>Kirim Kode OTP</span>
                        <ArrowRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </template>
                </button>
            </form>

            <form
                v-else
                class="flex flex-col gap-5"
                @submit.prevent="verifyCode"
            >
                <div>
                    <label class="mb-1.5 block text-xs font-bold text-foreground">
                        Kode OTP
                    </label>
                    <div class="relative">
                        <KeyRound
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            v-model="code"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            placeholder="123456"
                            required
                            class="auth-field tracking-[0.35em]"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="isLoading"
                    class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand text-sm font-bold text-brand-foreground shadow-lg shadow-brand/25 transition-all hover:brightness-110 active:scale-[0.99] disabled:pointer-events-none disabled:opacity-60"
                >
                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                    <template v-else>
                        <span>Verifikasi & Masuk</span>
                        <ArrowRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </template>
                </button>

                <button
                    type="button"
                    class="text-xs font-semibold text-brand hover:underline"
                    @click="sendCode"
                >
                    Kirim ulang kode
                </button>
            </form>
        </div>

        <div class="mt-6 text-center text-sm text-muted-foreground">
            Lebih suka kata sandi?
            <a
                :href="loginUrl"
                class="ml-1 font-bold text-brand hover:underline"
                @click.prevent="router.visit(loginUrl)"
            >
                Masuk biasa
            </a>
        </div>

        <Toaster position="top-right" />
    </AuthLayout>
</template>
