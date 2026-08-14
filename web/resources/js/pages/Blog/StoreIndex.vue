<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SeoHead, { type SeoMeta } from '@/components/SeoHead.vue';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

defineProps<{
    store: { name: string; slug: string };
    posts: Array<{
        title: string;
        slug: string;
        excerpt: string | null;
        cover_url: string | null;
        category: string | null;
        author: string | null;
        published_at: string | null;
    }>;
    seo: SeoMeta;
}>();
</script>

<template>
    <SeoHead :seo="seo" />
    <StorefrontLayout>
        <main class="mx-auto flex w-full max-w-3xl flex-col gap-6 px-4 py-8 pb-28">
            <h1 class="text-2xl font-extrabold">Blog {{ store.name }}</h1>
            <article v-for="p in posts" :key="p.slug" class="rounded-2xl border bg-card p-4">
                <p class="text-xs text-muted-foreground">{{ p.published_at }} · {{ p.author }}</p>
                <Link :href="`/${store.slug}/blog/${p.slug}`" class="mt-1 block text-lg font-bold hover:underline">
                    {{ p.title }}
                </Link>
                <p class="mt-1 text-sm text-muted-foreground">{{ p.excerpt }}</p>
            </article>
            <p v-if="!posts.length" class="text-sm text-muted-foreground">Belum ada artikel.</p>
        </main>
    </StorefrontLayout>
</template>
