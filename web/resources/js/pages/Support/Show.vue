<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    ticket: {
        id: number;
        subject: string;
        body: string;
        status: string;
        replies: Array<{ id: number; body: string; author: string; created_at: string | null }>;
    };
}>();

const form = useForm({ body: '' });
</script>

<template>
    <Head :title="ticket.subject" />
    <AppLayout title="Tiket" activePage="Tiket">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 p-4 sm:p-8">
            <h1 class="text-xl font-extrabold">{{ ticket.subject }}</h1>
            <p class="text-sm">{{ ticket.body }}</p>
            <p class="text-xs uppercase text-muted-foreground">{{ ticket.status }}</p>
            <Card v-for="r in ticket.replies" :key="r.id">
                <CardContent class="p-4 text-sm">
                    <p class="text-xs font-bold">{{ r.author }} · {{ r.created_at }}</p>
                    <p>{{ r.body }}</p>
                </CardContent>
            </Card>
            <Textarea v-model="form.body" rows="3" />
            <div class="flex gap-2">
                <Button :disabled="form.processing" @click="form.post(`/support/${ticket.id}/replies`)">Balas</Button>
                <Button variant="outline" @click="router.patch(`/support/${ticket.id}/status`, { status: 'closed' })">Tutup</Button>
            </div>
        </div>
    </AppLayout>
</template>
