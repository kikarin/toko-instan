<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    tickets: Array<{ id: number; subject: string; status: string; created_at: string | null }>;
}>();

const form = useForm({ subject: '', body: '', priority: 'normal' });
</script>

<template>
    <Head title="Tiket bantuan" />
    <AppLayout title="Tiket" activePage="Tiket">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 sm:p-8">
            <h1 class="text-xl font-extrabold">Customer support</h1>
            <Card>
                <CardContent class="grid gap-2 p-4">
                    <Input v-model="form.subject" placeholder="Subjek" />
                    <Textarea v-model="form.body" rows="4" placeholder="Deskripsi" />
                    <Button :disabled="form.processing" @click="form.post('/support')">Buat tiket</Button>
                </CardContent>
            </Card>
            <Link v-for="t in props.tickets" :key="t.id" :href="`/support/${t.id}`" class="block">
                <Card>
                    <CardContent class="flex justify-between p-4 text-sm">
                        <span class="font-bold">{{ t.subject }}</span>
                        <span class="text-xs uppercase text-muted-foreground">{{ t.status }}</span>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </AppLayout>
</template>
