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
const w = 72;
const h = 26;

function getSparkPaths(data: number[]) {
    if (!data || data.length === 0) {
        return { area: '', pts: '' };
    }

    const min = Math.min(...data);
    const max = Math.max(...data);
    const getX = (i: number) => (i / (data.length - 1)) * w;
    const getY = (v: number) =>
        h - ((v - min) / (max - min || 1)) * (h - 4) - 2;

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
        class="flex flex-col gap-2.5 p-4 transition-all duration-200 hover:border-[#00000020] hover:shadow-md"
    >
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium text-[#9090a0]">{{ label }}</p>
            <div
                class="flex h-7 w-7 items-center justify-center rounded-lg text-sm shadow-2xs"
                :style="{ backgroundColor: softColor }"
            >
                <component
                    :is="icon"
                    class="h-4 w-4"
                    :style="{ color: color }"
                />
            </div>
        </div>

        <p
            class="font-mono text-xl leading-none font-extrabold tracking-tight text-[#1c1c22]"
        >
            {{ value }}
        </p>

        <div class="flex items-center justify-between pt-0.5">
            <span
                class="flex items-center gap-0.5 font-mono text-[11px] font-bold"
                :class="[
                    up === null
                        ? 'text-[#0e9f8a]'
                        : up
                          ? 'text-[#22a15a]'
                          : 'text-[#e0405a]',
                ]"
            >
                <ArrowUpRight v-if="up === true" class="h-3.5 w-3.5" />
                <ArrowDownRight v-else-if="up === false" class="h-3.5 w-3.5" />
                <Minus v-else class="h-3 w-3" />
                {{ delta }}
            </span>

            <!-- Sparkline SVG -->
            <svg :viewBox="`0 0 ${w} ${h}`" class="block h-[26px] w-[72px]">
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
                            stop-opacity="0.3"
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
                    stroke-width="1.8"
                    stroke-linejoin="round"
                    stroke-linecap="round"
                />
            </svg>
        </div>

        <p class="text-[10px] text-[#c8c8d5]">{{ sub }}</p>
    </Card>
</template>
