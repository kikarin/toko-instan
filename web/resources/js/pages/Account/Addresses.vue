<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { MapPin, Plus, Pencil, Trash2, Check } from 'lucide-vue-next';
import { reactive, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardTitle } from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface Address {
    id: number;
    label: string | null;
    recipient_name: string;
    phone: string;
    address: string;
    city: string;
    province: string;
    postal_code: string;
    is_default: boolean;
}

interface Props {
    addresses?: Address[];
}

defineProps<Props>();

const showForm = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<Address | null>(null);

const blankForm = {
    label: '',
    recipient_name: '',
    phone: '',
    address: '',
    city: '',
    province: '',
    postal_code: '',
    is_default: false,
};

const form = reactive({ ...blankForm });

function openNew() {
    editingId.value = null;
    Object.assign(form, blankForm);
    showForm.value = true;
}

function openEdit(a: Address) {
    editingId.value = a.id;
    Object.assign(form, {
        label: a.label ?? '',
        recipient_name: a.recipient_name,
        phone: a.phone,
        address: a.address,
        city: a.city,
        province: a.province,
        postal_code: a.postal_code,
        is_default: a.is_default,
    });
    showForm.value = true;
}

function payload() {
    return {
        label: form.label || undefined,
        recipient_name: form.recipient_name,
        phone: form.phone,
        address: form.address,
        city: form.city,
        province: form.province,
        postal_code: form.postal_code,
        is_default: form.is_default,
    };
}

function save() {
    const options = {
        onSuccess: () => {
            showForm.value = false;
            editingId.value = null;
            toast.success('Alamat tersimpan.');
        },
        onError: () =>
            toast.error('Gagal menyimpan alamat. Periksa kembali formulir.'),
    };

    if (editingId.value === null) {
        router.post('/addresses', payload(), options);
    } else {
        router.put(`/addresses/${editingId.value}`, payload(), options);
    }
}

function makeDefault(a: Address) {
    router.patch(
        `/addresses/${a.id}/default`,
        {},
        { onError: () => toast.error('Gagal mengubah alamat utama.') },
    );
}

function confirmDelete() {
    if (!deleteTarget.value) {
        return;
    }

    router.delete(`/addresses/${deleteTarget.value.id}`, {
        onSuccess: () => toast.success('Alamat dihapus.'),
        onError: () => toast.error('Gagal menghapus alamat.'),
    });
}
</script>

