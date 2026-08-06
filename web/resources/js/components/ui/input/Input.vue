<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    modelValue?: string | number;
    class?: string;
}

defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

function onInput(event: Event) {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
}
</script>

<template>
    <input
        :value="modelValue"
        @input="onInput"
        :class="
            cn(
                'flex h-10 w-full rounded-xl border border-[#00000015] bg-white px-3.5 py-2 text-sm text-[#1c1c22] placeholder:text-[#9090a0] focus:border-[#e07c28] focus:outline-none focus:ring-2 focus:ring-[#e07c28]/20 disabled:cursor-not-allowed disabled:opacity-50 transition-all duration-150',
                $props.class
            )
        "
    />
</template>
