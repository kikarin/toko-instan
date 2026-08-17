<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps<{
    tickets?: Array<{ id: number; subject: string; status: string; author?: string; created_at: string | null }>;
    ticket?: {
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
    <Head title="Tiket" />
    <AdminLayout activePage="Tiket">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 p-6">
            <template v-if="ticket">
                <h1 class="text-xl font-extrabold">{{ ticket.subject }}</h1>
                <p class="text-sm">{{ ticket.body }}</p>
                <Card v-for="r in ticket.replies" :key="r.id">
                    <CardContent class="p-4 text-sm">{{ r.author }}: {{ r.body }}</CardContent>
                </Card>
                <Textarea v-model="form.body" rows="3" />
                <div class="flex gap-2">
                    <Button @click="form.post(`/admin/tickets/${ticket.id}/replies`)">Balas</Button>
                    <Button variant="outline" @click="router.patch(`/admin/tickets/${ticket.id}/status`, { status: 'closed' })">Tutup</Button>
                </div>
            </template>
            <template v-else>
                <h1 class="text-xl font-extrabold">Tiket support</h1>
                <Link v-for="t in tickets" :key="t.id" :href="`/admin/tickets/${t.id}`" class="block">
                    <Card>
                        <CardContent class="flex justify-between p-4 text-sm">
                            <span>{{ t.subject }}</span>
                            <span class="uppercase">{{ t.status }}</span>
                        </CardContent>
                    </Card>
                </Link>
            </template>
        </div>
    </AdminLayout>
</template>
