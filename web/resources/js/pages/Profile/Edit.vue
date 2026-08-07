<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronRight,
    Copy,
    Info,
    Check,
    Camera,
    Loader2,
    X,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
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
import { Textarea } from '@/components/ui/textarea';
import { toast } from '@/components/ui/sonner';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { useActiveUser } from '@/lib/useActiveUser';
import { useCart } from '@/lib/useCart';

interface UserInfo {
    id?: number;
    userId: string;
    name: string;
    username?: string;
    bio?: string;
    email?: string;
    phone?: string;
    phoneVerified?: boolean;
    gender?: string;
    birthDate?: string;
    role: string;
    avatar?: string | null;
}

interface Props {
    user: UserInfo;
}

const props = defineProps<Props>();
const activeUser = useActiveUser();
const { totalCount: totalCartCount } = useCart();

// Form state
const nameValue = ref(props.user.name || activeUser.value?.name || 'Niko Agustio');
const usernameValue = ref(props.user.username || '');
const bioValue = ref(props.user.bio || '');
const phoneValue = ref(props.user.phone || '+6285264415051');
const genderValue = ref(props.user.gender || 'Pria');
const birthDateValue = ref(props.user.birthDate || '01 January 1991');
const avatarValue = ref(props.user.avatar || activeUser.value?.photoURL || '');

const form = useForm({
    name: nameValue.value,
    username: usernameValue.value,
    bio: bioValue.value,
    phone: phoneValue.value,
    gender: genderValue.value,
    birth_date: birthDateValue.value,
    avatar: avatarValue.value,
});

// Modal state
const activeEditField = ref<string | null>(null);
const tempEditValue = ref('');
const copiedUserId = ref(false);

const userAvatar = computed(() => {
    return avatarValue.value || activeUser.value?.photoURL || undefined;
});

const userInitial = computed(() => {
    return (nameValue.value || 'U').substring(0, 2).toUpperCase();
});

const presetAvatars = [
    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&h=200&fit=crop&auto=format',
    'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=200&fit=crop&auto=format',
];

function copyUserId() {
    navigator.clipboard.writeText(props.user.userId);
    copiedUserId.value = true;
    toast.success(`User ID ${props.user.userId} berhasil disalin!`);
    setTimeout(() => {
        copiedUserId.value = false;
    }, 2000);
}

function openEditModal(field: string, currentValue: string) {
    activeEditField.value = field;
    tempEditValue.value = currentValue;
}

function saveField() {
    if (!activeEditField.value) return;

    if (activeEditField.value === 'name') {
        nameValue.value = tempEditValue.value || nameValue.value;
        form.name = nameValue.value;
    } else if (activeEditField.value === 'username') {
        usernameValue.value = tempEditValue.value;
        form.username = tempEditValue.value;
    } else if (activeEditField.value === 'bio') {
        bioValue.value = tempEditValue.value;
        form.bio = tempEditValue.value;
    } else if (activeEditField.value === 'phone') {
        phoneValue.value = tempEditValue.value;
        form.phone = tempEditValue.value;
    } else if (activeEditField.value === 'gender') {
        genderValue.value = tempEditValue.value;
        form.gender = tempEditValue.value;
    } else if (activeEditField.value === 'birthDate') {
        birthDateValue.value = tempEditValue.value;
        form.birth_date = tempEditValue.value;
    } else if (activeEditField.value === 'avatar') {
        avatarValue.value = tempEditValue.value;
        form.avatar = tempEditValue.value;
    }

    activeEditField.value = null;

    form.put('/profile/edit', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Profil berhasil diperbarui!');
        },
    });
}
</script>

