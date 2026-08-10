<script setup lang="ts">
import { ArrowUpRight, ArrowDownRight, Minus } from 'lucide-vue-next';
import { Card } from '@/components/ui/card';

interface Props {
    label: string;
    value: string;
    delta: string;
    up: boolean | null;
    sub: string;
    color: string;
    softColor: string;
    sparkData: number[];
    icon: any;
}

defineProps<Props>();

// Generate SVG paths for sparkline
const w = 84;
const h = 28;

function getSparkPaths(data: number[]) {
    if (!data || data.length === 0) {
        return { area: '', pts: '' };
    }

    const min = Math.min(...data);
    const max = Math.max(...data);
    const getX = (i: number) => (i / (data.length - 1)) * w;
    const getY = (v: number) =>
        h - ((v - min) / (max - min || 1)) * (h - 6) - 3;

    const pts = data.map((v, i) => `${getX(i)},${getY(v)}`).join(' ');
    const area =
        `M${getX(0)},${getY(data[0])} ` +
        data
            .slice(1)
            .map((v, i) => `L${getX(i + 1)},${getY(v)}`)
            .join(' ') +
        ` L${getX(data.length - 1)},${h} L0,${h} Z`;

    return { area, pts };
}
</script>

<template>
    <Card
        class="group relative flex flex-col justify-between gap-2.5 overflow-hidden rounded-2xl border border-border bg-card p-4 transition-all duration-300 hover:-translate-y-1 hover:border-primary/20 hover:shadow-xl"
    >
        <div class="flex items-center justify-between">
            <span
                class="text-xs font-bold text-muted-foreground transition-colors group-hover:text-foreground"
                >{{ label }}</span
            >
            <div
                class="flex h-9 w-9 items-center justify-center rounded-xl text-base shadow-xs transition-transform duration-300 group-hover:scale-110"
                :style="{ backgroundColor: softColor }"
            >
                <component
                    :is="icon"
                    class="h-4.5 w-4.5"
                    :style="{ color: color }"
                />
            </div>
        </div>

        <p
            class="font-mono text-2xl leading-none font-black tracking-tight text-foreground"
        >
            {{ value }}
        </p>

        <div class="flex items-center justify-between pt-1">
            <span
                class="inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 font-mono text-[11px] font-extrabold"
                :class="[
                    up === null
                        ? 'bg-emerald-500/10 text-emerald-600'
                        : up
                          ? 'bg-emerald-500/10 text-emerald-600'
                          : 'bg-destructive/10 text-destructive',
                ]"
            >
                <ArrowUpRight v-if="up === true" class="h-3.5 w-3.5" />
                <ArrowDownRight v-else-if="up === false" class="h-3.5 w-3.5" />
                <Minus v-else class="h-3 w-3" />
                {{ delta }}
            </span>

            <!-- Sparkline SVG -->
            <svg :viewBox="`0 0 ${w} ${h}`" class="block h-[28px] w-[84px]">
                <defs>
                    <linearGradient
                        :id="`sg-${label.toLowerCase().replace(/[^a-z0-9]/g, '')}`"
                        x1="0"
                        y1="0"
                        x2="0"
                        y2="1"
                    >
                        <stop
                            offset="0%"
                            :stop-color="color"
                            stop-opacity="0.35"
                        />
                        <stop
                            offset="100%"
                            :stop-color="color"
                            stop-opacity="0"
                        />
                    </linearGradient>
                </defs>
                <path
                    :d="getSparkPaths(sparkData).area"
                    :fill="`url(#sg-${label.toLowerCase().replace(/[^a-z0-9]/g, '')})`"
                />
                <polyline
                    :points="getSparkPaths(sparkData).pts"
                    fill="none"
                    :stroke="color"
                    stroke-width="2"
                    stroke-linejoin="round"
                    stroke-linecap="round"
                />
            </svg>
        </div>

        <p
            class="border-t border-border/50 pt-1.5 text-[10px] font-medium text-muted-foreground/80"
        >
            {{ sub }}
        </p>
    </Card>
</template>
