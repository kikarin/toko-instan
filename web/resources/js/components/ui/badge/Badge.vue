<script setup lang="ts">
import { computed } from 'vue';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const badgeVariants = cva(
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
        variants: {
            variant: {
                default: 'bg-[#1c1c22] text-white hover:bg-[#1c1c22]/80',
                secondary: 'bg-[#0000000a] text-[#4a4a57] hover:bg-[#00000010]',
                destructive: 'bg-[#e0405a] text-white hover:bg-[#e0405a]/80',
                outline: 'text-[#1c1c22] border border-[#00000015]',
                amber: 'bg-[#e07c281a] text-[#e07c28] border border-[#e07c2830]',
                amberSolid: 'bg-[#e07c28] text-white',
                violet: 'bg-[#6d4fc21a] text-[#6d4fc2] border border-[#6d4fc230]',
                violetSolid: 'bg-[#6d4fc2] text-white',
                teal: 'bg-[#0e9f8a1a] text-[#0e9f8a] border border-[#0e9f8a30]',
                sky: 'bg-[#3b82f61a] text-[#3b82f6] border border-[#3b82f630]',
                green: 'bg-[#22a15a1a] text-[#22a15a] border border-[#22a15a30]',
                rose: 'bg-[#e0405a1a] text-[#e0405a] border border-[#e0405a30]',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    }
);

interface Props {
    variant?: VariantProps<typeof badgeVariants>['variant'];
    class?: string;
}

const props = defineProps<Props>();
const classes = computed(() => cn(badgeVariants({ variant: props.variant }), props.class));
</script>

<template>
    <div :class="classes">
        <slot />
    </div>
</template>
