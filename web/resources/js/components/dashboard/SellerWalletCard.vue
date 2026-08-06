<script setup lang="ts">
import { Wallet, ArrowUpRight, CheckCircle2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
</script>

<template>
    <div
        class="flex flex-col gap-4 rounded-2xl border border-[#e07c2830] bg-gradient-to-br from-[#fdf6ee] to-[#fef9f3] p-5 shadow-sm"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div
                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#e07c281a] text-[#e07c28]"
                >
                    <Wallet class="h-4 w-4" />
                </div>
                <span class="text-sm font-bold text-[#1c1c22]"
                    >Dompet Seller</span
                >
            </div>
            <Badge
                variant="amber"
                class="px-2 py-0.5 text-[9px] tracking-wider uppercase"
            >
                PREMIUM
            </Badge>
        </div>

        <div>
            <p class="mb-1 text-[10px] text-[#9090a0]">Saldo Tersedia</p>
            <p
                class="font-mono text-2xl leading-none font-extrabold tracking-tight text-[#1c1c22]"
            >
                Rp 38.4Jt
            </p>
        </div>

        <div class="rounded-xl border border-[#e07c281a] bg-[#e07c280f] p-3">
            <p class="mb-0.5 text-[10px] text-[#9090a0]">Pending Escrow</p>
            <p class="font-mono text-base font-bold text-amber-600">Rp 6.2Jt</p>
        </div>

        <div class="flex flex-col gap-1.5 text-[11px]">
            <div class="flex items-center justify-between">
                <span class="text-[#9090a0]">Total Withdraw</span>
                <span class="font-mono font-semibold text-[#4a4a57]"
                    >Rp 122.7Jt</span
                >
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#9090a0]">Withdraw Terakhir</span>
                <span class="font-mono font-semibold text-[#4a4a57]"
                    >3 Agu 2026</span
                >
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#9090a0]">Fee Withdraw</span>
                <span class="flex items-center gap-1 font-bold text-[#22a15a]">
                    <CheckCircle2 class="h-3 w-3" /> Gratis
                </span>
            </div>
        </div>

        <Button
            variant="amber"
            size="lg"
            class="mt-1 w-full font-bold shadow-md"
        >
            Tarik Dana
            <ArrowUpRight class="ml-1 h-4 w-4" />
        </Button>
    </div>
</template>
