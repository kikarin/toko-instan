<script setup lang="ts">
import { AlertTriangle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Props {
    open: boolean;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    tone?: 'danger' | 'primary';
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    description: '',
    confirmLabel: 'Ya, Lanjutkan',
    cancelLabel: 'Batal',
    tone: 'danger',
    loading: false,
});

const emit = defineEmits<{
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

function onBackdrop() {
    if (!props.loading) {
        emit('cancel');
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                    @click="onBackdrop"
                />
                <div
                    class="relative z-10 w-full max-w-sm rounded-2xl border border-black/8 bg-white p-5 shadow-2xl"
                >
                    <div class="flex items-start gap-3.5">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="
                                tone === 'danger'
                                    ? 'bg-red-50 text-red-500'
                                    : 'bg-[#6d4fc21a] text-[#6d4fc2]'
                            "
                        >
                            <AlertTriangle class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-sm font-extrabold text-[#1c1c22]">
                                {{ title }}
                            </h2>
                            <p
                                v-if="description"
                                class="mt-1 text-xs leading-relaxed text-[#9090a0]"
                            >
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            class="text-xs font-bold"
                            :disabled="loading"
                            @click="emit('cancel')"
                        >
                            {{ cancelLabel }}
                        </Button>
                        <Button
                            :variant="tone === 'danger' ? 'destructive' : 'amber'"
                            size="sm"
                            class="text-xs font-bold"
                            :disabled="loading"
                            @click="emit('confirm')"
                        >
                            {{ loading ? 'Memproses...' : confirmLabel }}
                        </Button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>