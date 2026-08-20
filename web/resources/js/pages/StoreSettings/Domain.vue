<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    store: { name: string; slug: string; custom_domain: string | null; custom_domain_status: string | null } | null;
    is_premium: boolean;
}>();

const form = useForm({ custom_domain: props.store?.custom_domain ?? '' });
</script>

<template>
    <Head title="Custom domain" />
    <AppLayout title="Custom domain" activePage="Domain">
        <div class="mx-auto flex w-full max-w-xl flex-col gap-6 p-4 sm:p-8">
            <h1 class="text-xl font-extrabold">Custom domain</h1>
            <p class="text-sm text-muted-foreground">Khusus Premium. Arahkan CNAME ke platform, lalu daftarkan hostname (Cloudflare API jika token terisi).</p>
            <p v-if="!is_premium" class="text-sm text-destructive">Upgrade Premium untuk memakai fitur ini.</p>
            <Card>
                <CardContent class="flex flex-col gap-3 p-6">
                    <Input v-model="form.custom_domain" placeholder="shop.domainkamu.com" :disabled="!is_premium" />
                    <p v-if="store?.custom_domain_status" class="text-xs uppercase">Status: {{ store.custom_domain_status }}</p>
                    <Button :disabled="!is_premium || form.processing" @click="form.put('/store-domain')">Simpan</Button>
                    <Button v-if="store?.custom_domain" variant="outline" @click="router.delete('/store-domain')">Lepas domain</Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
