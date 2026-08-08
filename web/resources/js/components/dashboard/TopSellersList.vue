<script setup lang="ts">
import { Star, Trophy } from 'lucide-vue-next';
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
    <div class="flex flex-col gap-2.5">
        <div
            v-for="(seller, i) in sellers"
            :key="i"
            class="group flex cursor-pointer items-center gap-3 rounded-2xl border p-3 transition-all duration-200"
            :class="[
                i === 0
                    ? 'border-amber-500/30 bg-amber-50/40 shadow-xs hover:shadow-md'
                    : 'border-black/8 bg-white hover:border-black/15 hover:bg-zinc-50 hover:shadow-xs',
            ]"
        >
            <!-- Rank Badge -->
            <div
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl font-mono text-xs font-black shadow-2xs"
                :class="[
                    i === 0
                        ? 'bg-amber-500 text-white'
                        : i === 1
                          ? 'bg-zinc-800 text-white'
                          : i === 2
                            ? 'bg-zinc-600 text-white'
                            : 'bg-zinc-100 text-zinc-500',
                ]"
            >
                <Trophy v-if="i === 0" class="h-3.5 w-3.5 text-white" />
                <span v-else>#{{ i + 1 }}</span>
            </div>

            <Avatar
                :fallback="seller.avatar"
                :hue="seller.hue"
                size="sm"
                class="shrink-0 rounded-xl"
            />

            <div class="min-w-0 flex-1">
                <p
                    class="truncate text-xs font-extrabold text-[#1c1c22] transition-colors group-hover:text-amber-600"
                >
                    {{ seller.name }}
                </p>
                <div
                    class="flex items-center gap-2 text-[10px] font-medium text-zinc-400"
                >
                    <span>{{ seller.orders }} Terjual</span>
                    <span>•</span>
                    <span
                        class="flex items-center gap-0.5 font-bold text-amber-500"
                    >
                        <Star class="h-3 w-3 fill-amber-400 text-amber-400" />
                        {{ seller.rating }}
                    </span>
                </div>
            </div>

            <div class="flex shrink-0 flex-col items-end gap-0.5 text-right">
                <p class="font-mono text-xs font-black text-amber-600">
                    {{ seller.gmv }}
                </p>
                <Badge
                    v-if="seller.badge"
                    :variant="seller.badge === 'top' ? 'amber' : 'violetSolid'"
                    class="py-0.2 px-2 text-[9px] font-black tracking-wider uppercase"
                >
                    {{ seller.badge }}
                </Badge>
            </div>
        </div>
    </div>
</template>
