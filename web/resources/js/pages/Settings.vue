<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, ChevronDown, LogOut, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';
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

defineProps<Props>();

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
        @search="
            (q: string) => router.visit('/marketplace', { data: { search: q } })
        "
    >
        <main class="mx-auto w-full max-w-[800px] p-3 sm:p-6">
            <div
                class="flex flex-col overflow-hidden rounded-2xl border border-black/6 bg-white shadow-xs"
            >
                <!-- ── Header Bar ── -->
                <div
                    class="flex items-center gap-3 border-b border-black/6 px-4 py-4 sm:px-6"
                >
                    <button
                        @click="router.visit('/account')"
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-black/6 bg-white text-[#1c1c22] transition-colors hover:bg-[#f5f4f0]"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </button>
                    <h1 class="text-lg font-extrabold text-[#1c1c22]">
                        Pengaturan Akun
                    </h1>
                </div>

                <!-- ── Menu Options Group 1 ── -->
                <div class="flex flex-col divide-y divide-black/5 text-sm">
                    <!-- Ubah Profil -->
                    <div
                        @click="router.visit('/profile/edit')"
                        class="flex cursor-pointer items-center justify-between px-4 py-4 transition-colors hover:bg-[#faf9f6] sm:px-6"
                    >
                        <div>
                            <p class="font-bold text-[#1c1c22]">Ubah Profil</p>
                            <p class="mt-0.5 text-xs text-[#9090a0]">
                                Ubah foto, nama, username, & data pribadi
                            </p>
                        </div>
                        <ChevronRight class="h-4 w-4 text-[#9090a0]" />
                    </div>

                    <!-- Daftar Alamat -->
                    <div
                        @click="handleItemClick('Daftar Alamat Pengiriman')"
                        class="flex cursor-pointer items-center justify-between px-4 py-4 transition-colors hover:bg-[#faf9f6] sm:px-6"
                    >
                        <div>
                            <p class="font-bold text-[#1c1c22]">
                                Daftar Alamat
                            </p>
                            <p class="mt-0.5 text-xs text-[#9090a0]">
                                Atur alamat pengiriman pesanan kamu
                            </p>
                        </div>
                        <ChevronRight class="h-4 w-4 text-[#9090a0]" />
                    </div>

                    <!-- Keamanan Akun -->
                    <div
                        @click="handleItemClick('Keamanan Akun & Kata Sandi')"
                        class="flex cursor-pointer items-center justify-between px-4 py-4 transition-colors hover:bg-[#faf9f6] sm:px-6"
                    >
                        <div>
                            <p class="font-bold text-[#1c1c22]">
                                Keamanan Akun
                            </p>
                            <p class="mt-0.5 text-xs text-[#9090a0]">
                                Kata sandi, PIN transaksi, & verifikasi 2
                                langkah
                            </p>
                        </div>
                        <ChevronRight class="h-4 w-4 text-[#9090a0]" />
                    </div>

                    <!-- Notifikasi -->
                    <div
                        @click="handleItemClick('Pengaturan Notifikasi')"
                        class="flex cursor-pointer items-center justify-between px-4 py-4 transition-colors hover:bg-[#faf9f6] sm:px-6"
                    >
                        <div>
                            <p class="font-bold text-[#1c1c22]">Notifikasi</p>
                            <p class="mt-0.5 text-xs text-[#9090a0]">
                                Atur notifikasi promo & status pesanan
                            </p>
                        </div>
                        <ChevronRight class="h-4 w-4 text-[#9090a0]" />
                    </div>

                    <!-- Privasi Akun -->
                    <div
                        @click="handleItemClick('Privasi & Keamanan Data')"
                        class="flex cursor-pointer items-center justify-between px-4 py-4 transition-colors hover:bg-[#faf9f6] sm:px-6"
                    >
                        <div>
                            <p class="font-bold text-[#1c1c22]">Privasi Akun</p>
                            <p class="mt-0.5 text-xs text-[#9090a0]">
                                Atur penggunaan data pribadimu di Nike Official
                                Store
                            </p>
                        </div>
                        <ChevronRight class="h-4 w-4 text-[#9090a0]" />
                    </div>
                </div>

                <!-- Grey Divider Bar -->
                <div class="h-2.5 border-y border-black/5 bg-[#f5f4f0]" />

                <!-- ── Expandable Accordion: Pengaturan Aplikasi ── -->
                <div class="border-b border-black/5">
                    <button
                        @click="appSettingsOpen = !appSettingsOpen"
                        class="flex w-full items-center justify-between px-4 py-4 text-left transition-colors hover:bg-[#faf9f6] sm:px-6"
                    >
                        <span class="text-sm font-bold text-[#1c1c22]"
                            >Pengaturan Aplikasi</span
                        >
                        <ChevronDown
                            class="h-4 w-4 text-[#4a4a57] transition-transform duration-200"
                            :class="appSettingsOpen ? 'rotate-180' : ''"
                        />
                    </button>
                    <div
                        v-if="appSettingsOpen"
                        class="flex flex-col gap-3 border-t border-black/5 bg-[#faf9f6] px-6 py-3 text-xs text-[#4a4a57]"
                    >
                        <div
                            class="flex cursor-pointer items-center justify-between py-1"
                            @click="handleItemClick('Bahasa / Language')"
                        >
                            <span>Bahasa / Language</span>
                            <span class="font-semibold text-[#9090a0]"
                                >Bahasa Indonesia</span
                            >
                        </div>
                        <div
                            class="flex cursor-pointer items-center justify-between py-1"
                            @click="handleItemClick('Mode Tampilan')"
                        >
                            <span>Mode Tampilan</span>
                            <span class="font-semibold text-[#9090a0]"
                                >Terang (Light)</span
                            >
                        </div>
                        <div
                            class="flex cursor-pointer items-center justify-between py-1"
                            @click="handleItemClick('Hapus Cache Aplikasi')"
                        >
                            <span>Hapus Cache Aplikasi</span>
                            <span class="font-semibold text-[#9090a0]"
                                >12.4 MB</span
                            >
                        </div>
                    </div>
                </div>

                <!-- ── Expandable Accordion: Seputar Nike Official Store ── -->
                <div class="border-b border-black/5">
                    <button
                        @click="aboutAppOpen = !aboutAppOpen"
                        class="flex w-full items-center justify-between px-4 py-4 text-left transition-colors hover:bg-[#faf9f6] sm:px-6"
                    >
                        <span class="text-sm font-bold text-[#1c1c22]"
                            >Seputar Nike Official Store</span
                        >
                        <ChevronDown
                            class="h-4 w-4 text-[#4a4a57] transition-transform duration-200"
                            :class="aboutAppOpen ? 'rotate-180' : ''"
                        />
                    </button>
                    <div
                        v-if="aboutAppOpen"
                        class="flex flex-col gap-3 border-t border-black/5 bg-[#faf9f6] px-6 py-3 text-xs text-[#4a4a57]"
                    >
                        <div
                            class="flex cursor-pointer items-center justify-between py-1"
                            @click="handleItemClick('Syarat Ketentuan')"
                        >
                            <span>Syarat & Ketentuan</span>
                            <ChevronRight class="h-3.5 w-3.5 text-[#9090a0]" />
                        </div>
                        <div
                            class="flex cursor-pointer items-center justify-between py-1"
                            @click="handleItemClick('Kebijakan Privasi')"
                        >
                            <span>Kebijakan Privasi</span>
                            <ChevronRight class="h-3.5 w-3.5 text-[#9090a0]" />
                        </div>
                        <div
                            class="flex cursor-pointer items-center justify-between py-1"
                            @click="handleItemClick('Lisensi')"
                        >
                            <span>Lisensi Perangkat Lunak</span>
                            <ChevronRight class="h-3.5 w-3.5 text-[#9090a0]" />
                        </div>
                    </div>
                </div>

                <!-- Grey Divider Bar -->
                <div class="h-2.5 border-y border-black/5 bg-[#f5f4f0]" />

                <!-- ── Keluar Akun Button ── -->
                <button
                    @click="handleLogout"
                    class="flex items-center gap-4 border-b border-black/5 px-4 py-4 text-left text-rose-600 transition-colors hover:bg-rose-50 sm:px-6"
                >
                    <LogOut class="h-5 w-5 shrink-0" />
                    <span class="text-sm font-bold">Keluar Akun</span>
                </button>

                <!-- ── Version Footer ── -->
                <div class="py-10 text-center">
                    <p class="font-mono text-xs text-[#c0c0d0]">
                        Versi 2.378.0
                    </p>
                </div>
            </div>
        </main>
    </StorefrontLayout>
</template>
