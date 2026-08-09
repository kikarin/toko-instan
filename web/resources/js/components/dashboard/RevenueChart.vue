<script setup lang="ts">
import { ref, computed } from 'vue';

interface DataPoint {
    i: number;
    v: number;
}

interface Props {
    data: DataPoint[];
    labels: string[];
}

const props = defineProps<Props>();

const W = 800;
const H = 200;
const pl = 4;
const pr = 4;
const pt = 16;
const pb = 28;

const hoveredIndex = ref<number | null>(null);

const vals = computed(() => props.data.map((d) => d.v));
const maxVal = computed(() =>
    props.data.length ? Math.max(...vals.value) * 1.08 : 0,
);
const minVal = computed(() =>
    props.data.length ? Math.min(...vals.value) * 0.92 : 0,
);

const x = (i: number) =>
    pl + (props.data.length > 1 ? i / (props.data.length - 1) : 0) * (W - pl - pr);
const y = (v: number) =>
    pt +
    (maxVal.value - minVal.value
        ? ((maxVal.value - v) / (maxVal.value - minVal.value)) * (H - pt - pb)
        : (H - pt - pb) / 2);

const pointsStr = computed(() =>
    props.data.map((d) => `${x(d.i)},${y(d.v)}`).join(' '),
);

const areaD = computed(() => {
    if (props.data.length === 0) {
        return '';
    }

    return (
        `M${x(0)},${y(props.data[0].v)} ` +
        props.data
            .slice(1)
            .map((d) => `L${x(d.i)},${y(d.v)}`)
            .join(' ') +
        ` L${x(props.data.length - 1)},${H - pb} L${x(0)},${H - pb} Z`
    );
});

const step = computed(() =>
    props.data.length ? Math.ceil(props.data.length / 7) : 1,
);

function fmtRp(n: number) {
    if (n >= 1_000_000_000) {
        return `Rp ${(n / 1_000_000_000).toFixed(1)}M`;
    }

    if (n >= 1_000_000) {
        return `Rp ${(n / 1_000_000).toFixed(1)}Jt`;
    }

    if (n >= 1_000) {
        return `Rp ${(n / 1_000).toFixed(0)}rb`;
    }

    return `Rp ${n}`;
}
</script>

<template>
    <div v-if="!props.data.length" class="flex flex-col items-center gap-2 py-14 text-center">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3v18h18" />
            <path d="M7 15l4-6 4 3 5-7" />
        </svg>
        <p class="text-sm font-bold text-zinc-600">Belum ada data omzet</p>
        <p class="text-xs text-zinc-400">
            Grafik akan muncul saat toko Anda memiliki penjualan.
        </p>
    </div>
    <svg v-else :viewBox="`0 0 ${W} ${H}`" class="h-auto w-full font-mono">
        <defs>
            <linearGradient id="ra" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#e07c28" stop-opacity="0.18" />
                <stop offset="100%" stop-color="#e07c28" stop-opacity="0" />
            </linearGradient>
            <linearGradient id="rl" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%" stop-color="#f59e0b" />
                <stop offset="100%" stop-color="#e07c28" />
            </linearGradient>
        </defs>

        <!-- Grid Lines -->
        <g class="opacity-50">
            <line
                v-for="f in [0.25, 0.5, 0.75]"
                :key="f"
                :x1="pl"
                :x2="W - pr"
                :y1="y(minVal + (maxVal - minVal) * f)"
                :y2="y(minVal + (maxVal - minVal) * f)"
                stroke="rgba(0,0,0,0.07)"
                stroke-width="1"
            />
        </g>

        <!-- Area & Polyline -->
        <path :d="areaD" fill="url(#ra)" />
        <polyline
            :points="pointsStr"
            fill="none"
            stroke="url(#rl)"
            stroke-width="2.5"
            stroke-linejoin="round"
            stroke-linecap="round"
        />

        <!-- Hover Interactivity -->
        <g
            v-for="(d, i) in props.data"
            :key="i"
            @mouseenter="hoveredIndex = i"
            @mouseleave="hoveredIndex = null"
            class="cursor-crosshair"
        >
            <rect
                :x="x(i) - 12"
                :y="pt"
                :width="24"
                :height="H - pt - pb"
                fill="transparent"
            />

            <template v-if="hoveredIndex === i">
                <line
                    :x1="x(i)"
                    :x2="x(i)"
                    :y1="pt"
                    :y2="H - pb"
                    stroke="#e07c28"
                    stroke-width="1.5"
                    stroke-dasharray="3 3"
                    opacity="0.6"
                />
                <circle
                    :cx="x(i)"
                    :cy="y(d.v)"
                    r="4.5"
                    fill="#e07c28"
                    stroke="#ffffff"
                    stroke-width="2.5"
                />
                <rect
                    :x="Math.min(x(i) - 44, W - 96)"
                    :y="y(d.v) - 42"
                    width="88"
                    height="32"
                    rx="8"
                    fill="#ffffff"
                    stroke="rgba(0,0,0,0.1)"
                    stroke-width="1"
                    style="filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08))"
                />
                <text
                    :x="Math.min(x(i) - 44, W - 96) + 44"
                    :y="y(d.v) - 25"
                    text-anchor="middle"
                    font-size="9"
                    fill="#9090a0"
                >
                    {{ labels[i] }}
                </text>
                <text
                    :x="Math.min(x(i) - 44, W - 96) + 44"
                    :y="y(d.v) - 12"
                    text-anchor="middle"
                    font-size="10"
                    fill="#e07c28"
                    font-weight="700"
                >
                    {{ fmtRp(d.v) }}
                </text>
            </template>
        </g>

        <!-- X Axis Labels -->
        <template v-for="(l, i) in labels" :key="i">
            <text
                v-if="i % step === 0"
                :x="x(i)"
                :y="H - 8"
                text-anchor="middle"
                font-size="9"
                fill="#c8c8d5"
                class="select-none"
            >
                {{ l }}
            </text>
        </template>
    </svg>
</template>
