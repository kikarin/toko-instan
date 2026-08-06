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
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-[#f5f4f0] p-4 font-sans select-none"
    >
        <!-- Ambient Glow -->
        <div
            class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#e07c28]/10 blur-3xl"
        />
        <div
            class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-[#0e9f8a]/10 blur-3xl"
        />

        <div
            class="relative z-10 flex w-full max-w-xl flex-col items-center gap-6 text-center"
        >
            <!-- Brand -->
            <div class="flex flex-col items-center">
                <div
                    class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#e07c28] to-[#c2500a] text-2xl font-extrabold text-white shadow-lg shadow-[#e07c28]/30"
                >
                    S
                </div>
                <div class="flex items-center gap-1.5">
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-[#1c1c22]"
                    >
                        Toko Instan
                    </h1>
                    <Badge
                        variant="amber"
                        class="px-1.5 py-0 text-[9px] uppercase"
                        >Beta</Badge
                    >
                </div>
                <p class="mt-2 text-sm text-[#9090a0]">
                    Marketplace UMKM digital — belanja aman, jualan gampang,
                    semua dalam satu platform.
                </p>
            </div>

            <!-- Feature pills -->
            <div class="flex flex-wrap items-center justify-center gap-2">
                <Badge
                    variant="outline"
                    class="border-black/10 bg-white text-[#4a4a57]"
                >
                    <ShieldCheck class="mr-1 h-3 w-3 text-teal-500" /> Escrow
                    Guarantee
                </Badge>
                <Badge
                    variant="outline"
                    class="border-black/10 bg-white text-[#4a4a57]"
                >
                    <Package class="mr-1 h-3 w-3 text-[#e07c28]" /> Ribuan
                    Produk
                </Badge>
                <Badge
                    variant="outline"
                    class="border-black/10 bg-white text-[#4a4a57]"
                >
                    <Truck class="mr-1 h-3 w-3 text-blue-500" /> Gratis Ongkir
                </Badge>
            </div>

            <!-- CTA Buttons -->
            <div class="flex w-full max-w-sm flex-col gap-2.5">
                <Button
                    variant="amber"
                    size="lg"
                    class="w-full text-sm font-bold shadow-md"
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
                    class="rounded-2xl border border-black/8 bg-white p-4 text-left shadow-2xs"
                >
                    <ShoppingBag class="mb-2 h-5 w-5 text-[#0e9f8a]" />
                    <p class="text-xs font-extrabold text-[#1c1c22]">Pembeli</p>
                    <p class="mt-1 text-[10px] leading-relaxed text-[#9090a0]">
                        Belanja dari marketplace dengan proteksi rekening
                        bersama.
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-black/8 bg-white p-4 text-left shadow-2xs"
                >
                    <Store class="mb-2 h-5 w-5 text-[#e07c28]" />
                    <p class="text-xs font-extrabold text-[#1c1c22]">
                        Pemilik Toko
                    </p>
                    <p class="mt-1 text-[10px] leading-relaxed text-[#9090a0]">
                        Buka toko online dan kelola produk dari dashboard
                        seller.
                    </p>
                </div>
            </div>

            <a
                href="#"
                @click.prevent="router.visit('/login')"
                class="flex items-center gap-1 text-xs font-bold text-[#e07c28] hover:underline"
            >
                Jelajahi Toko Instan <ArrowRight class="h-3 w-3" />
            </a>
        </div>
    </div>
</template>
