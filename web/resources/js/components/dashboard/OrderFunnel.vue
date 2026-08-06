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
</script>

<template>
    <div class="flex flex-col gap-2.5">
        <div
            v-for="(item, i) in orderFlow"
            :key="i"
            class="flex cursor-default items-center gap-2.5 transition-all duration-150"
            @mouseenter="hoveredIndex = i"
            @mouseleave="hoveredIndex = null"
        >
            <span
                class="w-18 shrink-0 text-right font-mono text-[11px] transition-colors duration-150"
                :style="{ color: hoveredIndex === i ? item.c : '#9090a0' }"
            >
                {{ item.label }}
            </span>

            <div class="h-5 flex-1 overflow-hidden rounded-full bg-[#f5f4f0]">
                <div
                    class="h-full rounded-full transition-all duration-300 ease-out"
                    :style="{
                        width: `${Math.max(4, (item.n / totalOrders) * 100)}%`,
                        backgroundColor:
                            hoveredIndex === i ? item.c : `${item.c}60`,
                    }"
                />
            </div>

            <span
                class="w-11 shrink-0 text-right font-mono text-[11px] transition-colors duration-150"
                :style="{ color: hoveredIndex === i ? '#1c1c22' : '#9090a0' }"
            >
                {{ item.n.toLocaleString('id') }}
            </span>
        </div>
    </div>
</template>