<template>
    <Head title="Ubah Profil — Toko Instan" />

    <StorefrontLayout
        :cartCount="totalCartCount"
        @open-cart="router.visit('/marketplace')"
        @search="(q: string) => router.visit('/marketplace', { data: { search: q } })"
    >
        <main class="mx-auto w-full max-w-[600px] min-h-[calc(100vh-140px)] bg-white shadow-xs">
            <!-- ── Top Header ── -->
            <div class="sticky top-14 z-30 flex items-center gap-3 border-b border-black/6 bg-white px-4 py-3.5 sm:px-6">
                <button
                    @click="router.visit('/account')"
                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-[#1c1c22] hover:bg-black/5 transition-colors"
                >
                    <ArrowLeft class="h-5 w-5" />
                </button>
                <h1 class="text-base font-extrabold text-[#1c1c22]">Ubah Profil</h1>
            </div>

            <div class="flex flex-col py-6">

                <!-- ── Centered Avatar Section ── -->
                <div class="flex flex-col items-center justify-center gap-2 pb-6">
                    <Avatar
                        :src="userAvatar"
                        :fallback="userInitial"
                        :hue="220"
                        class="h-20 w-20 text-2xl font-black ring-2 ring-black/10 shadow-sm sm:h-24 sm:w-24 sm:text-3xl"
                    />
                    <button
                        type="button"
                        @click="openEditModal('avatar', avatarValue)"
                        class="mt-1 text-sm font-extrabold text-[#e07c28] hover:underline cursor-pointer"
                    >
                        Ubah Foto Profil
                    </button>
                </div>

                <!-- ── Section 1: Info profil ── -->
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5 px-4 py-2 sm:px-6 bg-white">
                        <span class="text-sm font-extrabold text-[#1c1c22]">Info profil</span>
                        <Info class="h-3.5 w-3.5 text-[#9090a0]" />
                    </div>

                    <!-- Nama -->
                    <button
                        type="button"
                        @click="openEditModal('name', nameValue)"
                        class="flex items-center justify-between px-4 py-3.5 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left cursor-pointer border-b border-black/5"
                    >
                        <span class="text-xs font-semibold text-[#9090a0] w-28 shrink-0">Nama</span>
                        <span class="text-xs font-semibold text-[#1c1c22] flex-1 text-left truncate">{{ nameValue }}</span>
                        <ChevronRight class="h-4 w-4 text-[#9090a0] shrink-0" />
                    </button>

                    <!-- Username -->
                    <button
                        type="button"
                        @click="openEditModal('username', usernameValue)"
                        class="flex items-center justify-between px-4 py-3.5 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left cursor-pointer border-b border-black/5"
                    >
                        <span class="text-xs font-semibold text-[#9090a0] w-28 shrink-0">Username</span>
                        <span
                            class="text-xs flex-1 text-left truncate"
                            :class="usernameValue ? 'font-semibold text-[#1c1c22]' : 'text-[#c0c0d0]'"
                        >
                            {{ usernameValue || 'Buat username yang unik' }}
                        </span>
                        <ChevronRight class="h-4 w-4 text-[#9090a0] shrink-0" />
                    </button>

                    <!-- Bio -->
                    <button
                        type="button"
                        @click="openEditModal('bio', bioValue)"
                        class="flex items-center justify-between px-4 py-3.5 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left cursor-pointer border-b border-black/5"
                    >
                        <span class="text-xs font-semibold text-[#9090a0] w-28 shrink-0">Bio</span>
                        <span
                            class="text-xs flex-1 text-left truncate"
                            :class="bioValue ? 'font-semibold text-[#1c1c22]' : 'text-[#c0c0d0]'"
                        >
                            {{ bioValue || 'Tulis bio tentangmu' }}
                        </span>
                        <ChevronRight class="h-4 w-4 text-[#9090a0] shrink-0" />
                    </button>
                </div>

                <!-- Grey Divider -->
                <div class="h-2 bg-[#f5f4f0] my-2 border-y border-black/5" />

                <!-- ── Section 2: Info pribadi ── -->
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5 px-4 py-2 sm:px-6 bg-white">
                        <span class="text-sm font-extrabold text-[#1c1c22]">Info pribadi</span>
                        <Info class="h-3.5 w-3.5 text-[#9090a0]" />
                    </div>

                    <!-- User ID -->
                    <div class="flex items-center justify-between px-4 py-3.5 sm:px-6 border-b border-black/5">
                        <span class="text-xs font-semibold text-[#9090a0] w-28 shrink-0">User ID</span>
                        <span class="text-xs font-semibold text-[#1c1c22] flex-1 text-left font-mono">{{ user.userId }}</span>
                        <button
                            type="button"
                            @click="copyUserId"
                            class="p-1 text-[#4a4a57] hover:text-[#e07c28] cursor-pointer transition-colors"
                            title="Salin User ID"
                        >
                            <Check v-if="copiedUserId" class="h-4 w-4 text-emerald-600" />
                            <Copy v-else class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- E-mail -->
                    <button
                        type="button"
                        @click="toast.info('Email tidak dapat diubah langsung demi keamanan akun.')"
                        class="flex items-center justify-between px-4 py-3.5 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left cursor-pointer border-b border-black/5"
                    >
                        <span class="text-xs font-semibold text-[#9090a0] w-28 shrink-0">E-mail</span>
                        <span class="text-xs font-semibold text-[#1c1c22] flex-1 text-left truncate">{{ user.email }}</span>
                        <ChevronRight class="h-4 w-4 text-[#9090a0] shrink-0" />
                    </button>

                    <!-- Nomor HP -->
                    <button
                        type="button"
                        @click="openEditModal('phone', phoneValue)"
                        class="flex items-center justify-between px-4 py-3.5 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left cursor-pointer border-b border-black/5"
                    >
                        <span class="text-xs font-semibold text-[#9090a0] w-28 shrink-0">Nomor HP</span>
                        <div class="flex flex-1 flex-col items-start gap-1">
                            <span class="text-xs font-semibold text-[#1c1c22]">{{ phoneValue }}</span>
                            <span class="rounded bg-[#f0f0f5] px-1.5 py-0.5 text-[9px] font-bold text-[#9090a0]">
                                Belum Diverifikasi
                            </span>
                        </div>
                        <ChevronRight class="h-4 w-4 text-[#9090a0] shrink-0" />
                    </button>

                    <!-- Jenis Kelamin -->
                    <button
                        type="button"
                        @click="openEditModal('gender', genderValue)"
                        class="flex items-center justify-between px-4 py-3.5 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left cursor-pointer border-b border-black/5"
                    >
                        <span class="text-xs font-semibold text-[#9090a0] w-28 shrink-0">Jenis Kelamin</span>
                        <span class="text-xs font-semibold text-[#1c1c22] flex-1 text-left">{{ genderValue }}</span>
                        <ChevronRight class="h-4 w-4 text-[#9090a0] shrink-0" />
                    </button>

                    <!-- Tanggal Lahir -->
                    <button
                        type="button"
                        @click="openEditModal('birthDate', birthDateValue)"
                        class="flex items-center justify-between px-4 py-3.5 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left cursor-pointer border-b border-black/5"
                    >
                        <span class="text-xs font-semibold text-[#9090a0] w-28 shrink-0">Tanggal Lahir</span>
                        <span class="text-xs font-semibold text-[#1c1c22] flex-1 text-left">{{ birthDateValue }}</span>
                        <ChevronRight class="h-4 w-4 text-[#9090a0] shrink-0" />
                    </button>
                </div>

                <!-- ── Footer Link: Tutup Akun ── -->
                <div class="pt-10 pb-6 text-center">
                    <button
                        type="button"
                        @click="toast.error('Layanan Penutupan Akun memerlukan verifikasi identitas.')"
                        class="text-xs font-extrabold text-[#e07c28] hover:underline cursor-pointer"
                    >
                        Tutup Akun
                    </button>
                </div>

            </div>
        </main>

        <!-- ── Field Edit Modal ── -->
        <Dialog :open="!!activeEditField" @update:open="(val) => { if (!val) activeEditField = null; }">
            <DialogContent class="max-w-md rounded-2xl p-5">
                <DialogHeader>
                    <DialogTitle class="text-base font-bold text-[#1c1c22]">
                        Ubah {{ activeEditField === 'name' ? 'Nama' : activeEditField === 'username' ? 'Username' : activeEditField === 'bio' ? 'Bio' : activeEditField === 'phone' ? 'Nomor HP' : activeEditField === 'gender' ? 'Jenis Kelamin' : activeEditField === 'birthDate' ? 'Tanggal Lahir' : 'Foto Profil' }}
                    </DialogTitle>
                </DialogHeader>

                <div class="py-2 flex flex-col gap-3">
                    <!-- Photo edit -->
                    <template v-if="activeEditField === 'avatar'">
                        <Label class="text-xs font-bold text-[#1c1c22]">URL Foto Profil</Label>
                        <Input
                            v-model="tempEditValue"
                            type="url"
                            placeholder="https://example.com/avatar.jpg"
                            class="h-10 text-xs rounded-xl border-black/10 focus-visible:ring-[#e07c28]"
                        />
                        <div class="mt-2">
                            <p class="text-xs font-semibold text-[#4a4a57] mb-2">Pilih Preset Foto:</p>
                            <div class="flex items-center gap-2">
                                <button
                                    v-for="(preset, i) in presetAvatars"
                                    :key="i"
                                    type="button"
                                    @click="tempEditValue = preset"
                                    class="h-10 w-10 overflow-hidden rounded-full ring-2 transition-all cursor-pointer"
                                    :class="tempEditValue === preset ? 'ring-[#e07c28] ring-offset-2' : 'ring-transparent opacity-70 hover:opacity-100'"
                                >
                                    <img :src="preset" alt="Preset" class="h-full w-full object-cover" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Text / Textarea Edit -->
                    <template v-else-if="activeEditField === 'bio'">
                        <Textarea
                            v-model="tempEditValue"
                            placeholder="Tulis bio tentangmu..."
                            rows="3"
                            class="text-xs rounded-xl border-black/10 focus-visible:ring-[#e07c28]"
                        />
                    </template>

                    <template v-else-if="activeEditField === 'gender'">
                        <div class="flex gap-3 pt-1">
                            <button
                                type="button"
                                @click="tempEditValue = 'Pria'"
                                class="flex-1 py-2.5 rounded-xl border text-xs font-bold transition-all"
                                :class="tempEditValue === 'Pria' ? 'border-[#e07c28] bg-[#fdf0e4] text-[#e07c28]' : 'border-black/10 text-[#4a4a57]'"
                            >
                                Pria
                            </button>
                            <button
                                type="button"
                                @click="tempEditValue = 'Wanita'"
                                class="flex-1 py-2.5 rounded-xl border text-xs font-bold transition-all"
                                :class="tempEditValue === 'Wanita' ? 'border-[#e07c28] bg-[#fdf0e4] text-[#e07c28]' : 'border-black/10 text-[#4a4a57]'"
                            >
                                Wanita
                            </button>
                        </div>
                    </template>

                    <template v-else>
                        <Input
                            v-model="tempEditValue"
                            type="text"
                            :placeholder="`Masukkan ${activeEditField}...`"
                            class="h-10 text-xs rounded-xl border-black/10 focus-visible:ring-[#e07c28]"
                        />
                    </template>
                </div>

                <DialogFooter class="flex gap-2 justify-end pt-3">
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
