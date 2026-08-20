<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Ticket, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

interface VoucherRow {
    id: number;
    code: string;
    name: string;
    type: 'percent' | 'nominal';
    value: number;
    min_spend: number;
    max_discount: number | null;
    usage_limit: number | null;
    used_count: number;
    starts_at: string | null;
    expires_at: string | null;
    is_active: boolean;
}

const props = defineProps<{ vouchers: VoucherRow[] }>();

const showForm = ref(false);
const form = useForm({
    code: '',
    name: '',
    type: 'nominal' as 'percent' | 'nominal',
    value: 10000,
    min_spend: 0,
    max_discount: null as number | null,
    usage_limit: null as number | null,
    starts_at: '',
    expires_at: '',
    is_active: true,
});

function submit() {
    form.post('/vouchers', {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

function toggleActive(v: VoucherRow) {
    router.put(`/vouchers/${v.id}`, { ...v, is_active: !v.is_active });
}

function remove(v: VoucherRow) {
    router.delete(`/vouchers/${v.id}`);
}
</script>

<template>
    <Head title="Voucher" />
    <AppLayout title="Voucher" activePage="Voucher">
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 sm:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold">Voucher & Diskon</h1>
                    <p class="text-sm text-muted-foreground">Kode % atau nominal, dipakai di checkout.</p>
                </div>
                <Button class="gap-1" @click="showForm = !showForm">
                    <Plus class="h-4 w-4" /> Voucher baru
                </Button>
            </div>

            <Card v-if="showForm">
                <CardContent class="grid gap-3 p-6 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <Label>Kode</Label>
                        <Input v-model="form.code" class="uppercase" placeholder="HEMAT10" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Nama</Label>
                        <Input v-model="form.name" placeholder="Diskon 10 ribu" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Tipe</Label>
                        <select v-model="form.type" class="h-10 rounded-md border bg-background px-3 text-sm">
                            <option value="nominal">Nominal (Rp)</option>
                            <option value="percent">Persen (%)</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Nilai</Label>
                        <Input v-model.number="form.value" type="number" min="1" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Min. belanja</Label>
                        <Input v-model.number="form.min_spend" type="number" min="0" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Maks. diskon (opsional)</Label>
                        <Input v-model.number="form.max_discount" type="number" min="0" />
                    </div>
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <Button :disabled="form.processing" @click="submit">Simpan</Button>
                    </div>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-3">
                <Card v-for="v in props.vouchers" :key="v.id">
                    <CardContent class="flex flex-wrap items-center justify-between gap-3 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                                <Ticket class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-mono text-sm font-bold">{{ v.code }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ v.name }} ·
                                    {{ v.type === 'percent' ? v.value + '%' : 'Rp ' + v.value.toLocaleString('id-ID') }}
                                    · terpakai {{ v.used_count }}{{ v.usage_limit ? '/' + v.usage_limit : '' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" @click="toggleActive(v)">
                                {{ v.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </Button>
                            <Button variant="ghost" size="sm" @click="remove(v)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
                <p v-if="!props.vouchers.length" class="text-sm text-muted-foreground">Belum ada voucher.</p>
            </div>
        </div>
    </AppLayout>
</template>
