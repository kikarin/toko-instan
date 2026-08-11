<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { MapPin, Plus, Pencil, Trash2, Check } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardTitle } from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useAddresses } from '@/composables/useAddresses';
import { useIndoRegions } from '@/composables/useIndoRegions';
import { onMounted, watch } from 'vue';
import type { Address } from '@/types/address';

interface Props {
    addresses?: Address[];
}

defineProps<Props>();

const {
    showForm,
    editingId,
    deleteTarget,
    form,
    openNew,
    openEdit,
    save,
    makeDefault,
    confirmDelete,
} = useAddresses();

const { provinces, cities, districts, loadProvinces, loadCities, loadDistricts } = useIndoRegions();

onMounted(() => {
    loadProvinces();
});

watch([() => form.province, provinces], ([newProvName, provs], [oldProvName]) => {
    const prov = provs.find(p => p.name === newProvName);
    if (prov) {
        loadCities(prov.id);
    } else {
        cities.value = [];
    }
    
    if (oldProvName !== undefined && oldProvName !== newProvName) {
        form.city = '';
        form.district = '';
    }
});

watch([() => form.city, cities], ([newCityName, cits], [oldCityName]) => {
    const city = cits.find(c => c.name === newCityName);
    if (city) {
        loadDistricts(city.id);
    } else {
        districts.value = [];
    }
    
    if (oldCityName !== undefined && oldCityName !== newCityName) {
        form.district = '';
    }
});
</script>

<template>
    <Head title="Alamat Saya" />

    <StorefrontLayout
        @open-cart="router.visit('/' + ((usePage().props.store as any)?.slug ?? ''))"
    >
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
                    variant="default"
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
                        <Label for="addr-province">Provinsi</Label>
                        <Select v-model="form.province" required>
                            <SelectTrigger id="addr-province">
                                <SelectValue placeholder="Pilih Provinsi" />
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectGroup>
                                    <SelectItem v-for="prov in provinces" :key="prov.id" :value="prov.name">
                                        {{ prov.name }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="addr-city">Kota/Kabupaten</Label>
                        <Select v-model="form.city" required :disabled="!form.province">
                            <SelectTrigger id="addr-city">
                                <SelectValue placeholder="Pilih Kota/Kabupaten" />
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectGroup>
                                    <SelectItem v-for="city in cities" :key="city.id" :value="city.name">
                                        {{ city.name }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="addr-district">Kecamatan</Label>
                        <Select v-model="form.district" required :disabled="!form.city">
                            <SelectTrigger id="addr-district">
                                <SelectValue placeholder="Pilih Kecamatan" />
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectGroup>
                                    <SelectItem v-for="district in districts" :key="district.id" :value="district.name">
                                        {{ district.name }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="addr-postal">Kode Pos</Label>
                        <Input
                            id="addr-postal"
                            v-model="form.postal_code"
                            required
                            type="number"
                            maxlength="5"
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
                        <Button type="submit" variant="default">Simpan</Button>
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
                    class="rounded-2xl border border-border bg-white p-4 shadow-sm"
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
                                        variant="default"
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
                                    {{ a.address }}, {{ a.district ? a.district + ', ' : '' }}{{ a.city }},
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
                                class="text-destructive hover:bg-destructive/10"
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
                        variant="default"
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
