<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface SeoMeta {
    title: string;
    description: string;
    canonical: string;
    og_title: string;
    og_description: string;
    og_image: string | null;
    og_url: string;
    og_type: string;
    json_ld?: Record<string, unknown> | null;
}

const props = defineProps<{
    seo?: SeoMeta | null;
    fallbackTitle?: string;
}>();

const title = computed(() => props.seo?.title ?? props.fallbackTitle ?? 'Toko Instan');
const jsonLd = computed(() =>
    props.seo?.json_ld
        ? JSON.stringify(props.seo.json_ld)
              .replace(/</g, '\\u003c')
              .replace(/>/g, '\\u003e')
        : null,
);
</script>

<template>
    <Head :title="title">
        <meta v-if="seo?.description" head-key="description" name="description" :content="seo.description" />
        <link v-if="seo?.canonical" head-key="canonical" rel="canonical" :href="seo.canonical" />
        <meta v-if="seo?.og_title" head-key="og:title" property="og:title" :content="seo.og_title" />
        <meta v-if="seo?.og_description" head-key="og:description" property="og:description" :content="seo.og_description" />
        <meta v-if="seo?.og_image" head-key="og:image" property="og:image" :content="seo.og_image" />
        <meta v-if="seo?.og_url" head-key="og:url" property="og:url" :content="seo.og_url" />
        <meta v-if="seo?.og_type" head-key="og:type" property="og:type" :content="seo.og_type" />
        <meta v-if="seo?.og_title" head-key="twitter:title" name="twitter:title" :content="seo.og_title" />
        <meta v-if="seo?.og_description" head-key="twitter:description" name="twitter:description" :content="seo.og_description" />
        <component v-if="jsonLd" :is="'script'" type="application/ld+json" v-html="jsonLd" />
    </Head>
</template>
