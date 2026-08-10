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
    Link2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/sonner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { signInWithGooglePopup } from '@/lib/firebase';

const props = defineProps<{
    store?: any;
    intent?: 'platform' | 'storefront';
}>();

const isBuyerStorefront = computed(() => props.intent === 'storefront');
const registerUrl = computed(() =>
    isBuyerStorefront.value && props.store
        ? `/${props.store.slug}/register`
        : '/register',
);

const name = ref('');
const storeName = ref('');
const storeSlug = ref('');
const slugTouched = ref(false);
const email = ref('');
const password = ref('');
const isLoading = ref(false);
const isGoogleLoading = ref(false);
const errorMessage = ref<string | null>(null);

watch(storeName, (value) => {
    if (!slugTouched.value && !isBuyerStorefront.value) {
        storeSlug.value = slugify(value);
    }
});

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

async function handleRegister() {
    if (!name.value || !email.value || !password.value) {
        errorMessage.value = 'Silakan lengkapi bidang yang wajib diisi.';
        toast.error(errorMessage.value);

        return;
    }

    if (!isBuyerStorefront.value && (!storeName.value || !storeSlug.value)) {
        errorMessage.value = 'Nama toko dan slug wajib diisi.';
        toast.error(errorMessage.value);

        return;
    }

    if (password.value.length < 6) {
        errorMessage.value = 'Kata sandi minimal 6 karakter.';
        toast.error(errorMessage.value);

        return;
    }

    errorMessage.value = null;
    isLoading.value = true;

    const payload = isBuyerStorefront.value
        ? {
              name: name.value,
              email: email.value,
              password: password.value,
          }
        : {
              name: name.value,
              store_name: storeName.value,
              store_slug: storeSlug.value,
              email: email.value,
              password: password.value,
          };

    router.post(registerUrl.value, payload, {
        onSuccess: () => {
            toast.success(
                isBuyerStorefront.value
                    ? 'Akun pembeli berhasil dibuat!'
                    : 'Toko Anda berhasil dibuat!',
            );
        },
        onFinish: () => {
            isLoading.value = false;
        },
        onError: (errors) => {
            const msg =
                errors.email ||
                errors.store_name ||
                errors.store_slug ||
                errors.password ||
                'Gagal mendaftar. Silakan periksa data Anda.';
            errorMessage.value = String(msg);
            toast.error(String(msg));
        },
    });
}

async function handleGoogleLogin() {
    errorMessage.value = null;

    if (!isBuyerStorefront.value && (!storeName.value || !storeSlug.value)) {
        errorMessage.value =
            'Isi nama toko dan slug dulu sebelum daftar dengan Google.';
        toast.error(errorMessage.value);

        return;
    }

    isGoogleLoading.value = true;

    const { idToken, error } = await signInWithGooglePopup();

    if (error || !idToken) {
        isGoogleLoading.value = false;
        errorMessage.value = error ?? 'Gagal daftar Google.';
        toast.error(errorMessage.value);

        return;
    }

    router.post(
        '/auth/google',
        {
            id_token: idToken,
            intent: 'register',
            store_name: isBuyerStorefront.value ? null : storeName.value,
            store_slug: isBuyerStorefront.value ? null : storeSlug.value,
            store_slug_context: isBuyerStorefront.value
                ? (props.store?.slug ?? null)
                : null,
        },
        {
            onSuccess: () => {
                toast.success(
                    isBuyerStorefront.value
                        ? 'Akun pembeli berhasil dibuat!'
                        : 'Toko Anda berhasil dibuat!',
                );
            },
            onFinish: () => {
                isGoogleLoading.value = false;
            },
            onError: (errors) => {
                const msg =
                    errors.id_token ||
                    errors.store_name ||
                    errors.store_slug ||
                    Object.values(errors)[0] ||
                    'Gagal daftar dengan Google.';
                errorMessage.value = String(msg);
                toast.error(String(msg));
            },
        },
    );
}
</script>

