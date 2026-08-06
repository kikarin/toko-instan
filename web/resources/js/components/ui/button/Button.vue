<script setup lang="ts">
import { computed } from 'vue';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
    'inline-flex items-center justify-center whitespace-nowrap rounded-xl text-sm font-semibold transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 active:scale-[0.98] cursor-pointer',
    {
        variants: {
            variant: {
                default: 'bg-[#1c1c22] text-white hover:bg-[#2e2e38] shadow-sm',
                amber: 'bg-gradient-to-r from-[#e07c28] to-[#c2500a] text-white hover:opacity-95 shadow-md shadow-[#e07c28]/20',
                outline: 'border border-[#00000012] bg-white text-[#1c1c22] hover:bg-[#faf9f6] hover:border-[#00000020] shadow-2xs',
                secondary: 'bg-[#00000008] text-[#4a4a57] hover:bg-[#00000010]',
                ghost: 'text-[#4a4a57] hover:bg-[#00000008] hover:text-[#1c1c22]',
                link: 'text-[#e07c28] underline-offset-4 hover:underline',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-8 rounded-lg px-3 text-xs',
                lg: 'h-12 rounded-xl px-6 text-base',
                icon: 'h-9 w-9 p-0 rounded-xl',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    }
);

interface Props {
    variant?: VariantProps<typeof buttonVariants>['variant'];
    size?: VariantProps<typeof buttonVariants>['size'];
    class?: string;
    as?: string;
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
});

const classes = computed(() => cn(buttonVariants({ variant: props.variant, size: props.size }), props.class));
</script>

<template>
    <component :is="as" :class="classes">
        <slot />
    </component>
</template>
