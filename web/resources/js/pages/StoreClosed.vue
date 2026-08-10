<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Store } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

defineProps<{
    store: {
        id: number;
        name: string;
        slug: string;
        logo?: string | null;
    };
}>();
</script>

<template>
    <Head :title="`${store.name} tutup sementara`" />

    <StorefrontLayout>
        <main
            class="mx-auto flex min-h-[70vh] w-full max-w-lg flex-col items-center justify-center gap-4 px-4 py-16 text-center"
        >
            <div
                class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#e07c28]/12 text-[#e07c28]"
            >
                <Store class="h-8 w-8" />
            </div>
            <h1 class="text-2xl font-extrabold text-[#1c1c22]">
                Toko sedang tutup
            </h1>
            <p class="text-sm text-[#9090a0]">
                <span class="font-semibold text-[#1c1c22]">{{ store.name }}</span>
                sementara tidak menerima pesanan. Silakan kembali lagi nanti.
            </p>
            <Button as-child variant="outline" class="mt-2">
                <Link href="/">Kembali ke beranda</Link>
            </Button>
        </main>
    </StorefrontLayout>
</template>
