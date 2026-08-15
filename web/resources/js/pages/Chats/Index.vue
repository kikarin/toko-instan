<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { MessageCircle } from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    chats: Array<{
        id: number;
        visitor_name: string;
        last_message: string | null;
        updated_at: string | null;
    }>;
}>();
</script>

<template>
    <Head title="Chat pelanggan" />
    <AppLayout title="Chat" activePage="Chat">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 p-4 sm:p-8">
            <h1 class="text-xl font-extrabold">Chat pelanggan</h1>
            <p class="text-sm text-muted-foreground">Auto-reply dasar di storefront. Riwayat percakapan:</p>
            <Card v-for="c in chats" :key="c.id">
                <CardContent class="flex items-start gap-3 p-4">
                    <MessageCircle class="mt-0.5 h-4 w-4" />
                    <div>
                        <p class="text-sm font-bold">{{ c.visitor_name }}</p>
                        <p class="text-xs text-muted-foreground">{{ c.last_message }}</p>
                        <p class="mt-1 text-[10px] text-muted-foreground">{{ c.updated_at }}</p>
                    </div>
                </CardContent>
            </Card>
            <p v-if="!chats.length" class="text-sm text-muted-foreground">Belum ada chat.</p>
        </div>
    </AppLayout>
</template>
