<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    Store,
    Image as ImageIcon,
    Phone,
    ExternalLink,
    Save,
    Sparkles,
    ShieldCheck,
    Globe,
    FileText,
    Building2,
    Power,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/components/ui/sonner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

interface StoreData {
    id: number;
    name: string;
    slug: string;
    category?: string;
    description?: string;
    logo?: string;
    avatar_hue?: number;
    banner_url?: string;
    phone?: string;
    email?: string;
    address?: string;
    instagram?: string;
    tiktok?: string;
    headline?: string;
    badge?: string;
    rating?: number;
    is_active?: boolean;
    npwp?: string;
    nik?: string;
    is_pkp?: boolean;
    tax_name?: string;
    tax_address?: string;
}

interface Props {
    store: StoreData;
}

const props = defineProps<Props>();

const activeTab = ref<'profil' | 'banner' | 'kontak' | 'pajak'>('profil');

const form = useForm({
    name: props.store?.name || 'Nike Official Store',
    slug: props.store?.slug || 'nike-official',
    category: props.store?.category || 'Sportswear & Sneakers',
    description:
        props.store?.description ||
        'Toko Resmi Nike Indonesia. Garansi 100% Produk Asli & Original.',
    logo: props.store?.logo || '',
    avatar_hue: props.store?.avatar_hue || 220,
    banner_url:
        props.store?.banner_url ||
        'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1200&h=400&fit=crop&auto=format',
    phone: props.store?.phone || '+6285264415051',
    email: props.store?.email || 'support@nike-official.id',
    address:
        props.store?.address ||
        'Jl. Jend. Sudirman Kav. 54-55, Jakarta Selatan 12190',
    instagram: props.store?.instagram || '@nike_indonesia',
    tiktok: props.store?.tiktok || '@nike_official',
    headline:
        props.store?.headline || 'JUST DO IT. — Koleksi Resmi Nike Indonesia',
    is_active: props.store?.is_active ?? true,
    npwp: props.store?.npwp || '01.234.567.8-901.000',
    nik: props.store?.nik || '3171012345670001',
    is_pkp: props.store?.is_pkp ?? true,
    tax_name: props.store?.tax_name || 'PT Nike Indonesia Resmi',
    tax_address:
        props.store?.tax_address ||
        'Gedung Menara Mandiri Lt. 18, Jl. Jend. Sudirman Kav. 54-55, Jakarta Selatan 12190',
});

const bannerPreviewUrl = ref<string | null>(null);
const logoPreviewUrl = ref<string | null>(null);

function handleBannerFileChange(e: Event) {
    const target = e.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        const file = target.files[0];
        (form as any).banner_file = file;
        bannerPreviewUrl.value = URL.createObjectURL(file);
        form.banner_url = bannerPreviewUrl.value;
    }
}

function handleLogoFileChange(e: Event) {
    const target = e.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        const file = target.files[0];
        (form as any).logo_file = file;
        logoPreviewUrl.value = URL.createObjectURL(file);
        form.logo = logoPreviewUrl.value;
    }
}

function submitForm() {
    router.post(
        '/store-settings',
        {
            _method: 'put',
            ...form.data(),
            banner_file: (form as any).banner_file,
            logo_file: (form as any).logo_file,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Pengaturan & Branding Toko berhasil disimpan!');
            },
            onError: () => {
                toast.error(
                    'Gagal menyimpan pengaturan toko. Periksa kembali form.',
                );
            },
        },
    );
}
</script>

