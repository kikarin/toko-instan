<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Wallet, ArrowUpRight, CheckCircle2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

function goToWallet() {
    router.visit('/wallet');
}
</script>

<template>
    <div
        class="relative flex flex-col justify-between gap-5 overflow-hidden rounded-3xl border border-primary/20 bg-primary/5 p-6 shadow-lg"
    >
        <div
            class="pointer-events-none absolute -top-8 -right-8 h-32 w-32 rounded-full bg-primary/20 blur-2xl"
        />

        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary font-bold text-primary-foreground shadow-md shadow-primary/30"
                >
                    <Wallet class="h-5 w-5" />
                </div>
                <div>
                    <h4 class="text-sm font-black text-foreground">
                        Dompet Merchant
                    </h4>
                    <span class="text-[10px] font-semibold text-muted-foreground"
                        >Ledger Imutabel</span
                    >
                </div>
            </div>
            <Badge
                variant="default"
                class="px-2.5 py-0.5 text-[9px] font-black tracking-widest uppercase shadow-2xs"
            >
                PREMIUM
            </Badge>
        </div>

        <div class="relative z-10">
            <span
                class="mb-1 block text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                >Saldo Siap Ditarik</span
            >
            <p
                class="font-mono text-3xl leading-none font-black tracking-tight text-foreground"
            >
                Rp 95.400.000
            </p>
        </div>

        <div
            class="relative z-10 flex items-center justify-between rounded-2xl border border-primary/20 bg-background/70 p-3.5 backdrop-blur-md"
        >
            <div>
                <span
                    class="block text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                    >Pending Escrow</span
                >
                <span class="font-mono text-xs font-bold text-primary"
                    >Rp 12.500.000</span
                >
            </div>
            <Badge variant="teal" class="px-2 py-0 text-[9px] font-extrabold"
                >Pesanan Berjalan</Badge
            >
        </div>

        <div
            class="relative z-10 flex flex-col gap-2 border-t border-primary/10 pt-3 text-[11px] font-semibold"
        >
            <div class="flex items-center justify-between text-muted-foreground">
                <span>Total Penarikan Akumulasi</span>
                <span class="font-mono font-black text-foreground"
                    >Rp 122.700.000</span
                >
            </div>
            <div class="flex items-center justify-between text-muted-foreground">
                <span>Withdraw Fee Admin</span>
                <span
                    class="flex items-center gap-1 font-bold text-emerald-600"
                >
                    <CheckCircle2 class="h-3.5 w-3.5" /> Gratis (Premium)
                </span>
            </div>
        </div>

        <Button
            variant="default"
            size="lg"
            class="relative z-10 h-11 w-full cursor-pointer rounded-2xl text-xs font-extrabold shadow-lg shadow-primary/25"
            @click="goToWallet"
        >
            Cairkan Saldo / Dompet
            <ArrowUpRight class="ml-1.5 h-4 w-4" />
        </Button>
    </div>
</template>
