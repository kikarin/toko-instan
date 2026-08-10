<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Lock,
    Mail,
    User,
    Store,
    AlertCircle,
    Loader2,
    Sparkles,
    ShoppingBag,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { toast, Toaster } from '@/components/ui/sonner';
import { signInWithGooglePopup } from '@/lib/firebase';

const props = defineProps<{
    store?: any;
}>();

type AccountRole = 'buyer' | 'seller';
const selectedRole = ref<AccountRole>('buyer');

const registerUrl = computed(() => props.store ? `/${props.store.slug}/register` : '/register');

const name = ref('');
const storeName = ref('');
const email = ref('');
const password = ref('');
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
        <div class="flex flex-col items-center text-center">
            <div
                class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand text-2xl font-extrabold text-brand-foreground shadow-lg shadow-brand/30"
            >
                S
            </div>


                <div class="mb-1 flex items-center gap-1.5">
                    <h1
                        class="text-2xl font-extrabold tracking-tight text-[#1c1c22]"
                    >
                        {{ props.store ? `Daftar di ${props.store.name}` : 'Buat Akun Toko Instan' }}
                    </h1>
                    <Badge
                        v-if="!props.store"
                        variant="amber"
                        class="px-1.5 py-0 text-[9px] uppercase"
                        >GRATIS</Badge
                    >
                </div>
                <p class="text-xs text-[#9090a0]">
                    {{ props.store ? 'Gabung sebagai Member dan nikmati promo' : 'Pilih peran akun Anda untuk memulai' }}
                </p>
            </div>

            <!-- Role Selector Tabs -->
            <div
                v-if="!props.store"
                class="flex gap-1 rounded-2xl border border-border bg-card p-1 shadow-sm mb-4"
            >
                <button
                    type="button"
                    @click="selectedRole = 'seller'"
                    class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold transition-all"
                    :class="[
                        selectedRole === 'seller'
                            ? 'bg-brand text-brand-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    <Store class="h-3.5 w-3.5" />
                    <span>Pemilik Toko (Seller)</span>
                </button>
                <button
                    type="button"
                    @click="selectedRole = 'buyer'"
                    class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold transition-all"
                    :class="[
                        selectedRole === 'buyer'
                            ? 'bg-brand text-brand-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    <ShoppingBag class="h-3.5 w-3.5" />
                    <span>Pembeli (Buyer)</span>
                </button>
            </div>

            <!-- Register Card -->
            <Card class="border-border p-6 shadow-md md:p-8">
                <!-- Error Alert -->
                <div
                    v-if="errorMessage"
                    class="mb-5 flex items-start gap-2.5 rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-xs font-medium text-red-600"
                >
                    <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{{ errorMessage }}</span>
                </div>

                <!-- Google Sign In Button -->
                <Button
                    variant="outline"
                    class="flex h-11 w-full items-center justify-center gap-2.5 rounded-xl border-border bg-card py-2.5 text-xs font-semibold shadow-sm transition-all hover:bg-muted"
                    :disabled="isGoogleLoading || isLoading"
                    @click="handleGoogleLogin"
                >
                    <Loader2
                        v-if="isGoogleLoading"
                        class="h-4 w-4 animate-spin text-[#e07c28]"
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
                </Button>

                <!-- Divider -->
                <div class="relative my-5 flex items-center justify-center">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-black/10" />
                    </div>
                    <span
                        class="relative bg-white px-3 text-[11px] font-medium tracking-wider text-[#9090a0] uppercase"
                    >
                        atau isi form
                    </span>
                </div>

                <!-- Registration Form -->
                <form
                    @submit.prevent="handleRegister"
                    class="flex flex-col gap-3.5"
                >
                    <div class="flex flex-col gap-1">
                        <label
                            class="flex items-center gap-1.5 text-xs font-bold text-[#1c1c22]"
                        >
                            <User class="h-3.5 w-3.5 text-[#9090a0]" /> Nama
                            Lengkap
                        </label>
                        <Input
                            v-model="name"
                            type="text"
                            placeholder="Nama Anda"
                            required
                            class="h-10"
                        />
                    </div>

                    <div
                        v-if="!props.store && selectedRole === 'seller'"
                        class="flex flex-col gap-1"
                    >
                        <label
                            class="flex items-center gap-1.5 text-xs font-bold text-[#1c1c22]"
                        >
                            <Store class="h-3.5 w-3.5 text-[#9090a0]" /> Nama
                            Toko Online *
                        </label>
                        <Input
                            v-model="storeName"
                            type="text"
                            placeholder="Contoh: NovaBatik Studio"
                            required
                            class="h-10"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label
                            class="flex items-center gap-1.5 text-xs font-bold text-[#1c1c22]"
                        >
                            <Mail class="h-3.5 w-3.5 text-[#9090a0]" /> Email
                        </label>
                        <Input
                            v-model="email"
                            type="email"
                            placeholder="nama@email.com"
                            required
                            class="h-10"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label
                            class="flex items-center gap-1.5 text-xs font-bold text-[#1c1c22]"
                        >
                            <Lock class="h-3.5 w-3.5 text-[#9090a0]" /> Kata
                            Sandi
                        </label>
                        <Input
                            v-model="password"
                            type="password"
                            placeholder="Minimal 6 karakter"
                            required
                            class="h-10"
                        />
                    </div>

                    <Button
                        type="submit"
                        variant="amber"
                        class="mt-3 flex h-11 w-full items-center justify-center gap-2 text-xs font-bold shadow-md"
                        :disabled="isLoading || isGoogleLoading"
                    >
                        <Loader2
                            v-if="isLoading"
                            class="h-4 w-4 animate-spin"
                        />
                        <template v-else>
                            <Sparkles class="h-4 w-4" />
                            <span>{{
                                selectedRole === 'seller'
                                    ? 'Buat Toko Sekarang'
                                    : 'Daftar Pembeli Sekarang'
                            }}</span>
                        </template>
                    </Button>
                </form>
            </Card>

            <!-- Bottom Link -->
            <div class="text-center text-xs text-[#9090a0]">
                Sudah punya akun?
                <a
                    :href="props.store ? `/${props.store.slug}/login` : '/login'"
                    @click.prevent="router.visit(props.store ? `/${props.store.slug}/login` : '/login')"
                    class="ml-1 font-bold text-[#e07c28] hover:underline"
                >
                    Masuk di sini
                </a>
            </div>

        <Toaster position="top-right" />
    </AuthLayout>
</template>
