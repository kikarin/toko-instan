<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
    class?: string;
}>();
</script>

<template>
    <div
        :class="
            cn(
                'rounded-2xl border border-[#00000012] bg-white text-[#1c1c22] shadow-[0_1px_4px_rgba(0,0,0,0.04)] transition-all duration-200',
                $props.class
            )
        "
    >
        <slot />
    </div>
</template>
