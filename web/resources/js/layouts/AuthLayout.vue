<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, Lock, ShieldCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { useStoreTheme } from '@/composables/useStoreTheme';

withDefaults(
    defineProps<{
        spacious?: boolean;
    }>(),
    {
        spacious: false,
    },
);

useStoreTheme();

const store = computed<any>(() => usePage().props.store ?? null);

const brandName = computed(() => store.value?.name ?? 'Toko Instan');
const brandSub = computed(() =>
    store.value
        ? store.value.badge || 'Official Store'
        : 'Platform Jualan Online',
);
const brandInitial = computed(() => brandName.value.charAt(0));
const headline = computed(() =>
    store.value
        ? `Belanja nyaman di ${store.value.name}`
        : 'Jualan online jadi lebih mudah & cepat',
);
const benefits = computed<string[]>(() =>
    store.value
        ? [
              'Pembayaran mudah dan pembelian terjamin',
              'Promo serta diskon khusus untuk member',
              'Transaksi langsung dengan toko resmi',
          ]
        : [
              'Buat toko online gratis dalam hitungan menit',
              'Tanpa biaya bulanan, tanpa ribet',
              'Kelola produk, pesanan & pembayaran dalam satu dashboard',
          ],
);
</script>

<template>
    <div class="flex min-h-screen w-full bg-background font-sans select-none">
        <!-- Brand panel (desktop) -->
        <aside
            class="relative hidden w-[46%] shrink-0 flex-col justify-between overflow-hidden bg-brand p-10 text-brand-foreground lg:flex xl:p-14"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-black/25"
            />
            <div
                class="pointer-events-none absolute inset-0 opacity-15"
                style="
                    background-image: radial-gradient(
                        currentColor 1px,
                        transparent 1px
                    );
                    background-size: 22px 22px;
                "
            />
            <div
                class="pointer-events-none absolute -top-24 -right-20 h-80 w-80 rounded-full bg-white/10 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-secondary/40 blur-3xl"
            />

            <div class="relative z-10 flex items-center gap-3.5">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-lg font-extrabold shadow-inner ring-1 ring-white/25 backdrop-blur-sm"
                >
                    {{ brandInitial }}
                </div>
                <div>
                    <p
                        class="text-lg leading-tight font-extrabold tracking-tight"
                    >
                        {{ brandName }}
                    </p>
                    <p class="text-xs font-medium text-brand-foreground/70">
                        {{ brandSub }}
                    </p>
                </div>
            </div>

            <div class="relative z-10 flex flex-col gap-8">
                <h2
                    class="max-w-md text-3xl leading-tight font-extrabold tracking-tight xl:text-4xl"
                >
                    {{ headline }}
                </h2>
                <ul
                    class="flex flex-col gap-4 text-sm font-medium text-brand-foreground/85"
                >
                    <li
                        v-for="item in benefits"
                        :key="item"
                        class="flex items-center gap-3"
                    >
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5" />
                        </span>
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </div>

            <div
                class="relative z-10 flex items-center gap-3 text-xs font-semibold text-brand-foreground/75"
            >
                <span class="flex items-center gap-1.5">
                    <ShieldCheck class="h-4 w-4" />
                    Aman & Terpercaya
                </span>
                <span class="h-1 w-1 rounded-full bg-brand-foreground/40" />
                <span class="flex items-center gap-1.5">
                    <Lock class="h-4 w-4" />
                    Data Terenkripsi
                </span>
            </div>
        </aside>

        <!-- Form panel -->
        <main
            class="relative flex min-h-screen flex-1 items-center justify-center overflow-y-auto bg-background p-4 py-10 md:p-8"
        >
            <div
                class="w-full max-w-md animate-rise"
                :class="spacious ? 'xl:max-w-lg' : ''"
            >
                <slot />
            </div>
        </main>
    </div>
</template>
