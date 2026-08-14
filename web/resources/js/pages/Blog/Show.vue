<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SeoHead, { type SeoMeta } from '@/components/SeoHead.vue';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

defineProps<{
    store: { name: string; slug: string };
    post: {
        title: string;
        slug: string;
        excerpt: string | null;
        body: string | null;
        cover_url: string | null;
        category: string | null;
        author: string | null;
        tags: string[];
        published_at: string | null;
    };
    seo: SeoMeta;
}>();
</script>

<template>
    <SeoHead :seo="seo" />
    <StorefrontLayout>
        <article class="mx-auto w-full max-w-3xl px-4 py-8 pb-28">
            <Link :href="`/${store.slug}/blog`" class="text-sm text-muted-foreground hover:underline">← Blog</Link>
            <p class="mt-4 text-xs text-muted-foreground">{{ post.published_at }} · {{ post.author }} · {{ post.category }}</p>
            <h1 class="mt-2 text-3xl font-extrabold">{{ post.title }}</h1>
            <img v-if="post.cover_url" :src="post.cover_url" alt="" class="mt-4 w-full rounded-2xl object-cover" />
            <div class="mt-6 whitespace-pre-wrap text-sm leading-relaxed">{{ post.body }}</div>
            <p v-if="post.tags.length" class="mt-6 text-xs text-muted-foreground">Tag: {{ post.tags.join(', ') }}</p>
        </article>
    </StorefrontLayout>
</template>
