<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    User,
    Home,
    Lock,
    Bell,
    Globe,
    ChevronDown,
    LogOut,
    Smartphone,
    Info,
    ChevronRight,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { toast } from '@/components/ui/sonner';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { logoutUser } from '@/lib/firebase';
import { useCart } from '@/lib/useCart';

interface UserInfo {
    id?: number;
    name: string;
    email?: string;
    role: string;
    avatar?: string | null;
}

interface Props {
    user: UserInfo;
}

const props = defineProps<Props>();

const appSettingsOpen = ref(false);
const aboutAppOpen = ref(false);

const { totalCount: totalCartCount } = useCart();

async function handleLogout() {
    await logoutUser();
    router.post('/logout');
}

function handleItemClick(title: string) {
    toast.info(`Fitur ${title} akan segera hadir!`);
}
</script>

<template>
    <Head title="Settings — Toko Instan" />

    <StorefrontLayout
        :cartCount="totalCartCount"
        @open-cart="router.visit('/marketplace')"
        @search="(q: string) => router.visit('/marketplace', { data: { search: q } })"
    >
        <main class="mx-auto w-full max-w-[800px] bg-white min-h-[calc(100vh-140px)] shadow-xs">
            <!-- ── Top Bar Header ── -->
            <div class="sticky top-14 z-30 flex items-center gap-3 border-b border-black/8 bg-white px-4 py-3.5 sm:px-6">
                <button
                    @click="router.visit('/account')"
                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full text-[#1c1c22] hover:bg-black/5 transition-colors"
                >
                    <ArrowLeft class="h-5 w-5" />
                </button>
                <h1 class="text-lg font-bold text-[#1c1c22]">Settings</h1>
            </div>

            <!-- ── Main Settings List ── -->
            <div class="flex flex-col">

                <!-- 1. Ubah Profil -->
                <button
                    @click="router.visit('/profile/edit')"
                    class="flex items-start gap-4 px-4 py-4 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left border-b border-black/5"
                >
                    <User class="h-5 w-5 shrink-0 text-[#1c1c22] mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-bold text-[#1c1c22]">Ubah Profil</p>
                        <p class="text-xs text-[#9090a0] mt-0.5">Atur identitas dan foto profil kamu</p>
                    </div>
                </button>

                <!-- 2. Daftar Alamat -->
                <button
                    @click="handleItemClick('Daftar Alamat')"
                    class="flex items-start gap-4 px-4 py-4 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left border-b border-black/5"
                >
                    <Home class="h-5 w-5 shrink-0 text-[#1c1c22] mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-bold text-[#1c1c22]">Daftar Alamat</p>
                        <p class="text-xs text-[#9090a0] mt-0.5">Atur alamat pengiriman belanjaan</p>
                    </div>
                </button>

                <!-- 3. Keamanan Akun -->
                <button
                    @click="handleItemClick('Keamanan Akun')"
                    class="flex items-start gap-4 px-4 py-4 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left border-b border-black/5"
                >
                    <Lock class="h-5 w-5 shrink-0 text-[#1c1c22] mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-bold text-[#1c1c22]">Keamanan Akun</p>
                        <p class="text-xs text-[#9090a0] mt-0.5">Kata sandi, PIN, & verifikasi data diri</p>
                    </div>
                </button>

                <!-- 4. Notifikasi -->
                <button
                    @click="handleItemClick('Notifikasi')"
                    class="flex items-start gap-4 px-4 py-4 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left border-b border-black/5"
                >
                    <Bell class="h-5 w-5 shrink-0 text-[#1c1c22] mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-bold text-[#1c1c22]">Notifikasi</p>
                        <p class="text-xs text-[#9090a0] mt-0.5">Atur segala jenis pesan notifikasi</p>
                    </div>
                </button>

                <!-- 5. Privasi Akun -->
                <button
                    @click="handleItemClick('Privasi Akun')"
                    class="flex items-start gap-4 px-4 py-4 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left border-b border-black/5"
                >
                    <Globe class="h-5 w-5 shrink-0 text-[#1c1c22] mt-0.5" />
                    <div class="flex-1">
                        <p class="text-sm font-bold text-[#1c1c22]">Privasi Akun</p>
                        <p class="text-xs text-[#9090a0] mt-0.5">Atur penggunaan data pribadimu di Toko Instan</p>
                    </div>
                </button>

                <!-- Grey Divider Bar -->
                <div class="h-2.5 bg-[#f5f4f0] border-y border-black/5" />

                <!-- ── Expandable Accordion: Pengaturan Aplikasi ── -->
                <div class="border-b border-black/5">
                    <button
                        @click="appSettingsOpen = !appSettingsOpen"
                        class="flex w-full items-center justify-between px-4 py-4 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left"
                    >
                        <span class="text-sm font-bold text-[#1c1c22]">Pengaturan Aplikasi</span>
                        <ChevronDown class="h-4 w-4 text-[#4a4a57] transition-transform duration-200" :class="appSettingsOpen ? 'rotate-180' : ''" />
                    </button>
                    <div v-if="appSettingsOpen" class="bg-[#faf9f6] px-6 py-3 flex flex-col gap-3 text-xs text-[#4a4a57] border-t border-black/5">
                        <div class="flex justify-between items-center py-1 cursor-pointer" @click="handleItemClick('Bahasa')">
                            <span>Bahasa Aplikasi</span>
                            <span class="font-semibold text-[#9090a0]">Bahasa Indonesia</span>
                        </div>
                        <div class="flex justify-between items-center py-1 cursor-pointer" @click="handleItemClick('Mode Gelap')">
                            <span>Tema Tampilan</span>
                            <span class="font-semibold text-[#9090a0]">Terang (Light)</span>
                        </div>
                        <div class="flex justify-between items-center py-1 cursor-pointer" @click="handleItemClick('Hapus Cache')">
                            <span>Bersihkan Cache</span>
                            <span class="font-semibold text-[#9090a0]">12.4 MB</span>
                        </div>
                    </div>
                </div>

                <!-- ── Expandable Accordion: Seputar Toko Instan ── -->
                <div class="border-b border-black/5">
                    <button
                        @click="aboutAppOpen = !aboutAppOpen"
                        class="flex w-full items-center justify-between px-4 py-4 sm:px-6 hover:bg-[#faf9f6] transition-colors text-left"
                    >
                        <span class="text-sm font-bold text-[#1c1c22]">Seputar Toko Instan</span>
                        <ChevronDown class="h-4 w-4 text-[#4a4a57] transition-transform duration-200" :class="aboutAppOpen ? 'rotate-180' : ''" />
                    </button>
                    <div v-if="aboutAppOpen" class="bg-[#faf9f6] px-6 py-3 flex flex-col gap-3 text-xs text-[#4a4a57] border-t border-black/5">
                        <div class="flex justify-between items-center py-1 cursor-pointer" @click="handleItemClick('Syarat Ketentuan')">
                            <span>Syarat & Ketentuan</span>
                            <ChevronRight class="h-3.5 w-3.5 text-[#9090a0]" />
                        </div>
                        <div class="flex justify-between items-center py-1 cursor-pointer" @click="handleItemClick('Kebijakan Privasi')">
                            <span>Kebijakan Privasi</span>
                            <ChevronRight class="h-3.5 w-3.5 text-[#9090a0]" />
                        </div>
                        <div class="flex justify-between items-center py-1 cursor-pointer" @click="handleItemClick('Lisensi')">
                            <span>Lisensi Perangkat Lunak</span>
                            <ChevronRight class="h-3.5 w-3.5 text-[#9090a0]" />
                        </div>
                    </div>
                </div>

                <!-- Grey Divider Bar -->
                <div class="h-2.5 bg-[#f5f4f0] border-y border-black/5" />

                <!-- ── Keluar Akun Button ── -->
                <button
                    @click="handleLogout"
                    class="flex items-center gap-4 px-4 py-4 sm:px-6 hover:bg-rose-50 transition-colors text-left text-rose-600 border-b border-black/5"
                >
                    <LogOut class="h-5 w-5 shrink-0" />
                    <span class="text-sm font-bold">Keluar Akun</span>
                </button>

                <!-- ── Version Footer ── -->
                <div class="py-10 text-center">
                    <p class="text-xs text-[#c0c0d0] font-mono">Versi 2.378.0</p>
                </div>

            </div>
        </main>
    </StorefrontLayout>
</template>
