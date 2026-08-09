<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { LogIn, LogOut } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const page = usePage();
const auth = page.props.auth as {
    user?: { name?: string };
    impersonating?: { name?: string; label?: string } | null;
};
const impersonating = computed(() => auth.impersonating ?? null);

function stopImpersonating() {
    router.post('/admin/impersonate/stop');
}
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="-translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-full opacity-0"
    >
        <div
            v-if="impersonating"
            class="sticky top-0 z-[60] flex items-center justify-between gap-3 border-b border-[#8b5cf6]/20 bg-[#6d4fc2] px-4 py-2 text-white shadow-sm"
        >
            <div class="flex min-w-0 items-center gap-2 text-xs font-semibold">
                <LogIn class="h-3.5 w-3.5 shrink-0" />
                <span class="truncate">
                    Mode Peninjauan — Anda masuk sebagai
                    <span class="font-black">{{ auth.user?.name }}</span>
                    ({{ impersonating.label ?? 'Admin' }}:
                    <span class="font-black">{{ impersonating.name }}</span
                    >)
                </span>
            </div>
            <Button
                variant="secondary"
                size="sm"
                class="h-7 shrink-0 gap-1.5 px-2.5 text-[10px] font-bold"
                @click="stopImpersonating"
            >
                <LogOut class="h-3 w-3" />
                Kembali ke {{ impersonating.label ?? 'Admin' }}
            </Button>
        </div>
    </Transition>
</template>