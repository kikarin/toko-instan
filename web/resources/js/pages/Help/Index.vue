<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    articles: Array<{ slug: string; title: string; category: string; body: string }>;
    store_faqs: Array<{ question: string; answer: string }>;
}>();
</script>

<template>
    <Head title="Bantuan" />
    <AppLayout title="Bantuan" activePage="Bantuan">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 sm:p-8">
            <h1 class="text-xl font-extrabold">Knowledge base</h1>
            <Card v-for="a in articles" :key="a.slug">
                <CardContent class="p-4">
                    <p class="text-[10px] font-bold uppercase text-muted-foreground">{{ a.category }}</p>
                    <h2 class="text-sm font-extrabold">{{ a.title }}</h2>
                    <p class="mt-1 text-xs text-muted-foreground">{{ a.body }}</p>
                </CardContent>
            </Card>
            <h2 class="text-lg font-extrabold">FAQ toko Anda</h2>
            <Card v-for="(f, i) in store_faqs" :key="i">
                <CardContent class="p-4">
                    <p class="text-sm font-bold">{{ f.question }}</p>
                    <p class="text-xs text-muted-foreground">{{ f.answer }}</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
