<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ShoppingBag,
    Store,
    ShieldCheck,
    Package,
    Truck,
    LogIn,
    UserPlus,
    ArrowRight,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
</script>

<template>
    <Head title="Toko Instan - Marketplace UMKM Digital" />

    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-muted p-4 font-sans select-none"
    >
        <!-- Ambient Glow -->
        <div
            class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-brand-strong/10 blur-3xl"
        />
        <div
            class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-brand-accent/10 blur-3xl"
        />

        <div
            class="relative z-10 flex w-full max-w-xl flex-col items-center gap-6 text-center"
        >
            <!-- Brand -->
            <div class="flex flex-col items-center">
                <div
                    class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand text-2xl font-extrabold text-brand-foreground shadow-lg shadow-brand/30"
                >
                    S
                </div>
                <div class="flex items-center gap-1.5">
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-foreground"
                    >
                        Toko Instan
                    </h1>
                    <Badge
                        class="bg-brand text-brand-foreground px-1.5 py-0 text-[9px] uppercase"
                        >Beta</Badge
                    >
                </div>
                <p class="mt-2 text-sm text-muted-foreground">
                    Marketplace UMKM digital — belanja aman, jualan gampang,
                    semua dalam satu platform.
                </p>
            </div>

            <!-- Feature pills -->
            <div class="flex flex-wrap items-center justify-center gap-2">
                <Badge
                    variant="outline"
                    class="border-border bg-card text-muted-foreground"
                >
                    <ShieldCheck class="mr-1 h-3 w-3 text-brand-accent" /> Escrow
                    Guarantee
                </Badge>
                <Badge
                    variant="outline"
                    class="border-border bg-card text-muted-foreground"
                >
                    <Package class="mr-1 h-3 w-3 text-brand" /> Ribuan
                    Produk
                </Badge>
                <Badge
                    variant="outline"
                    class="border-border bg-card text-muted-foreground"
                >
                    <Truck class="mr-1 h-3 w-3 text-brand-strong" /> Gratis Ongkir
                </Badge>
            </div>

            <!-- CTA Buttons -->
            <div class="flex w-full max-w-sm flex-col gap-2.5">
                <Button
                    size="lg"
                    class="w-full text-sm font-bold shadow-md bg-brand text-brand-foreground border-0 hover:opacity-90"
                    @click="router.visit('/register')"
                >
                    <UserPlus class="mr-2 h-4 w-4" /> Daftar Sekarang
                </Button>
                <Button
                    variant="outline"
                    size="lg"
                    class="w-full text-sm font-bold"
                    @click="router.visit('/login')"
                >
                    <LogIn class="mr-2 h-4 w-4" /> Masuk ke Akun
                </Button>
            </div>

            <!-- Mode explanation -->
            <div class="mt-2 grid w-full max-w-md grid-cols-2 gap-3">
                <div
                    class="rounded-2xl border border-border bg-card p-4 text-left shadow-2xs"
                >
                    <ShoppingBag class="mb-2 h-5 w-5 text-brand-accent" />
                    <p class="text-xs font-extrabold text-foreground">Pembeli</p>
                    <p class="mt-1 text-[10px] leading-relaxed text-muted-foreground">
                        Belanja dari marketplace dengan proteksi rekening
                        bersama.
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border bg-card p-4 text-left shadow-2xs"
                >
                    <Store class="mb-2 h-5 w-5 text-brand" />
                    <p class="text-xs font-extrabold text-foreground">
                        Pemilik Toko
                    </p>
                    <p class="mt-1 text-[10px] leading-relaxed text-muted-foreground">
                        Buka toko online dan kelola produk dari dashboard
                        seller.
                    </p>
                </div>
            </div>

            <a
                href="#"
                @click.prevent="router.visit('/login')"
                class="flex items-center gap-1 text-xs font-bold text-brand hover:underline"
            >
                Jelajahi Toko Instan <ArrowRight class="h-3 w-3" />
            </a>
        </div>
    </div>
</template>
