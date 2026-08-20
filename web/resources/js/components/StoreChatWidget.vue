<script setup lang="ts">
import { MessageCircle, Send, X } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{ storeSlug: string }>();

interface Msg {
    role: string;
    body: string;
}

const open = ref(false);
const body = ref('');
const messages = ref<Msg[]>([]);
const sending = ref(false);

function xsrfHeaders(): Record<string, string> {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    const token = match?.[1];

    return {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': token ? decodeURIComponent(token) : '',
    };
}

async function load() {
    const res = await fetch(`/${props.storeSlug}/chat`, { credentials: 'same-origin', headers: xsrfHeaders() });
    if (!res.ok) {
        return;
    }
    const data = await res.json();
    messages.value = data.messages ?? [];
}

async function send() {
    const text = body.value.trim();
    if (!text || sending.value) {
        return;
    }
    sending.value = true;
    try {
        const res = await fetch(`/${props.storeSlug}/chat`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: xsrfHeaders(),
            body: JSON.stringify({ body: text }),
        });
        const data = await res.json();
        messages.value = data.messages ?? messages.value;
        body.value = '';
    } finally {
        sending.value = false;
    }
}

onMounted(() => {
    load();
});
</script>

<template>
    <div class="pointer-events-none fixed right-4 bottom-24 z-40 flex flex-col items-end gap-2 sm:bottom-6">
        <div
            v-if="open"
            class="pointer-events-auto flex h-80 w-72 flex-col overflow-hidden rounded-2xl border bg-card shadow-xl"
        >
            <div class="flex items-center justify-between border-b px-3 py-2">
                <p class="text-xs font-bold">Chat toko</p>
                <button type="button" @click="open = false"><X class="h-4 w-4" /></button>
            </div>
            <div class="flex-1 space-y-2 overflow-y-auto p-3 text-xs">
                <div
                    v-for="(m, i) in messages"
                    :key="i"
                    class="rounded-xl px-2 py-1.5"
                    :class="m.role === 'visitor' ? 'ml-6 bg-primary text-primary-foreground' : 'mr-6 bg-muted'"
                >
                    {{ m.body }}
                </div>
                <p v-if="!messages.length" class="text-muted-foreground">Tanya stok, ongkir, atau produk.</p>
            </div>
            <form class="flex gap-1 border-t p-2" @submit.prevent="send">
                <Input v-model="body" class="h-8 text-xs" placeholder="Tulis pesan…" />
                <Button type="submit" size="sm" class="h-8 w-8 p-0" :disabled="sending">
                    <Send class="h-3.5 w-3.5" />
                </Button>
            </form>
        </div>
        <button
            type="button"
            class="pointer-events-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg"
            @click="open = !open"
        >
            <MessageCircle class="h-5 w-5" />
        </button>
    </div>
</template>
