<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Lock, Mail, AlertCircle, ArrowRight, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/sonner';
import { signInWithGooglePopup } from '@/lib/firebase';

const email = ref('');
const password = ref('');
const isLoading = ref(false);
const isGoogleLoading = ref(false);
const errorMessage = ref<string | null>(null);

async function handleEmailLogin() {
    if (!email.value || !password.value) {
        errorMessage.value = 'Silakan isi email dan kata sandi Anda.';
        toast.error('Silakan isi email dan kata sandi Anda.');

        return;
    }

    errorMessage.value = null;
    isLoading.value = true;

    // Send to Laravel AuthController
    router.post(
        '/login',
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

    const { user, error } = await signInWithGooglePopup();
    isGoogleLoading.value = false;

    if (error) {
        errorMessage.value = error;
    } else if (user) {
        // Also authenticate with Laravel backend
        router.post('/login', {
            email: user.email,
            password: user.uid,
        });
    }
}
</script>

<template>
    <Head title="Masuk - Toko Instan" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#f5f4f0] p-4 font-sans select-none"
    >
        <!-- Background Ambient Glow -->
        <div
            class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-[#e07c28]/10 blur-3xl"
        />
        <div
            class="pointer-events-none absolute -right-24 -bottom-24 h-96 w-96 rounded-full bg-[#6d4fc2]/10 blur-3xl"
        />

        <div class="relative z-10 flex w-full max-w-md flex-col gap-6">
            <!-- Header Brand -->
            <div class="flex flex-col items-center text-center">
                <div
                    class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#e07c28] to-[#c2500a] text-2xl font-extrabold text-white shadow-lg shadow-[#e07c28]/30"
                >
                    S
                </div>
                <h1
                    class="text-2xl font-extrabold tracking-tight text-[#1c1c22]"
                >
                    Selamat Datang Kembali
                </h1>
                <p class="mt-1 text-xs text-[#9090a0]">
                    Masuk ke dashboard Toko Instan Anda
                </p>
            </div>

            <!-- Login Card -->
            <Card
                class="flex flex-col gap-4 border-black/10 p-6 shadow-md md:p-8"
            >
                <!-- Error Alert -->
                <div
                    v-if="errorMessage"
                    class="flex items-start gap-2.5 rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-xs font-medium text-red-600"
                >
                    <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{{ errorMessage }}</span>
                </div>

                <!-- Google Sign In Button -->
                <Button
                    variant="outline"
                    class="flex h-11 w-full items-center justify-center gap-2.5 rounded-xl border-black/12 bg-white py-2.5 text-xs font-semibold shadow-2xs transition-all hover:bg-black/5"
                    :disabled="isGoogleLoading || isLoading"
                    @click="handleGoogleLogin"
                >
                    <Loader2
                        v-if="isGoogleLoading"
                        class="h-4 w-4 animate-spin text-[#e07c28]"
                    />
                    <template v-else>
                        <!-- Official Google SVG Logo -->
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
                </Button>

                <!-- Divider -->
                <div class="relative my-2 flex items-center justify-center">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-black/10" />
                    </div>
                    <span
                        class="relative bg-white px-3 text-[11px] font-medium tracking-wider text-[#9090a0] uppercase"
                    >
                        atau email
                    </span>
                </div>

                <!-- Email & Password Form -->
                <form
                    @submit.prevent="handleEmailLogin"
                    class="flex flex-col gap-4"
                >
                    <div class="flex flex-col gap-1.5">
                        <label
                            class="flex items-center gap-1.5 text-xs font-bold text-[#1c1c22]"
                        >
                            <Mail class="h-3.5 w-3.5 text-[#9090a0]" /> Email
                            Toko
                        </label>
                        <Input
                            v-model="email"
                            type="email"
                            placeholder="nama@tokomu.com"
                            required
                            class="h-10"
                        />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label
                                class="flex items-center gap-1.5 text-xs font-bold text-[#1c1c22]"
                            >
                                <Lock class="h-3.5 w-3.5 text-[#9090a0]" /> Kata
                                Sandi
                            </label>
                            <a
                                href="#"
                                class="text-[11px] font-medium text-[#e07c28] hover:underline"
                                >Lupa sandi?</a
                            >
                        </div>
                        <Input
                            v-model="password"
                            type="password"
                            placeholder="••••••••"
                            required
                            class="h-10"
                        />
                    </div>

                    <Button
                        type="submit"
                        variant="amber"
                        class="mt-1 flex h-11 w-full items-center justify-center gap-2 text-xs font-bold shadow-md"
                        :disabled="isLoading || isGoogleLoading"
                    >
                        <Loader2
                            v-if="isLoading"
                            class="h-4 w-4 animate-spin"
                        />
                        <template v-else>
                            <span>Masuk ke Dashboard</span>
                            <ArrowRight class="h-4 w-4" />
                        </template>
                    </Button>
                </form>
            </Card>

            <!-- Bottom Link -->
            <div class="text-center text-xs text-[#9090a0]">
                Belum punya toko?
                <a
                    href="/register"
                    @click.prevent="router.visit('/register')"
                    class="ml-1 font-bold text-[#e07c28] hover:underline"
                >
                    Daftar Toko Gratis
                </a>
            </div>
        </div>
    </div>
</template>
