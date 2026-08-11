<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    Eye,
    EyeOff,
    Loader2,
    Lock,
    Mail,
    ShoppingBag,
    Sparkles,
    Store,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast, Toaster } from '@/components/ui/sonner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { signInWithGooglePopup } from '@/lib/firebase';

const props = defineProps<{
    store?: any;
}>();

type AccountRole = 'buyer' | 'seller';
const selectedRole = ref<AccountRole>('buyer');

const registerUrl = computed(() =>
    props.store ? `/${props.store.slug}/register` : '/register',
);
const loginUrl = computed(() =>
    props.store ? `/${props.store.slug}/login` : '/login',
);

const name = ref('');
const storeName = ref('');
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const isLoading = ref(false);
const isGoogleLoading = ref(false);
const errorMessage = ref<string | null>(null);

async function handleRegister() {
    if (!name.value || !email.value || !password.value) {
        errorMessage.value = 'Silakan lengkapi bidang yang wajib diisi.';
        toast.error('Silakan lengkapi bidang yang wajib diisi.');

        return;
    }

    if (selectedRole.value === 'seller' && !storeName.value) {
        errorMessage.value = 'Silakan isi nama toko Anda.';
        toast.error('Silakan isi nama toko Anda.');

        return;
    }

    if (password.value.length < 6) {
        errorMessage.value = 'Kata sandi minimal 6 karakter.';
        toast.error('Kata sandi minimal 6 karakter.');

        return;
    }

    errorMessage.value = null;
    isLoading.value = true;

    router.post(
        registerUrl.value,
        {
            role: selectedRole.value,
            name: name.value,
            store_name:
                selectedRole.value === 'seller' ? storeName.value : null,
            email: email.value,
            password: password.value,
        },
        {
            onSuccess: () => {
                toast.success(
                    selectedRole.value === 'seller'
                        ? 'Toko Anda berhasil dibuat!'
                        : 'Akun Pembeli berhasil dibuat!',
                );
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                const msg =
                    errors.email ||
                    errors.store_name ||
                    errors.password ||
                    'Gagal mendaftar. Silakan periksa data Anda.';
                errorMessage.value = msg;
                toast.error(msg);
            },
        },
    );
}

async function handleGoogleLogin() {
    errorMessage.value = null;
    isGoogleLoading.value = true;

    const { user, error } = await signInWithGooglePopup();
    isGoogleLoading.value = false;

    if (error) {
        errorMessage.value = error;
        toast.error(error);
    } else if (user) {
        router.post(registerUrl.value, {
            role: selectedRole.value,
            name: user.displayName || 'Pengguna',
            store_name:
                selectedRole.value === 'seller'
                    ? (user.displayName || 'Toko') + ' Store'
                    : null,
            email: user.email,
            password: user.uid,
        });
    }
}
</script>

<template>
    <Head title="Daftar Akun Baru - Toko Instan" />

    <AuthLayout spacious>
        <!-- Header -->
        <div class="mb-6 flex flex-col items-center text-center">
            <div
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand/70 text-xl font-extrabold text-brand-foreground shadow-lg ring-1 shadow-brand/30 ring-white/20"
            >
                S
            </div>

            <div class="flex items-center gap-2">
                <h1
                    class="text-[26px] leading-tight font-extrabold tracking-tight text-foreground"
                >
                    {{
                        props.store
                            ? `Daftar di ${props.store.name}`
                            : 'Buat Akun Toko Instan'
                    }}
                </h1>
                <span
                    v-if="!props.store"
                    class="rounded-full bg-brand px-2 py-0.5 text-[9px] font-extrabold tracking-widest text-brand-foreground uppercase"
                    >Gratis</span
                >
            </div>
            <p class="mt-2 text-sm text-muted-foreground">
                {{
                    props.store
                        ? 'Gabung sebagai Member dan nikmati promo'
                        : 'Pilih peran akun Anda untuk memulai'
                }}
            </p>
        </div>

        <!-- Role Selector -->
        <div
            v-if="!props.store"
            class="mb-6 grid grid-cols-2 gap-1.5 rounded-2xl border border-border bg-card p-1.5 shadow-sm"
        >
            <button
                type="button"
                @click="selectedRole = 'seller'"
                class="flex cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold transition-all"
                :class="[
                    selectedRole === 'seller'
                        ? 'bg-brand text-brand-foreground shadow-sm'
                        : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground',
                ]"
            >
                <Store class="h-4 w-4" />
                <span>Pemilik Toko</span>
            </button>
            <button
                type="button"
                @click="selectedRole = 'buyer'"
                class="flex cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold transition-all"
                :class="[
                    selectedRole === 'buyer'
                        ? 'bg-brand text-brand-foreground shadow-sm'
                        : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground',
                ]"
            >
                <ShoppingBag class="h-4 w-4" />
                <span>Pembeli</span>
            </button>
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

            <!-- Google Sign Up -->
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
                    <span>Daftar Cepat dengan Google</span>
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
                    atau isi form
                </span>
            </div>

            <!-- Registration Form -->
            <form @submit.prevent="handleRegister" class="flex flex-col gap-4">
                <div>
                    <label
                        class="mb-1.5 block text-xs font-bold text-foreground"
                    >
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <User
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            v-model="name"
                            type="text"
                            placeholder="Nama Anda"
                            required
                            class="auth-field"
                        />
                    </div>
                </div>

                <div v-if="!props.store && selectedRole === 'seller'">
                    <label
                        class="mb-1.5 block text-xs font-bold text-foreground"
                    >
                        Nama Toko Online *
                    </label>
                    <div class="relative">
                        <Store
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            v-model="storeName"
                            type="text"
                            placeholder="Contoh: NovaBatik Studio"
                            required
                            class="auth-field"
                        />
                    </div>
                </div>

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
                            placeholder="nama@email.com"
                            required
                            class="auth-field"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-xs font-bold text-foreground"
                    >
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <Lock
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            v-model="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Minimal 6 karakter"
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
                    class="group mt-1 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand text-sm font-bold text-brand-foreground shadow-lg shadow-brand/25 transition-all hover:brightness-110 active:scale-[0.99] disabled:pointer-events-none disabled:opacity-60"
                >
                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                    <template v-else>
                        <Sparkles class="h-4 w-4" />
                        <span>{{
                            selectedRole === 'seller'
                                ? 'Buat Toko Sekarang'
                                : 'Daftar Pembeli Sekarang'
                        }}</span>
                    </template>
                </button>
            </form>
        </div>

        <!-- Bottom Link -->
        <div class="mt-6 text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <a
                :href="loginUrl"
                @click.prevent="router.visit(loginUrl)"
                class="ml-1 font-bold text-brand hover:underline"
            >
                Masuk di sini
            </a>
        </div>

        <Toaster position="top-right" />
    </AuthLayout>
</template>
