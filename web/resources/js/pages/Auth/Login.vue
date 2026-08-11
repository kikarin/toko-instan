<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    Eye,
    EyeOff,
    Loader2,
    Lock,
    Mail,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast, Toaster } from '@/components/ui/sonner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { signInWithGooglePopup } from '@/lib/firebase';

const props = defineProps<{
    store?: any;
    intent?: 'platform' | 'storefront';
}>();

const isStorefront = computed(() => props.intent === 'storefront');

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const isLoading = ref(false);
const isGoogleLoading = ref(false);
const errorMessage = ref<string | null>(null);

const loginUrl = computed(() =>
    props.store ? `/${props.store.slug}/login` : '/login',
);
const forgotUrl = computed(() =>
    props.store ? `/${props.store.slug}/forgot-password` : '/forgot-password',
);
const registerUrl = computed(() =>
    props.store ? `/${props.store.slug}/register` : '/register',
);

async function handleEmailLogin() {
    if (!email.value || !password.value) {
        errorMessage.value = 'Silakan isi email dan kata sandi Anda.';
        toast.error('Silakan isi email dan kata sandi Anda.');

        return;
    }

    errorMessage.value = null;
    isLoading.value = true;

    router.post(
        loginUrl.value,
        {
            email: email.value,
            password: password.value,
        },
        {
            onSuccess: () => {
                toast.success('Berhasil masuk ke Dashboard!');
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                const msg =
                    errors.email ||
                    errors.password ||
                    'Gagal masuk. Periksa kredensial Anda.';
                errorMessage.value = msg;
                toast.error(msg);
            },
        },
    );
}

async function handleGoogleLogin() {
    errorMessage.value = null;
    isGoogleLoading.value = true;

    const { idToken, error } = await signInWithGooglePopup();

    if (error || !idToken) {
        isGoogleLoading.value = false;
        errorMessage.value = error ?? 'Gagal masuk menggunakan Google.';
        toast.error(errorMessage.value);

        return;
    }

    router.post(
        '/auth/google',
        {
            id_token: idToken,
            intent: 'login',
            store_slug_context: isStorefront.value
                ? (props.store?.slug ?? null)
                : null,
        },
        {
            onSuccess: () => {
                toast.success('Berhasil masuk!');
            },
            onFinish: () => {
                isGoogleLoading.value = false;
            },
            onError: (errors) => {
                const msg =
                    errors.id_token ||
                    Object.values(errors)[0] ||
                    'Gagal masuk dengan Google.';
                errorMessage.value = String(msg);
                toast.error(String(msg));
            },
        },
    );
}
</script>

<template>
    <Head title="Masuk - Toko Instan" />

    <AuthLayout>
        <!-- Header -->
        <div class="mb-7 flex flex-col items-center text-center">
            <div
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand/70 text-xl font-extrabold text-brand-foreground shadow-lg ring-1 shadow-brand/30 ring-white/20"
            >
                S
            </div>
            <h1
                class="text-[26px] leading-tight font-extrabold tracking-tight text-foreground"
            >
                {{
                    props.store
                        ? `Masuk ke ${props.store.name}`
                        : 'Selamat Datang Kembali'
                }}
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{
                    props.store
                        ? `Silakan masuk untuk belanja di ${props.store.name}`
                        : 'Masuk untuk melanjutkan ke dashboard Toko Instan Anda'
                }}
            </p>
        </div>

        <!-- Panel -->
        <div
            class="rounded-3xl border border-border bg-card p-6 shadow-xl shadow-black/5 md:p-8"
        >
            <!-- Error Alert -->
            <div
                v-if="errorMessage"
                class="mb-5 flex items-start gap-2.5 rounded-xl border border-destructive/20 bg-destructive/10 p-3 text-xs font-medium text-destructive"
            >
                <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <!-- Google Sign In -->
            <button
                type="button"
                @click="handleGoogleLogin"
                :disabled="isGoogleLoading || isLoading"
                class="flex h-11 w-full items-center justify-center gap-2.5 rounded-xl border border-border bg-card text-sm font-semibold text-foreground shadow-xs transition-all hover:bg-muted/60 hover:shadow-sm active:scale-[0.99] disabled:pointer-events-none disabled:opacity-50"
            >
                <Loader2
                    v-if="isGoogleLoading"
                    class="h-4 w-4 animate-spin text-brand"
                />
                <template v-else>
                    <svg class="h-4 w-4" viewBox="0 0 24 24">
                        <path
                            fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        />
                        <path
                            fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        />
                        <path
                            fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"
                        />
                        <path
                            fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"
                        />
                    </svg>
                    <span>Masuk dengan Google</span>
                </template>
            </button>

            <!-- Divider -->
            <div class="relative my-6 flex items-center justify-center">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-border" />
                </div>
                <span
                    class="relative bg-card px-3 text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    atau gunakan email
                </span>
            </div>

            <!-- Email & Password Form -->
            <form
                @submit.prevent="handleEmailLogin"
                class="flex flex-col gap-5"
            >
                <div>
                    <label
                        class="mb-1.5 block text-xs font-bold text-foreground"
                    >
                        Email
                    </label>
                    <div class="relative">
                        <Mail
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            v-model="email"
                            type="email"
                            placeholder="nama@tokomu.com"
                            required
                            class="auth-field"
                        />
                    </div>
                </div>

                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="text-xs font-bold text-foreground">
                            Kata Sandi
                        </label>
                        <a
                            :href="forgotUrl"
                            @click.prevent="router.visit(forgotUrl)"
                            class="text-xs font-semibold text-brand hover:underline"
                        >
                            Lupa sandi?
                        </a>
                    </div>
                    <div class="relative">
                        <Lock
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            v-model="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            required
                            class="auth-field pr-11"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute top-1/2 right-2 -translate-y-1/2 rounded-lg p-1.5 text-muted-foreground transition-colors hover:text-foreground"
                            :aria-label="
                                showPassword
                                    ? 'Sembunyikan kata sandi'
                                    : 'Tampilkan kata sandi'
                            "
                        >
                            <EyeOff v-if="showPassword" class="h-4 w-4" />
                            <Eye v-else class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="isLoading || isGoogleLoading"
                    class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand text-sm font-bold text-brand-foreground shadow-lg shadow-brand/25 transition-all hover:brightness-110 active:scale-[0.99] disabled:pointer-events-none disabled:opacity-60"
                >
                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                    <template v-else>
                        <span>{{
                            props.store
                                ? 'Masuk Sekarang'
                                : 'Masuk ke Dashboard'
                        }}</span>
                        <ArrowRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </template>
                </button>
            </form>
        </div>

        <!-- Bottom Link -->
        <div class="mt-6 text-center text-sm text-muted-foreground">
            Belum punya akun?
            <a
                :href="registerUrl"
                @click.prevent="router.visit(registerUrl)"
                class="ml-1 font-bold text-brand hover:underline"
            >
                {{ props.store ? 'Daftar Sekarang' : 'Daftar Toko Gratis' }}
            </a>
        </div>

        <Toaster position="top-right" />
    </AuthLayout>
</template>
