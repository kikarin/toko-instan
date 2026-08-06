<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    src?: string;
    alt?: string;
    fallback?: string;
    hue?: number;
    size?: 'sm' | 'md' | 'lg';
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    fallback: 'U',
    hue: 220,
    size: 'md',
});

const sizeClasses = {
    sm: 'w-7 h-7 text-xs',
    md: 'w-9 h-9 text-xs',
    lg: 'w-12 h-12 text-sm',
};
</script>

<template>
    <div
        :class="
            cn(
                'relative flex shrink-0 overflow-hidden rounded-full font-bold items-center justify-center select-none shadow-2xs',
                sizeClasses[props.size],
                $props.class
            )
        "
        :style="{
            backgroundColor: `hsl(${props.hue}, 48%, 88%)`,
            color: `hsl(${props.hue}, 48%, 28%)`,
            border: `1.5px solid hsl(${props.hue}, 48%, 78%)`,
        }"
    >
        <img v-if="src" :src="src" :alt="alt" class="h-full w-full object-cover" />
        <span v-else>{{ fallback }}</span>
    </div>
</template>