<template>
    <Head title="Alamat Saya" />

    <StorefrontLayout>
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-5 p-4 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p
                        class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                    >
                        Pengiriman
                    </p>
                    <h1
                        class="flex items-center gap-2 text-2xl font-extrabold text-[#1c1c22]"
                    >
                        <MapPin class="h-6 w-6" /> Alamat Saya
                    </h1>
                </div>
                <Button
                    v-if="!showForm"
                    variant="amber"
                    size="sm"
                    @click="openNew"
                >
                    <Plus class="mr-1.5 h-3.5 w-3.5" /> Tambah Alamat
                </Button>
            </div>

            <Card v-if="showForm" class="p-5">
                <CardTitle class="mb-4 text-sm">
                    {{
                        editingId === null
                            ? 'Tambah Alamat Baru'
                            : 'Ubah Alamat'
                    }}
                </CardTitle>
                <form class="grid gap-3 sm:grid-cols-2" @submit.prevent="save">
                    <div class="flex flex-col gap-1.5">
                        <Label for="addr-label">Label (opsional)</Label>
                        <Input
                            id="addr-label"
                            v-model="form.label"
                            placeholder="cth: Rumah / Kantor"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5 sm:col-span-1">
                        <Label for="addr-name" class="required"
                            >Nama Penerima</Label
                        >
                        <Input
                            id="addr-name"
                            v-model="form.recipient_name"
                            required
                            placeholder="Nama penerima"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="addr-phone">No. HP</Label>
                        <Input
                            id="addr-phone"
                            v-model="form.phone"
                            required
                            placeholder="08xxxx"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <Label for="addr-address">Alamat Lengkap</Label>
                        <Input
                            id="addr-address"
                            v-model="form.address"
                            required
                            placeholder="Jalan, nomor, RT/RW, kelurahan..."
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="addr-city">Kota/Kabupaten</Label>
                        <Input
                            id="addr-city"
                            v-model="form.city"
                            required
                            placeholder="Jakarta Selatan"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="addr-province">Provinsi</Label>
                        <Input
                            id="addr-province"
                            v-model="form.province"
                            required
                            placeholder="DKI Jakarta"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="addr-postal">Kode Pos</Label>
                        <Input
                            id="addr-postal"
                            v-model="form.postal_code"
                            required
                            placeholder="12190"
                        />
                    </div>
                    <div class="flex items-end gap-3 pb-1">
                        <Label class="text-xs">Jadikan alamat utama</Label>
                        <Switch
                            :checked="form.is_default"
                            @update:checked="form.is_default = $event"
                        />
                    </div>
                    <div class="flex gap-2 sm:col-span-2">
                        <Button type="submit" variant="amber">Simpan</Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showForm = false"
                        >
                            Batal
                        </Button>
                    </div>
                </form>
            </Card>

            <div class="flex flex-col gap-3">
                <div
                    v-for="a in addresses ?? []"
                    :key="a.id"
                    class="rounded-2xl border border-black/5 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div
                                class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                                :class="
                                    a.is_default
                                        ? 'bg-[#e07c281a] text-[#e07c28]'
                                        : 'bg-[#f5f4f0] text-[#9090a0]'
                                "
                            >
                                <MapPin class="h-4 w-4" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-[#1c1c22]">
                                        {{ a.recipient_name }}
                                    </p>
                                    <Badge
                                        v-if="a.is_default"
                                        variant="amber"
                                        class="px-2 py-0 text-[9px] uppercase"
                                    >
                                        Utama
                                    </Badge>
                                </div>
                                <p class="mt-0.5 text-[11px] text-[#9090a0]">
                                    {{ a.phone }}
                                    <template v-if="a.label">
                                        • {{ a.label }}</template
                                    >
                                </p>
                                <p
                                    class="mt-1 text-xs leading-relaxed text-[#4a4a57]"
                                >
                                    {{ a.address }}, {{ a.city }},
                                    {{ a.province }}
                                    {{ a.postal_code }}
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="openEdit(a)"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="text-red-500 hover:bg-red-50"
                                @click="deleteTarget = a"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>
                    <Button
                        v-if="!a.is_default"
                        variant="ghost"
                        size="sm"
                        class="mt-2 px-2 text-[11px] font-bold text-[#6d4fc2]"
                        @click="makeDefault(a)"
                    >
                        <Check class="mr-1 h-3 w-3" /> Jadikan alamat utama
                    </Button>
                </div>

                <Card
                    v-if="!(addresses ?? []).length && !showForm"
                    class="py-14 text-center"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f5f4f0]"
                    >
                        <MapPin class="h-7 w-7 text-[#c8c8d5]" />
                    </div>
                    <p class="mt-3 text-sm font-semibold text-[#4a4a57]">
                        Belum ada alamat
                    </p>
                    <p class="mt-1 text-xs text-[#9090a0]">
                        Tambahkan alamat pengiriman pertamamu.
                    </p>
                    <Button
                        variant="amber"
                        size="sm"
                        class="mt-3 text-xs font-bold"
                        @click="openNew"
                    >
                        <Plus class="mr-1.5 h-3.5 w-3.5" /> Tambah Alamat
                    </Button>
                </Card>
            </div>
        </main>

        <ConfirmDialog
            :open="deleteTarget !== null"
            :title="deleteTarget ? 'Hapus alamat ini?' : ''"
            description="Alamat yang dihapus tidak dapat dikembalikan."
            confirm-label="Hapus"
            tone="danger"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />
    </StorefrontLayout>
</template>
