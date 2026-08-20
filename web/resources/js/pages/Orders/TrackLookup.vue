<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ClipboardList, Loader2, Mail, PackageSearch } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from '@/components/ui/sonner';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useStoreName } from '@/composables/useStoreName';

const page = usePage();
const storeSlug = computed(() => (page.props.store as { slug?: string } | null)?.slug ?? '');
const { storeName } = useStoreName();

const form = useForm({
    order_number: '',
    email: '',
});

function submit() {
    if (!storeSlug.value) {
        return;
    }

    form.post(`/${storeSlug.value}/cek-pesanan`, {
        onError: () => {
            const firstError = Object.values(form.errors)[0];
            if (firstError) {
                toast.error(String(firstError));
            }
        },
    });
}
</script>

<template>
    <StorefrontLayout>
        <Head :title="`Cek Pesanan — ${storeName}`" />

        <div class="mx-auto flex w-full max-w-lg flex-col gap-6 px-4 py-10 sm:py-14">
            <div class="text-center">
                <div
                    class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand/10 text-brand"
                >
                    <PackageSearch class="h-7 w-7" />
                </div>
                <h1 class="text-2xl font-black tracking-tight text-foreground">
                    Cek Pesanan
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Masukkan nomor pesanan dan email yang dipakai saat checkout.
                    Tidak perlu login.
                </p>
            </div>

            <Card class="border-border/80 shadow-sm">
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <ClipboardList class="h-4 w-4 text-brand" />
                        Lacak status pesanan
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="flex flex-col gap-4" @submit.prevent="submit">
                        <div class="space-y-2">
                            <Label for="order_number">Nomor pesanan</Label>
                            <Input
                                id="order_number"
                                v-model="form.order_number"
                                placeholder="ORD-20260820-ABC123"
                                autocomplete="off"
                                required
                            />
                            <p
                                v-if="form.errors.order_number"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.order_number }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <div class="relative">
                                <Mail
                                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="pl-9"
                                    placeholder="email@contoh.com"
                                    autocomplete="email"
                                    required
                                />
                            </div>
                            <p
                                v-if="form.errors.email"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <Button
                            type="submit"
                            class="mt-2 w-full bg-brand font-bold text-brand-foreground hover:opacity-90"
                            :disabled="form.processing"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            Lihat Status Pesanan
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </StorefrontLayout>
</template>
