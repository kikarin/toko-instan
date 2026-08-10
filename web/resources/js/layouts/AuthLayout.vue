<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';

withDefaults(
    defineProps<{
        /** Extra vertical padding for taller forms (e.g. register). */
        spacious?: boolean;
    }>(),
    { spacious: false },
);
</script>

<template>
    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#f5f4f0] p-4 font-sans select-none"
        :class="spacious ? 'py-10' : ''"
    >
        <div
            class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-[#e07c28]/10 blur-3xl"
        />
        <div
            class="pointer-events-none absolute -right-24 -bottom-24 h-96 w-96 rounded-full bg-[#6d4fc2]/10 blur-3xl"
        />

        <div class="relative z-10 flex w-full max-w-md flex-col gap-5">
            <slot />
        </div>

        <Toaster position="top-right" />
    </div>
</template>
