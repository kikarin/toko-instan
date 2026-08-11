<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, Loader2, Lock, Mail } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast, Toaster } from '@/components/ui/sonner';
import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{
    store?: any;
}>();

const email = ref('');
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);
const sent = ref(false);

const postUrl = computed(() =>
    props.store ? `/${props.store.slug}/forgot-password` : '/forgot-password',
);
const loginUrl = computed(() =>
    props.store ? `/${props.store.slug}/login` : '/login',
);

function handleSendLink() {
    if (!email.value) {
        errorMessage.value = 'Silakan isi email Anda.';
        toast.error('Silakan isi email Anda.');

        return;
    }

    errorMessage.value = null;
    isLoading.value = true;

    router.post(
        postUrl.value,
        {
            email: email.value,
        },
        {
            onSuccess: () => {
                sent.value = true;
                toast.success('Link reset kata sandi telah dikirim.');
            },
            onFinish: () => {
                isLoading.value = false;
            },
            onError: (errors) => {
                const msg =
                    errors.email || 'Gagal mengirim link reset. Coba lagi.';
                errorMessage.value = msg;
                toast.error(msg);
            },
        },
    );
}
</script>

<template>
    <Head title="Lupa Kata Sandi - Toko Instan" />

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
                Lupa Kata Sandi
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{
                    props.store
                        ? `Masukkan email untuk akun di ${props.store.name}`
                        : 'Masukkan email akun Anda untuk menerima link reset'
                }}
            </p>
        </div>

        <!-- Panel -->
        <div
            class="rounded-3xl border border-border bg-card p-6 shadow-xl shadow-black/5 md:p-8"
        >
            <!-- Success Alert -->
            <div
                v-if="sent"
                class="flex flex-col gap-1.5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-xs font-medium text-emerald-600"
            >
                <span class="flex items-center gap-2">
                    <CheckCircle2 class="h-4 w-4 shrink-0" />
                    Link reset telah dikirim ke email Anda.
                </span>
                <span class="pl-6"
                    >Jika email tidak muncul, periksa folder spam.</span
                >
            </div>

            <!-- Error Alert -->
            <div
                v-else-if="errorMessage"
                class="mb-5 flex items-start gap-2.5 rounded-xl border border-destructive/20 bg-destructive/10 p-3 text-xs font-medium text-destructive"
            >
                <Lock class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <form
                v-if="!sent"
                @submit.prevent="handleSendLink"
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
                        <span>Kirim Link Reset</span>
                        <ArrowRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </template>
                </button>
            </form>

            <button
                v-else
                type="button"
                @click="router.visit(loginUrl)"
                class="flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-border bg-card text-sm font-semibold text-foreground shadow-xs transition-all hover:bg-muted/60 hover:shadow-sm active:scale-[0.99]"
            >
                Kembali ke Halaman Masuk
            </button>
        </div>

        <!-- Bottom Link -->
        <div class="mt-6 text-center text-sm text-muted-foreground">
            Ingat kata sandi Anda?
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
