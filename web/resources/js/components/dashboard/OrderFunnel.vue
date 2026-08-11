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
    if (!totalOrders.value) {
        return '0%';
    }

    return `${((n / totalOrders.value) * 100).toFixed(1)}%`;
}
</script>

<template>
    <div class="flex flex-col gap-3">
        <div
            v-for="(item, i) in orderFlow"
            :key="i"
            class="group flex cursor-pointer items-center gap-3 rounded-2xl p-2 transition-all duration-200 hover:bg-accent/10"
            @mouseenter="hoveredIndex = i"
            @mouseleave="hoveredIndex = null"
        >
            <div class="w-24 shrink-0 text-left">
                <span
                    class="block text-xs font-extrabold transition-colors"
                    :style="{ color: hoveredIndex === i ? item.c : 'var(--foreground)' }"
                >
                    {{ item.label }}
                </span>
                <span class="font-mono text-[10px] font-bold text-muted-foreground">{{
                    getPercent(item.n)
                }}</span>
            </div>

            <div
                class="h-6 flex-1 overflow-hidden rounded-xl border border-border bg-muted p-0.5"
            >
                <div
                    class="flex h-full items-center justify-end rounded-lg pr-2 shadow-xs transition-all duration-500 ease-out"
                    :style="{
                        width: `${Math.max(6, (item.n / totalOrders) * 100)}%`,
                        backgroundColor: item.c,
                    }"
                />
            </div>

            <span
                class="w-14 shrink-0 text-right font-mono text-xs font-black transition-colors"
                :style="{ color: hoveredIndex === i ? item.c : 'var(--foreground)' }"
            >
                {{ item.n.toLocaleString('id') }}
            </span>
        </div>
    </div>
</template>
