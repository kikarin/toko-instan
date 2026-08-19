<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { KeyRound, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

interface TokenRow {
    id: number;
    name: string;
    last_used_at: string | null;
    created_at: string | null;
}

const props = defineProps<{
    tokens: TokenRow[];
    docs_url: string;
}>();

const page = usePage();
const plain = (page.props.flash as { plain_api_token?: string | null } | undefined)?.plain_api_token
    ?? null;

const form = useForm({ name: 'Integrasi' });

function createToken() {
    form.post('/developer/tokens');
}
</script>

<template>
    <Head title="API Developer" />
    <AppLayout title="API Developer" activePage="API">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 sm:p-8">
            <div>
                <h1 class="text-xl font-extrabold">API key tenant</h1>
                <p class="text-sm text-muted-foreground">
                    Bearer token Sanctum. Dokumentasi:
                    <a :href="docs_url" class="underline" target="_blank">{{ docs_url }}</a>
                </p>
            </div>

            <Card v-if="plain" class="border-primary">
                <CardContent class="p-4">
                    <p class="text-xs font-bold">Salin sekarang (hanya sekali):</p>
                    <code class="mt-2 block break-all rounded-lg bg-muted p-2 text-xs">{{ plain }}</code>
                </CardContent>
            </Card>

            <div class="flex gap-2">
                <Input v-model="form.name" class="max-w-xs" placeholder="Nama key" />
                <Button :disabled="form.processing" @click="createToken">Buat key</Button>
            </div>

            <Card v-for="t in props.tokens" :key="t.id">
                <CardContent class="flex items-center justify-between gap-3 p-4">
                    <div class="flex items-center gap-3">
                        <KeyRound class="h-4 w-4" />
                        <div>
                            <p class="text-sm font-bold">{{ t.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ t.created_at }} · last used {{ t.last_used_at || '—' }}</p>
                        </div>
                    </div>
                    <Button variant="ghost" size="sm" @click="router.delete(`/developer/tokens/${t.id}`)">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
