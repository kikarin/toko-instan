<script setup lang="ts">
import { Star } from 'lucide-vue-next';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';

interface Seller {
    name: string;
    gmv: string;
    orders: number;
    rating: number;
    badge: 'top' | 'pro' | null;
    avatar: string;
    hue: number;
}

interface Props {
    sellers: Seller[];
}

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col gap-2">
        <div
            v-for="(seller, i) in sellers"
            :key="i"
            class="flex cursor-default items-center gap-2.5 rounded-xl border p-2.5 transition-all duration-200"
            :class="[
                i === 0
                    ? 'border-[#e07c2825] bg-[#e07c2810]'
                    : 'border-[#0000000d] bg-[#f5f4f0] hover:bg-white hover:shadow-xs',
            ]"
        >
            <span
                class="w-3.5 text-center font-mono text-[10px] font-bold text-[#c8c8d5]"
            >
                {{ i + 1 }}
            </span>

            <Avatar :fallback="seller.avatar" :hue="seller.hue" size="sm" />

            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-semibold text-[#1c1c22]">
                    {{ seller.name }}
                </p>
                <p class="flex items-center gap-1 text-[10px] text-[#9090a0]">
                    {{ seller.orders }} pesanan ·
                    <span
                        class="flex items-center gap-0.5 font-medium text-amber-500"
                    >
                        <Star
                            class="h-2.5 w-2.5 fill-amber-400 stroke-amber-400"
                        />
                        {{ seller.rating }}
                    </span>
                </p>
            </div>

            <div class="flex shrink-0 flex-col items-end gap-0.5 text-right">
                <p class="font-mono text-xs font-bold text-[#e07c28]">
                    {{ seller.gmv }}
                </p>
                <Badge
                    v-if="seller.badge"
                    :variant="seller.badge === 'top' ? 'amber' : 'violet'"
                    class="px-1.5 py-0 text-[9px] tracking-wider uppercase"
                >
                    {{ seller.badge }}
                </Badge>
            </div>
        </div>
    </div>
</template>
