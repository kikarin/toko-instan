<script setup lang="ts">
import { Toaster as Sonner, type ToasterProps } from 'vue-sonner';

const props = defineProps<ToasterProps>();
</script>

<template>
    <Sonner
        v-bind="props"
        class="toaster group"
        :toast-options="{
            classes: {
                toast: 'group toast group-[.toaster]:bg-white group-[.toaster]:text-[#1c1c22] group-[.toaster]:border-black/10 group-[.toaster]:shadow-lg group-[.toaster]:rounded-2xl group-[.toaster]:p-4 font-sans',
                description: 'group-[.toast]:text-[#9090a0] text-xs',
                actionButton: 'group-[.toast]:bg-[#e07c28] group-[.toast]:text-white font-bold rounded-xl text-xs',
                cancelButton: 'group-[.toast]:bg-black/5 group-[.toast]:text-[#4a4a57] font-semibold rounded-xl text-xs',
            },
        }"
    />
</template>
