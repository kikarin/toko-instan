<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Layers, Pencil, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardTitle } from '@/components/ui/card';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { useProductVariants } from '@/lib/useProductVariants';
import type { ProductVariant } from '@/types/product';

interface Props {
    product?: { id: number; name: string };
    variants?: ProductVariant[];
}

defineProps<Props>();

const {
    form,
    editingId,
    editingForm,
    deleteTarget,
    saveVariant,
    startEdit,
    commitEdit,
    confirmDelete,
} = useProductVariants();
</script>

<template>
    <Head :title="`Varian - ${product?.name ?? 'Produk'}`" />

    <AppLayout title="Varian Produk" activePage="Produk">
        <main class="mx-auto flex w-full max-w-3xl flex-col gap-5 p-4 sm:p-6">
            <div>
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                >
                    Product Variants
                </p>
                <h1
                    class="flex items-center gap-2 text-2xl font-extrabold text-[#1c1c22]"
                >
                    <Layers class="h-6 w-6" /> Varian — {{ product?.name }}
                </h1>
            </div>

            <Card class="p-5">
                <CardTitle class="mb-3 text-sm">Tambah Varian</CardTitle>
                <form
                    class="grid gap-3 sm:grid-cols-2"
                    @submit.prevent="saveVariant(product!.id)"
                >
                    <div class="flex flex-col gap-1.5">
                        <Label for="v-name">Nama varian</Label>
                        <Input
                            id="v-name"
                            v-model="form.name"
                            placeholder="cth: Hitam / 42"
                            required
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="v-sku">SKU</Label>
                        <Input
                            id="v-sku"
                            v-model="form.sku"
                            placeholder="NK-AF1-BLK42"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="v-price">Harga (opsional)</Label>
                        <Input
                            id="v-price"
                            v-model="form.price"
                            type="number"
                            min="0"
                            placeholder="Kosong = harga utama"
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="v-stock">Stok</Label>
                        <Input
                            id="v-stock"
                            v-model="form.stock"
                            type="number"
                            min="0"
                        />
                    </div>
                    <div>
                        <Button type="submit">Tambah Varian</Button>
                    </div>
                </form>
            </Card>

            <Card class="p-5">
                <CardTitle class="mb-3 text-sm">Daftar Varian</CardTitle>
                <div class="flex flex-col gap-2.5">
                    <div
                        v-for="v in variants ?? []"
                        :key="v.id"
                        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-black/5 bg-[#faf9f6] px-4 py-3"
                    >
                        <template v-if="editingId === v.id">
                            <div class="grid flex-1 gap-2 sm:grid-cols-3">
                                <Input
                                    v-model="editingForm.name"
                                    class="text-xs"
                                />
                                <Input
                                    v-model="editingForm.price"
                                    type="number"
                                    class="text-xs"
                                    placeholder="Harga"
                                />
                                <Input
                                    v-model="editingForm.stock"
                                    type="number"
                                    class="text-xs"
                                    placeholder="Stok"
                                />
                            </div>
                            <div class="flex gap-1">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="commitEdit"
                                    >Simpan</Button
                                >
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="editingId = null"
                                >
                                    Batal
                                </Button>
                            </div>
                        </template>
                        <template v-else>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-[#1c1c22]">
                                    {{ v.name }}
                                    <span class="font-normal text-[#9090a0]">
                                        {{ v.sku ? `• ${v.sku}` : '' }}
                                    </span>
                                </p>
                                <p class="text-[10px] text-[#9090a0]">
                                    {{ v.formatted_price ?? 'Harga utama' }} •
                                    Stok {{ v.stock }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge
                                    :variant="v.is_active ? 'teal' : 'rose'"
                                    class="px-2.5 py-0.5 text-[9px] uppercase"
                                >
                                    {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="startEdit(v, product!.id)"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                    @click="deleteTarget = v"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </Button>
                            </div>
                        </template>
                    </div>
                    <p
                        v-if="!(variants ?? []).length"
                        class="py-6 text-center text-xs text-[#9090a0]"
                    >
                        Belum ada varian.
                    </p>
                </div>
            </Card>
        </main>

        <ConfirmDialog
            :open="deleteTarget !== null"
            :title="deleteTarget ? 'Hapus ' + deleteTarget.name + '?' : ''"
            description="Varian yang dihapus tidak dapat dikembalikan."
            confirm-label="Hapus"
            tone="danger"
            @confirm="confirmDelete(product!.id)"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>