<template>
    <Head title="Pengaturan & Branding Toko — Dashboard Merchant" />

    <AppLayout title="Pengaturan Toko" activePage="Dashboard">
        <div class="flex flex-col gap-6 p-4 sm:p-6 lg:p-8">
            <!-- ── Top Title Bar ── -->
            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-[#1c1c22]">
                            Pengaturan & Branding Toko
                        </h1>
                        <Badge
                            variant="amber"
                            class="bg-black text-[10px] font-extrabold text-amber-400 uppercase"
                            >Shopify Webstore</Badge
                        >
                    </div>
                    <p class="text-xs text-[#9090a0]">
                        Kelola nama toko, subdomain, logo, hero banner, dan
                        kontak pelayanan webstore kamu.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 gap-1.5 border-black/10 text-xs font-bold"
                        @click="router.visit('/')"
                    >
                        <ExternalLink class="h-4 w-4" />
                        Lihat Storefront
                    </Button>
                    <Button
                        variant="amber"
                        size="sm"
                        class="h-9 gap-1.5 bg-black text-xs font-bold text-amber-400 shadow-md hover:bg-zinc-800"
                        :disabled="form.processing"
                        @click="submitForm"
                    >
                        <Save class="h-4 w-4 text-amber-400" />
                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan Perubahan'
                        }}
                    </Button>
                </div>
            </div>

            <!-- ── Live Preview Box ── -->
            <Card
                class="overflow-hidden rounded-2xl border-black/10 bg-white shadow-md"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between bg-zinc-900 p-4 text-white"
                >
                    <div class="flex items-center gap-2">
                        <Sparkles class="h-4 w-4 text-amber-400" />
                        <span
                            class="text-xs font-black tracking-wider text-amber-400 uppercase"
                            >Live Preview Header Webstore</span
                        >
                    </div>
                    <Badge
                        class="bg-emerald-600 text-[9px] font-black text-white uppercase"
                        >OFFICIAL STORE</Badge
                    >
                </CardHeader>
                <CardContent
                    class="flex flex-col items-center gap-4 bg-gradient-to-r from-zinc-100 to-zinc-50 p-4 sm:flex-row sm:p-6"
                >
                    <Avatar
                        :src="form.logo || undefined"
                        :fallback="form.name.substring(0, 2).toUpperCase()"
                        :hue="form.avatar_hue"
                        class="h-16 w-16 shrink-0 rounded-2xl text-xl font-black shadow-md ring-4 ring-white"
                    />
                    <div class="flex-1 text-center sm:text-left">
                        <div
                            class="flex items-center justify-center gap-2 sm:justify-start"
                        >
                            <h3
                                class="text-lg font-black tracking-wide text-[#1c1c22] uppercase"
                            >
                                {{ form.name }}
                            </h3>
                            <ShieldCheck class="h-5 w-5 text-emerald-600" />
                        </div>
                        <p class="mt-0.5 text-xs font-semibold text-zinc-500">
                            {{ form.headline }}
                        </p>
                        <p
                            class="mt-1 flex items-center justify-center gap-1 font-mono text-[11px] text-zinc-400 sm:justify-start"
                        >
                            <Globe class="h-3 w-3" />
                            https://{{ form.slug }}.toko-instan.id
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- ── Navigation Tabs ── -->
            <div class="flex gap-4 overflow-x-auto border-b border-black/10">
                <button
                    @click="activeTab = 'profil'"
                    class="flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 pb-3 text-xs font-extrabold transition-all"
                    :class="
                        activeTab === 'profil'
                            ? 'border-black text-black'
                            : 'border-transparent text-[#9090a0] hover:text-[#1c1c22]'
                    "
                >
                    <Store class="h-4 w-4" />
                    Profil Toko & Status
                </button>
                <button
                    @click="activeTab = 'banner'"
                    class="flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 pb-3 text-xs font-extrabold transition-all"
                    :class="
                        activeTab === 'banner'
                            ? 'border-black text-black'
                            : 'border-transparent text-[#9090a0] hover:text-[#1c1c22]'
                    "
                >
                    <ImageIcon class="h-4 w-4" />
                    Banner & Tampilan
                </button>
                <button
                    @click="activeTab = 'kontak'"
                    class="flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 pb-3 text-xs font-extrabold transition-all"
                    :class="
                        activeTab === 'kontak'
                            ? 'border-black text-black'
                            : 'border-transparent text-[#9090a0] hover:text-[#1c1c22]'
                    "
                >
                    <Phone class="h-4 w-4" />
                    Kontak & Medsos
                </button>
                <button
                    @click="activeTab = 'pajak'"
                    class="flex shrink-0 cursor-pointer items-center gap-1.5 border-b-2 pb-3 text-xs font-extrabold transition-all"
                    :class="
                        activeTab === 'pajak'
                            ? 'border-black text-black'
                            : 'border-transparent text-[#9090a0] hover:text-[#1c1c22]'
                    "
                >
                    <FileText class="h-4 w-4" />
                    Profil Pajak & Legalitas (PKP)
                </button>
            </div>

            <!-- ── Tab 1: Profil Toko ── -->
            <Card
                v-if="activeTab === 'profil'"
                class="rounded-2xl border-black/8 bg-white shadow-xs"
            >
                <CardContent class="flex flex-col gap-5 p-6">
                    <!-- Status Operasional Buka/Tutup Toko (P1-012) -->
                    <div
                        class="flex items-center justify-between rounded-xl border border-black/10 bg-[#faf9f6] p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl font-bold text-white shadow-xs"
                                :class="
                                    form.is_active
                                        ? 'bg-emerald-600'
                                        : 'bg-rose-600'
                                "
                            >
                                <Power class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4
                                        class="text-sm font-black text-[#1c1c22]"
                                    >
                                        Status Operasional Toko
                                    </h4>
                                    <Badge
                                        :class="
                                            form.is_active
                                                ? 'bg-emerald-600 text-white'
                                                : 'bg-rose-600 text-white'
                                        "
                                        class="text-[10px] font-extrabold"
                                    >
                                        {{
                                            form.is_active
                                                ? 'TOKO BUKAH (AKTIF)'
                                                : 'TOKO TUTUP (LIBUR)'
                                        }}
                                    </Badge>
                                </div>
                                <p class="mt-0.5 text-xs text-zinc-500">
                                    {{
                                        form.is_active
                                            ? 'Storefront publik aktif menerima pesanan dari pembeli.'
                                            : 'Storefront publik menampilkan pemberitahuan "Toko Sedang Libur".'
                                    }}
                                </p>
                            </div>
                        </div>
                        <Switch
                            :checked="form.is_active"
                            @update:checked="form.is_active = $event"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Nama Toko -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="name"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Nama Toko *</Label
                            >
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Contoh: Nike Official Store"
                                class="h-10 text-xs"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-[10px] font-semibold text-rose-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Subdomain Slug -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="slug"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Subdomain Slug Toko *</Label
                            >
                            <div class="flex items-center gap-1">
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    placeholder="nike-official"
                                    class="h-10 font-mono text-xs"
                                />
                                <span
                                    class="shrink-0 font-mono text-xs text-[#9090a0]"
                                    >.toko-instan.id</span
                                >
                            </div>
                            <p
                                v-if="form.errors.slug"
                                class="text-[10px] font-semibold text-rose-500"
                            >
                                {{ form.errors.slug }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Kategori Utama -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="category"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Kategori Utama Toko</Label
                            >
                            <Input
                                id="category"
                                v-model="form.category"
                                placeholder="Sportswear & Sneakers"
                                class="h-10 text-xs font-semibold"
                            />
                        </div>

                        <!-- Avatar Hue Color -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="avatar_hue"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Warna Theme Logo (Hue 0 - 360)</Label
                            >
                            <div class="flex items-center gap-3">
                                <Input
                                    id="avatar_hue"
                                    type="number"
                                    min="0"
                                    max="360"
                                    v-model.number="form.avatar_hue"
                                    class="h-10 w-28 font-mono text-xs"
                                />
                                <div
                                    class="h-9 w-9 rounded-xl border border-black/10 shadow-inner"
                                    :style="{
                                        backgroundColor: `hsl(${form.avatar_hue}, 70%, 50%)`,
                                    }"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Upload Logo File & URL -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="logo_file"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Upload File Logo Toko (PNG/JPG/WEBP)</Label
                            >
                            <Input
                                id="logo_file"
                                type="file"
                                accept="image/*"
                                @change="handleLogoFileChange"
                                class="h-10 cursor-pointer bg-white text-xs"
                            />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="logo"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Atau Paste URL Logo Gambar Toko</Label
                            >
                            <Input
                                id="logo"
                                v-model="form.logo"
                                placeholder="https://example.com/logo.jpg"
                                class="h-10 font-mono text-xs"
                            />
                        </div>
                    </div>

                    <!-- Deskripsi Toko -->
                    <div class="flex flex-col gap-1.5">
                        <Label
                            for="description"
                            class="text-xs font-bold text-[#1c1c22]"
                            >Deskripsi & Profil Toko</Label
                        >
                        <Textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Tuliskan deskripsi keaslian & keunggulan toko kamu..."
                            class="text-xs"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- ── Tab 2: Banner & Tampilan ── -->
            <Card
                v-if="activeTab === 'banner'"
                class="rounded-2xl border-black/8 bg-white shadow-xs"
            >
                <CardContent class="flex flex-col gap-5 p-6">
                    <!-- Headline Slogan -->
                    <div class="flex flex-col gap-1.5">
                        <Label
                            for="headline"
                            class="text-xs font-bold text-[#1c1c22]"
                            >Headline / Slogan Banner Webstore</Label
                        >
                        <Input
                            id="headline"
                            v-model="form.headline"
                            placeholder="JUST DO IT. — Koleksi Resmi Nike Indonesia"
                            class="h-10 text-xs font-semibold"
                        />
                    </div>

                    <!-- Upload Hero Banner File & URL -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="banner_file"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Upload File Gambar Hero Banner Toko
                                (PNG/JPG/WEBP)</Label
                            >
                            <Input
                                id="banner_file"
                                type="file"
                                accept="image/*"
                                @change="handleBannerFileChange"
                                class="h-10 cursor-pointer bg-white text-xs"
                            />
                            <p class="text-[10px] text-zinc-500">
                                Pilih gambar dari perangkat lokal Anda untuk
                                dijadikan Hero Banner toko.
                            </p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="banner_url"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Atau Paste URL Hero Banner</Label
                            >
                            <Input
                                id="banner_url"
                                v-model="form.banner_url"
                                placeholder="https://images.unsplash.com/photo-1542291026-7eec264c27ff"
                                class="h-10 font-mono text-xs"
                            />
                        </div>
                    </div>

                    <!-- Hero Banner Preview -->
                    <div v-if="form.banner_url" class="flex flex-col gap-2">
                        <Label class="text-xs font-bold text-[#9090a0]"
                            >Preview Hero Banner</Label
                        >
                        <div
                            class="relative aspect-3/1 w-full overflow-hidden rounded-2xl border border-black/10 bg-zinc-900"
                        >
                            <img
                                :src="form.banner_url"
                                class="h-full w-full object-cover opacity-80"
                            />
                            <div
                                class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/80 via-black/30 to-transparent p-6 text-white"
                            >
                                <p
                                    class="text-sm font-black tracking-widest text-amber-400 uppercase"
                                >
                                    {{ form.name }}
                                </p>
                                <h2 class="text-xl font-black uppercase">
                                    {{ form.headline }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- ── Tab 3: Kontak & Medsos ── -->
            <Card
                v-if="activeTab === 'kontak'"
                class="rounded-2xl border-black/8 bg-white shadow-xs"
            >
                <CardContent class="flex flex-col gap-5 p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- WhatsApp / Phone -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="phone"
                                class="text-xs font-bold text-[#1c1c22]"
                                >No. WhatsApp / Telepon CS</Label
                            >
                            <Input
                                id="phone"
                                v-model="form.phone"
                                placeholder="+6285264415051"
                                class="h-10 font-mono text-xs"
                            />
                        </div>

                        <!-- Email CS -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="email"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Email Pelayanan Toko</Label
                            >
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="support@nike-official.id"
                                class="h-10 font-mono text-xs"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Instagram -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="instagram"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Username Instagram</Label
                            >
                            <Input
                                id="instagram"
                                v-model="form.instagram"
                                placeholder="@nike_indonesia"
                                class="h-10 text-xs"
                            />
                        </div>

                        <!-- TikTok -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="tiktok"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Username TikTok</Label
                            >
                            <Input
                                id="tiktok"
                                v-model="form.tiktok"
                                placeholder="@nike_official"
                                class="h-10 text-xs"
                            />
                        </div>
                    </div>

                    <!-- Alamat Toko -->
                    <div class="flex flex-col gap-1.5">
                        <Label
                            for="address"
                            class="text-xs font-bold text-[#1c1c22]"
                            >Alamat Lengkap Toko / Gudang Origin</Label
                        >
                        <Textarea
                            id="address"
                            v-model="form.address"
                            rows="3"
                            placeholder="Jl. Jendral Sudirman No. 42, Jakarta Selatan..."
                            class="text-xs"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- ── Tab 4: Profil Pajak & Legalitas (P2-020) ── -->
            <Card
                v-if="activeTab === 'pajak'"
                class="rounded-2xl border-black/8 bg-white shadow-xs"
            >
                <CardContent class="flex flex-col gap-5 p-6">
                    <!-- Status PKP Switch -->
                    <div
                        class="flex items-center justify-between rounded-xl border border-black/10 bg-[#faf9f6] p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-black font-bold text-amber-400 shadow-xs"
                            >
                                <Building2 class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4
                                        class="text-sm font-black text-[#1c1c22]"
                                    >
                                        Status Pengusaha Kena Pajak (PKP)
                                    </h4>
                                    <Badge
                                        :class="
                                            form.is_pkp
                                                ? 'bg-black text-amber-400'
                                                : 'bg-zinc-200 text-zinc-700'
                                        "
                                        class="text-[10px] font-extrabold"
                                    >
                                        {{
                                            form.is_pkp
                                                ? 'PKP (Dapat Terbit Faktur Pajak PPN 11%)'
                                                : 'NON-PKP'
                                        }}
                                    </Badge>
                                </div>
                                <p class="mt-0.5 text-xs text-zinc-500">
                                    Aktifkan jika badan usaha kamu terdaftar
                                    sebagai Pengusaha Kena Pajak (PKP) resmi.
                                </p>
                            </div>
                        </div>
                        <Switch
                            :checked="form.is_pkp"
                            @update:checked="form.is_pkp = $event"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Nomor NPWP -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="npwp"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Nomor NPWP Wajib Pajak (16 Digit)</Label
                            >
                            <Input
                                id="npwp"
                                v-model="form.npwp"
                                placeholder="01.234.567.8-901.000"
                                class="h-10 font-mono text-xs"
                            />
                        </div>

                        <!-- NIK KTP -->
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="nik"
                                class="text-xs font-bold text-[#1c1c22]"
                                >NIK KTP Pemilik Toko</Label
                            >
                            <Input
                                id="nik"
                                v-model="form.nik"
                                placeholder="3171012345670001"
                                class="h-10 font-mono text-xs"
                            />
                        </div>
                    </div>

                    <!-- Nama Lengkap Faktur Pajak -->
                    <div class="flex flex-col gap-1.5">
                        <Label
                            for="tax_name"
                            class="text-xs font-bold text-[#1c1c22]"
                            >Nama Terdaftar pada NPWP / Faktur Pajak</Label
                        >
                        <Input
                            id="tax_name"
                            v-model="form.tax_name"
                            placeholder="PT Nike Indonesia Resmi"
                            class="h-10 text-xs font-semibold"
                        />
                    </div>

                    <!-- Alamat Faktur Pajak -->
                    <div class="flex flex-col gap-1.5">
                        <Label
                            for="tax_address"
                            class="text-xs font-bold text-[#1c1c22]"
                            >Alamat Lengkap Faktur Pajak</Label
                        >
                        <Textarea
                            id="tax_address"
                            v-model="form.tax_address"
                            rows="3"
                            placeholder="Gedung Menara Mandiri Lt. 18, Jl. Jend. Sudirman Kav. 54-55..."
                            class="text-xs"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Bottom Save Button -->
            <div class="flex justify-end pt-2">
                <Button
                    variant="amber"
                    size="lg"
                    class="h-11 gap-2 bg-black px-8 text-sm font-bold text-amber-400 shadow-md hover:bg-zinc-800"
                    :disabled="form.processing"
                    @click="submitForm"
                >
                    <Save class="h-5 w-5 text-amber-400" />
                    {{
                        form.processing
                            ? 'Menyimpan Perubahan...'
                            : 'Simpan Semua Perubahan'
                    }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
