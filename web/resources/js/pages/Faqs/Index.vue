<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    faqs: Array<{ id: number; question: string; answer: string; source: string }>;
}>();

const form = useForm({ question: '', answer: '' });
</script>

<template>
    <Head title="FAQ toko" />
    <AppLayout title="FAQ" activePage="FAQ">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 sm:p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-extrabold">FAQ toko</h1>
                <Button class="gap-1" @click="router.post('/faqs/generate')"><Sparkles class="h-4 w-4" /> Generate AI</Button>
            </div>
            <Card>
                <CardContent class="grid gap-2 p-4">
                    <Input v-model="form.question" placeholder="Pertanyaan" />
                    <Textarea v-model="form.answer" rows="3" placeholder="Jawaban" />
                    <Button :disabled="form.processing" @click="form.post('/faqs')">Tambah</Button>
                </CardContent>
            </Card>
            <Card v-for="f in props.faqs" :key="f.id">
                <CardContent class="flex justify-between gap-3 p-4">
                    <div>
                        <p class="text-sm font-bold">{{ f.question }}</p>
                        <p class="text-xs text-muted-foreground">{{ f.answer }}</p>
                        <p class="mt-1 text-[10px] uppercase">{{ f.source }}</p>
                    </div>
                    <Button variant="ghost" size="sm" @click="router.delete(`/faqs/${f.id}`)">Hapus</Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