<template>
    <Head title="Daftar Akun Baru - Toko Instan" />

    <AuthLayout spacious>
        <div class="flex flex-col items-center text-center">
            <div
                class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#e07c28] to-[#c2500a] text-2xl font-extrabold text-white shadow-lg shadow-[#e07c28]/30"
            >
                S
            </div>
            <div class="mb-1 flex items-center gap-1.5">
                <h1
                    class="text-2xl font-extrabold tracking-tight text-[#1c1c22]"
                >
                    {{
                        isBuyerStorefront && props.store
                            ? `Daftar di ${props.store.name}`
                            : 'Buat Toko di Toko Instan'
                    }}
                </h1>
                <Badge
                    v-if="!isBuyerStorefront"
                    variant="amber"
                    class="px-1.5 py-0 text-[9px] uppercase"
                    >GRATIS</Badge
                >
            </div>
            <p class="text-xs text-[#9090a0]">
                {{
                    isBuyerStorefront
                        ? 'Gabung sebagai pembeli dan nikmati promo'
                        : 'Daftar sebagai seller dan buat toko Anda'
                }}
            </p>
        </div>

        <Card class="border-black/10 p-6 shadow-md md:p-8">
            <div
                v-if="errorMessage"
                class="mb-5 flex items-start gap-2.5 rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-xs font-medium text-red-600"
            >
                <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ errorMessage }}</span>
            </div>

            <div
                v-if="!isBuyerStorefront"
                class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2"
            >
                <div class="flex flex-col gap-1">
                    <label
                        class="flex items-center gap-1.5 text-xs font-bold text-[#1c1c22]"
                    >
                        <Store class="h-3.5 w-3.5 text-[#9090a0]" /> Nama Toko *
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
                        <Link2 class="h-3.5 w-3.5 text-[#9090a0]" /> Slug URL *
                    </label>
                    <Input
                        :model-value="storeSlug"
                        type="text"
                        placeholder="nova-batik"
                        required
                        class="h-10"
                        @update:model-value="
                            (v) => {
                                slugTouched = true;
                                storeSlug = slugify(String(v));
                            }
                        "
                    />
                    <p class="text-[10px] text-[#9090a0]">
                        Storefront:
                        <span class="font-mono text-[#1c1c22]"
                            >/{{ storeSlug || 'slug-toko' }}</span
                        >
                    </p>
                </div>
            </div>

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

            <form
                class="flex flex-col gap-3.5"
                @submit.prevent="handleRegister"
            >
                <div class="flex flex-col gap-1">
                    <label
                        class="flex items-center gap-1.5 text-xs font-bold text-[#1c1c22]"
                    >
                        <User class="h-3.5 w-3.5 text-[#9090a0]" /> Nama Lengkap
                    </label>
                    <Input
                        v-model="name"
                        type="text"
                        placeholder="Nama Anda"
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
                        <Lock class="h-3.5 w-3.5 text-[#9090a0]" /> Kata Sandi
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
                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                    <template v-else>
                        <Sparkles class="h-4 w-4" />
                        <span>{{
                            isBuyerStorefront
                                ? 'Daftar Pembeli Sekarang'
                                : 'Buat Toko Sekarang'
                        }}</span>
                    </template>
                </Button>
            </form>
        </Card>

        <div class="text-center text-xs text-[#9090a0]">
            Sudah punya akun?
            <a
                :href="
                    isBuyerStorefront && props.store
                        ? `/${props.store.slug}/login`
                        : '/login'
                "
                class="ml-1 font-bold text-[#e07c28] hover:underline"
                @click.prevent="
                    router.visit(
                        isBuyerStorefront && props.store
                            ? `/${props.store.slug}/login`
                            : '/login',
                    )
                "
            >
                Masuk di sini
            </a>
        </div>
    </AuthLayout>
</template>
