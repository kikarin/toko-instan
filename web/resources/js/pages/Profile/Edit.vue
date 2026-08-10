<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, ChevronRight, Copy, Info, Check } from 'lucide-vue-next';
import { Avatar } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/components/ui/sonner';
import { Textarea } from '@/components/ui/textarea';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useCart } from '@/composables/useCart';
import { useProfileEdit } from '@/composables/useProfileEdit';
import type { UserProfile } from '@/types/user';

interface Props {
    user: UserProfile;
}

const props = defineProps<Props>();
const { totalCount: totalCartCount } = useCart();

const {
    nameValue,
    usernameValue,
    bioValue,
    phoneValue,
    genderValue,
    birthDateValue,
    avatarValue,
    activeEditField,
    tempEditValue,
    copiedUserId,
    userAvatar,
    userInitial,
    copyUserId,
    openEditModal,
    saveField,
} = useProfileEdit(props.user);
</script>

<template>

    <Head title="Ubah Profil — Toko Instan" />

    <StorefrontLayout :cartCount="totalCartCount" @open-cart="router.visit('/' + (usePage().props.store?.slug ?? ''))" @search="
        (q: string) => router.visit('/' + (usePage().props.store?.slug ?? ''), { data: { search: q } })
    ">
        <main class="mx-auto min-h-[calc(100vh-140px)] w-full max-w-[600px] bg-white shadow-xs">
            <!-- ── Top Header ── -->
            <div
                class="sticky top-14 z-30 flex items-center gap-3 border-b border-black/6 bg-white px-4 py-3.5 sm:px-6">
                <button @click="router.visit(`/${(usePage().props.store as any)?.slug ?? ''}/account`)"
                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-[#1c1c22] transition-colors hover:bg-black/5">
                    <ArrowLeft class="h-5 w-5" />
                </button>
                <h1 class="text-base font-extrabold text-[#1c1c22]">
                    Ubah Profil
                </h1>
            </div>

            <div class="flex flex-col py-6">
                <!-- ── Centered Avatar Section ── -->
                <div class="flex flex-col items-center justify-center gap-2 pb-6">
                    <Avatar :src="userAvatar" :fallback="userInitial" :hue="220"
                        class="h-20 w-20 text-2xl font-black shadow-sm ring-2 ring-black/10 sm:h-24 sm:w-24 sm:text-3xl" />
                    <button type="button" @click="openEditModal('avatar', avatarValue)"
                        class="mt-1 cursor-pointer text-sm font-extrabold text-[#e07c28] hover:underline">
                        Ubah Foto Profil
                    </button>
                </div>

                <!-- ── Section 1: Info profil ── -->
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5 bg-white px-4 py-2 sm:px-6">
                        <span class="text-sm font-extrabold text-[#1c1c22]">Info profil</span>
                        <Info class="h-3.5 w-3.5 text-[#9090a0]" />
                    </div>

                    <!-- Nama -->
                    <button type="button" @click="openEditModal('name', nameValue)"
                        class="flex cursor-pointer items-center justify-between border-b border-black/5 px-4 py-3.5 text-left transition-colors hover:bg-[#faf9f6] sm:px-6">
                        <span class="w-28 shrink-0 text-xs font-semibold text-[#9090a0]">Nama</span>
                        <span class="flex-1 truncate text-left text-xs font-semibold text-[#1c1c22]">{{ nameValue
                            }}</span>
                        <ChevronRight class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    </button>

                    <!-- Username -->
                    <button type="button" @click="openEditModal('username', usernameValue)"
                        class="flex cursor-pointer items-center justify-between border-b border-black/5 px-4 py-3.5 text-left transition-colors hover:bg-[#faf9f6] sm:px-6">
                        <span class="w-28 shrink-0 text-xs font-semibold text-[#9090a0]">Username</span>
                        <span class="flex-1 truncate text-left text-xs" :class="usernameValue
                                ? 'font-semibold text-[#1c1c22]'
                                : 'text-[#c0c0d0]'
                            ">
                            {{ usernameValue || 'Buat username yang unik' }}
                        </span>
                        <ChevronRight class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    </button>

                    <!-- Bio -->
                    <button type="button" @click="openEditModal('bio', bioValue)"
                        class="flex cursor-pointer items-center justify-between border-b border-black/5 px-4 py-3.5 text-left transition-colors hover:bg-[#faf9f6] sm:px-6">
                        <span class="w-28 shrink-0 text-xs font-semibold text-[#9090a0]">Bio</span>
                        <span class="flex-1 truncate text-left text-xs" :class="bioValue
                                ? 'font-semibold text-[#1c1c22]'
                                : 'text-[#c0c0d0]'
                            ">
                            {{ bioValue || 'Tulis bio tentangmu' }}
                        </span>
                        <ChevronRight class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    </button>
                </div>

                <!-- Grey Divider -->
                <div class="my-2 h-2 border-y border-black/5 bg-[#f5f4f0]" />

                <!-- ── Section 2: Info pribadi ── -->
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5 bg-white px-4 py-2 sm:px-6">
                        <span class="text-sm font-extrabold text-[#1c1c22]">Info pribadi</span>
                        <Info class="h-3.5 w-3.5 text-[#9090a0]" />
                    </div>

                    <!-- User ID -->
                    <div class="flex items-center justify-between border-b border-black/5 px-4 py-3.5 sm:px-6">
                        <span class="w-28 shrink-0 text-xs font-semibold text-[#9090a0]">User ID</span>
                        <span class="flex-1 text-left font-mono text-xs font-semibold text-[#1c1c22]">{{ user.userId
                            }}</span>
                        <button type="button" @click="copyUserId"
                            class="cursor-pointer p-1 text-[#4a4a57] transition-colors hover:text-[#e07c28]"
                            title="Salin User ID">
                            <Check v-if="copiedUserId" class="h-4 w-4 text-emerald-600" />
                            <Copy v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- E-mail -->
                    <button type="button" @click="
                        toast.info(
                            'Email tidak dapat diubah langsung demi keamanan akun.',
                        )
                        "
                        class="flex cursor-pointer items-center justify-between border-b border-black/5 px-4 py-3.5 text-left transition-colors hover:bg-[#faf9f6] sm:px-6">
                        <span class="w-28 shrink-0 text-xs font-semibold text-[#9090a0]">E-mail</span>
                        <span class="flex-1 truncate text-left text-xs font-semibold text-[#1c1c22]">{{ user.email
                            }}</span>
                        <ChevronRight class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    </button>

                    <!-- Nomor HP -->
                    <button type="button" @click="openEditModal('phone', phoneValue)"
                        class="flex cursor-pointer items-center justify-between border-b border-black/5 px-4 py-3.5 text-left transition-colors hover:bg-[#faf9f6] sm:px-6">
                        <span class="w-28 shrink-0 text-xs font-semibold text-[#9090a0]">Nomor HP</span>
                        <div class="flex flex-1 flex-col items-start gap-1">
                            <span class="text-xs font-semibold text-[#1c1c22]">{{ phoneValue }}</span>
                            <span class="rounded bg-[#f0f0f5] px-1.5 py-0.5 text-[9px] font-bold text-[#9090a0]">
                                Belum Diverifikasi
                            </span>
                        </div>
                        <ChevronRight class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    </button>

                    <!-- Jenis Kelamin -->
                    <button type="button" @click="openEditModal('gender', genderValue)"
                        class="flex cursor-pointer items-center justify-between border-b border-black/5 px-4 py-3.5 text-left transition-colors hover:bg-[#faf9f6] sm:px-6">
                        <span class="w-28 shrink-0 text-xs font-semibold text-[#9090a0]">Jenis Kelamin</span>
                        <span class="flex-1 text-left text-xs font-semibold text-[#1c1c22]">{{ genderValue }}</span>
                        <ChevronRight class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    </button>

                    <!-- Tanggal Lahir -->
                    <button type="button" @click="openEditModal('birthDate', birthDateValue)"
                        class="flex cursor-pointer items-center justify-between border-b border-black/5 px-4 py-3.5 text-left transition-colors hover:bg-[#faf9f6] sm:px-6">
                        <span class="w-28 shrink-0 text-xs font-semibold text-[#9090a0]">Tanggal Lahir</span>
                        <span class="flex-1 text-left text-xs font-semibold text-[#1c1c22]">{{ birthDateValue }}</span>
                        <ChevronRight class="h-4 w-4 shrink-0 text-[#9090a0]" />
                    </button>
                </div>

                <!-- ── Footer Link: Tutup Akun ── -->
                <div class="pt-10 pb-6 text-center">
                    <button type="button" @click="
                        toast.error(
                            'Layanan Penutupan Akun memerlukan verifikasi identitas.',
                        )
                        " class="cursor-pointer text-xs font-extrabold text-[#e07c28] hover:underline">
                        Tutup Akun
                    </button>
                </div>
            </div>
        </main>

        <!-- ── Field Edit Modal ── -->
        <Dialog :open="!!activeEditField" @update:open="
            (val) => {
                if (!val) activeEditField = null;
            }
        ">
            <DialogContent class="max-w-md rounded-2xl p-5">
                <DialogHeader>
                    <DialogTitle class="text-base font-bold text-[#1c1c22]">
                        Ubah
                        {{
                            activeEditField === 'name'
                                ? 'Nama'
                                : activeEditField === 'username'
                                    ? 'Username'
                                    : activeEditField === 'bio'
                                        ? 'Bio'
                                        : activeEditField === 'phone'
                                            ? 'Nomor HP'
                                            : activeEditField === 'gender'
                                                ? 'Jenis Kelamin'
                                                : activeEditField === 'birthDate'
                                                    ? 'Tanggal Lahir'
                                                    : 'Foto Profil'
                        }}
                    </DialogTitle>
                </DialogHeader>

                <div class="flex flex-col gap-3 py-2">
                    <!-- Photo edit -->
                    <template v-if="activeEditField === 'avatar'">
                        <Label class="text-xs font-bold text-[#1c1c22]">URL Foto Profil</Label>
                        <Input v-model="tempEditValue" type="url" placeholder="https://example.com/avatar.jpg"
                            class="h-10 rounded-xl border-black/10 text-xs focus-visible:ring-[#e07c28]" />
                    </template>

                    <!-- Text / Textarea Edit -->
                    <template v-else-if="activeEditField === 'bio'">
                        <Textarea v-model="tempEditValue" placeholder="Tulis bio tentangmu..." rows="3"
                            class="rounded-xl border-black/10 text-xs focus-visible:ring-[#e07c28]" />
                    </template>

                    <template v-else-if="activeEditField === 'gender'">
                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="tempEditValue = 'Pria'"
                                class="flex-1 rounded-xl border py-2.5 text-xs font-bold transition-all" :class="tempEditValue === 'Pria'
                                        ? 'border-[#e07c28] bg-[#fdf0e4] text-[#e07c28]'
                                        : 'border-black/10 text-[#4a4a57]'
                                    ">
                                Pria
                            </button>
                            <button type="button" @click="tempEditValue = 'Wanita'"
                                class="flex-1 rounded-xl border py-2.5 text-xs font-bold transition-all" :class="tempEditValue === 'Wanita'
                                        ? 'border-[#e07c28] bg-[#fdf0e4] text-[#e07c28]'
                                        : 'border-black/10 text-[#4a4a57]'
                                    ">
                                Wanita
                            </button>
                        </div>
                    </template>

                    <template v-else>
                        <Input v-model="tempEditValue" type="text" :placeholder="`Masukkan ${activeEditField}...`"
                            class="h-10 rounded-xl border-black/10 text-xs focus-visible:ring-[#e07c28]" />
                    </template>
                </div>

                <DialogFooter class="flex justify-end gap-2 pt-3">
                    <Button variant="outline" size="sm" class="rounded-xl text-xs" @click="activeEditField = null">
                        Batal
                    </Button>
                    <Button variant="amber" size="sm" class="rounded-xl text-xs font-bold" @click="saveField">
                        Simpan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </StorefrontLayout>
</template>
