<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { LockKeyhole, RotateCw, SearchX, TriangleAlert } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Props {
    status?: number;
}

const props = withDefaults(defineProps<Props>(), { status: 500 });

interface ErrorContent {
    title: string;
    description: string;
    icon: typeof SearchX;
}

const errorMap: Record<number, ErrorContent> = {
    403: {
        title: 'Akses Ditolak',
        description:
            'Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi pemilik toko jika menurut Anda ini sebuah kekeliruan.',
        icon: LockKeyhole,
    },
    404: {
        title: 'Halaman Tidak Ditemukan',
        description:
            'Halaman yang Anda cari tidak tersedia, sudah dipindahkan, atau belum pernah ada.',
        icon: SearchX,
    },
};

const current: ErrorContent = errorMap[props.status] ?? {
    title: 'Terjadi Kesalahan',
    description:
        'Terjadi kesalahan tak terduga. Silakan coba lagi beberapa saat lagi.',
    icon: TriangleAlert,
};

function reload() {
    window.location.reload();
}
</script>

<template>
    <Head :title="`${current.title} — Toko Instan`" />

    <main
        class="relative flex min-h-dvh items-center justify-center overflow-hidden bg-background px-4 py-16 font-sans"
    >
        <div
            class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-brand-strong/10 blur-3xl"
        />
        <div
            class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-brand-accent/10 blur-3xl"
        />

        <div class="relative z-10 w-full max-w-md text-center">
            <div
                class="mx-auto mb-6 flex size-20 items-center justify-center rounded-2xl bg-brand-soft"
            >
                <component :is="current.icon" class="size-9 text-primary" />
            </div>

            <p
                class="font-mono text-sm font-semibold tracking-[0.3em] text-primary"
            >
                ERROR {{ props.status }}
            </p>

            <h1
                class="mt-3 text-3xl font-extrabold tracking-tight text-foreground"
            >
                {{ current.title }}
            </h1>

            <p class="mt-3 text-sm leading-relaxed text-muted-foreground">
                {{ current.description }}
            </p>

            <div
                class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row"
            >
                <Button as-child>
                    <Link href="/">Kembali ke Beranda</Link>
                </Button>
                <Button variant="outline" @click="reload">
                    <RotateCw />
                    Muat Ulang
                </Button>
            </div>
        </div>
    </main>
</template>
