<script setup lang="ts">
import { cn } from '@/lib/utils';
defineProps<{ class?: string }>();
</script>

<template>
    <h3 :class="cn('text-base font-bold leading-none tracking-tight text-[#1c1c22]', $props.class)">
        <slot />
    </h3>
</template>
