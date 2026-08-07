<script setup lang="ts">
import { ref, computed } from 'vue';

interface FlowItem {
    label: string;
    n: number;
    c: string;
}

interface Props {
    orderFlow: FlowItem[];
}

const props = defineProps<Props>();

const hoveredIndex = ref<number | null>(null);
const totalOrders = computed(() =>
    props.orderFlow.reduce((acc, curr) => acc + curr.n, 0),
);

function getPercent(n: number) {
    if (!totalOrders.value) return '0%';
    return `${((n / totalOrders.value) * 100).toFixed(1)}%`;
}
</script>

<template>
    <div class="flex flex-col gap-3">
        <div
            v-for="(item, i) in orderFlow"
            :key="i"
            class="group flex cursor-pointer items-center gap-3 transition-all duration-200 p-2 rounded-2xl hover:bg-zinc-50"
            @mouseenter="hoveredIndex = i"
            @mouseleave="hoveredIndex = null"
        >
            <div class="w-24 shrink-0 text-left">
                <span
                    class="font-extrabold text-xs transition-colors block"
                    :style="{ color: hoveredIndex === i ? item.c : '#1c1c22' }"
                >
                    {{ item.label }}
                </span>
                <span class="text-[10px] text-zinc-400 font-mono font-bold">{{ getPercent(item.n) }}</span>
            </div>

            <div class="h-6 flex-1 overflow-hidden rounded-xl bg-zinc-100 p-0.5 border border-black/5">
                <div
                    class="h-full rounded-lg transition-all duration-500 ease-out shadow-xs flex items-center justify-end pr-2"
                    :style="{
                        width: `${Math.max(6, (item.n / totalOrders) * 100)}%`,
                        backgroundColor: item.c,
                    }"
                />
            </div>

            <span
                class="w-14 shrink-0 text-right font-mono text-xs font-black transition-colors"
                :style="{ color: hoveredIndex === i ? item.c : '#1c1c22' }"
            >
                {{ item.n.toLocaleString('id') }}
            </span>
        </div>
    </div>
</template>
