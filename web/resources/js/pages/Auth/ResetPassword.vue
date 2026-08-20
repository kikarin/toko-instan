<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { AlertCircle, Eye, EyeOff, Loader2, Lock, Mail } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast, Toaster } from '@/components/ui/sonner';
import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{
    store?: any;
    token: string;
}>();

const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const showPassword = ref(false);
const showConfirmation = ref(false);
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);

const postUrl = computed(() =>
    props.store ? `/${props.store.slug}/reset-password` : '/reset-password',
);
const loginUrl = computed(() =>
    props.store ? `/${props.store.slug}/login` : '/login',
);

function handleReset() {
    if (!email.value || !password.value) {
        errorMessage.value = 'Silakan lengkapi email dan kata sandi baru Anda.';
        toast.error('Silakan lengkapi email dan kata sandi baru Anda.');

        return;
    }

    if (password.value.length < 6) {
        errorMessage.value = 'Kata sandi minimal 6 karakter.';
        toast.error('Kata sandi minimal 6 karakter.');

        return;
    }

    if (password.value !== passwordConfirmation.value) {
        errorMessage.value = 'Konfirmasi kata sandi tidak cocok.';
        toast.error('Konfirmasi kata sandi tidak cocok.');

        return;
    }

    errorMessage.value = null;
    isLoading.value = true;

    router.post(
        postUrl.value,
        {
            email: email.value,
            password: password.value,
            token: props.token,
        },
        {
            onSuccess: () => {
                toast.success('Kata sandi berhasil diubah. Silakan masuk.');
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                const msg =
                    errors.email ||
                    errors.password ||
                    'Gagal mereset kata sandi. Periksa kembali link Anda.';
                errorMessage.value = msg;
                toast.error(msg);
            },
        },
    );
}
</script>

<template>
    <Head title="Reset Kata Sandi - Toko Instan" />

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
                Buat Kata Sandi Baru
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{
                    props.store
                        ? `Perbarui kata sandi akun ${props.store.name}`
                        : 'Perbarui kata sandi akun Anda'
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

            <form @submit.prevent="handleReset" class="flex flex-col gap-5">
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
                        Kata Sandi Baru
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

                <div>
                    <label
                        class="mb-1.5 block text-xs font-bold text-foreground"
                    >
                        Konfirmasi Kata Sandi
                    </label>
                    <div class="relative">
                        <Lock
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <input
                            v-model="passwordConfirmation"
                            :type="showConfirmation ? 'text' : 'password'"
                            placeholder="Ulangi kata sandi baru"
                            required
                            class="auth-field pr-11"
                        />
                        <button
                            type="button"
                            @click="showConfirmation = !showConfirmation"
                            class="absolute top-1/2 right-2 -translate-y-1/2 rounded-lg p-1.5 text-muted-foreground transition-colors hover:text-foreground"
                            :aria-label="
                                showConfirmation
                                    ? 'Sembunyikan kata sandi'
                                    : 'Tampilkan kata sandi'
                            "
                        >
                            <EyeOff v-if="showConfirmation" class="h-4 w-4" />
                            <Eye v-else class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="isLoading"
                    class="group mt-1 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand text-sm font-bold text-brand-foreground shadow-lg shadow-brand/25 transition-all hover:brightness-110 active:scale-[0.99] disabled:pointer-events-none disabled:opacity-60"
                >
                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                    <template v-else>
                        <span>Perbarui Kata Sandi</span>
                    </template>
                </button>
            </form>
        </div>

        <!-- Bottom Link -->
        <div class="mt-6 text-center text-sm text-muted-foreground">
            Kembali ke
            <a
                :href="loginUrl"
                @click.prevent="router.visit(loginUrl)"
                class="ml-1 font-bold text-brand hover:underline"
            >
                Halaman Masuk
            </a>
        </div>

        <Toaster position="top-right" />
    </AuthLayout>
</template>
