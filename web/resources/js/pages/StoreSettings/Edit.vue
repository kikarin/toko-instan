<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Store,
    Image as ImageIcon,
    Phone,
    Save,
    Sparkles,
    ShieldCheck,
    Globe,
    FileText,
    Building2,
    Power,
    X,
} from 'lucide-vue-next';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { useStoreSettings } from '@/composables/useStoreSettings';
import { useStoreTheme } from '@/composables/useStoreTheme';
import type { StoreData } from '@/types/store';

interface Props {
    store: StoreData;
}

const props = defineProps<Props>();

const {
    activeTab,
    form,
    handleBannerFileChange,
    handleLogoFileChange,
    submitForm,
    bannerPreviewUrls,
    removeBanner,
    newBannerUrl,
    addBannerUrl,
    newHighlight,
    addHighlight,
    removeHighlight,
} = useStoreSettings(props.store);

// Inject theme colors to :root so the preview can use --brand, --brand-secondary, etc.
useStoreTheme();
</script>

<template>
    <Head title="Pengaturan & Branding Toko — Dashboard Merchant" />

    <AppLayout title="Pengaturan Toko" activePage="Pengaturan Toko">
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
                            variant="default"
                            class="bg-foreground text-[10px] font-extrabold text-accent uppercase"
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
                        variant="default"
                        size="sm"
                        class="h-9 gap-1.5 bg-foreground text-xs font-bold text-accent shadow-md hover:bg-foreground/90"
                        :disabled="form.processing"
                        @click="submitForm"
                    >
                        <Save class="h-4 w-4 text-accent" />
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
                class="overflow-hidden rounded-2xl border-border bg-card shadow-md"
            >
                <CardHeader
                    class="flex flex-row items-center justify-between bg-brand p-4 text-brand-foreground"
                >
                    <div class="flex items-center gap-2">
                        <Sparkles class="h-4 w-4" />
                        <span
                            class="text-xs font-black tracking-wider uppercase"
                            >Live Preview Header Webstore</span
                        >
                    </div>
                    <Badge
                        class="bg-brand-accent text-[9px] font-black text-brand-foreground uppercase"
                        >OFFICIAL STORE</Badge
                    >
                </CardHeader>
                <CardContent
                    class="flex flex-col items-center gap-4 bg-gradient-to-r from-brand-soft to-card p-4 sm:flex-row sm:p-6"
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
                                class="text-lg font-black tracking-wide text-foreground uppercase"
                            >
                                {{ form.name }}
                            </h3>
                            <ShieldCheck class="h-5 w-5 text-brand" />
                        </div>
                        <p class="mt-0.5 text-xs font-semibold text-muted-foreground">
                            {{ form.headline }}
                        </p>
                        <p
                            class="mt-1 flex items-center justify-center gap-1 font-mono text-[11px] text-muted-foreground sm:justify-start"
                        >
                            <Globe class="h-3 w-3" />
                            https://{{ form.slug }}.toko-instan.id
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- ── Navigation Tabs ── -->
            <div class="flex gap-4 overflow-x-auto border-b border-border">
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
                        class="flex items-center justify-between rounded-xl border border-border bg-[#faf9f6] p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl font-bold text-white shadow-xs"
                                :class="
                                    form.is_active
                                        ? 'bg-emerald-600'
                                        : 'bg-destructive'
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
                                                : 'bg-destructive text-white'
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
                                <p class="mt-0.5 text-xs text-muted-foreground">
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
                                placeholder="Contoh: Toko Sepatu Sport"
                                class="h-10 text-xs"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-[10px] font-semibold text-destructive"
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
                                class="text-[10px] font-semibold text-destructive"
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
                                    class="h-9 w-9 rounded-xl border border-border shadow-inner"
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
                            placeholder="Contoh: Sepatu & Fashion Sportworn Terlengkap"
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
                                multiple
                                accept="image/*"
                                @change="handleBannerFileChange"
                                class="h-10 cursor-pointer bg-white text-xs"
                            />
                            <p class="text-[10px] text-muted-foreground">
                                Pilih beberapa gambar sekaligus untuk dijadikan Hero Banner Carousel.
                            </p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <Label
                                for="banner_url"
                                class="text-xs font-bold text-[#1c1c22]"
                                >Atau Tambah URL Hero Banner</Label
                            >
                            <div class="flex gap-2">
                                <Input
                                    id="banner_url"
                                    v-model="newBannerUrl"
                                    placeholder="https://example.com/hero-banner.jpg"
                                    class="h-10 flex-1 font-mono text-xs"
                                    @keyup.enter="addBannerUrl"
                                />
                                <Button type="button" @click="addBannerUrl" variant="default" class="h-10">Tambah</Button>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Banner Preview Carousel -->
                    <div v-if="bannerPreviewUrls.length > 0" class="flex flex-col gap-2">
                        <Label class="text-xs font-bold text-[#9090a0]"
                            >Preview Banner Carousel ({{ bannerPreviewUrls.length }}/5)</Label
                        >
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                            <div
                                v-for="(url, index) in bannerPreviewUrls"
                                :key="index"
                                class="group relative aspect-video w-full overflow-hidden rounded-2xl border border-border bg-card"
                            >
                                <img
                                    :src="url"
                                    class="h-full w-full object-cover opacity-80"
                                />
                                <div
                                    class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/80 via-black/30 to-transparent p-4 text-white"
                                >
                                    <p
                                        class="text-[10px] font-black tracking-widest text-accent uppercase"
                                    >
                                        {{ form.name }}
                                    </p>
                                    <h2 class="text-sm font-black uppercase line-clamp-1">
                                        {{ form.headline }}
                                    </h2>
                                </div>
                                <button
                                    type="button"
                                    @click="removeBanner(index)"
                                    class="absolute top-2 right-2 rounded-full bg-destructive/100 p-1.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                    title="Hapus Banner"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <Separator class="my-2 bg-black/5" />

                    <!-- Promo Highlights -->
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-1.5">
                            <Label class="text-xs font-bold text-[#1c1c22]">Highlights / Promo Badges (Maks 3)</Label>
                            <p class="text-[10px] text-muted-foreground">
                                Tambahkan maksimal 3 poin keunggulan toko yang akan ditampilkan di bawah Banner (contoh: "Gratis Ongkir", "Garansi Retur 30 Hari").
                            </p>
                            
                            <div class="flex gap-2">
                                <Input
                                    v-model="newHighlight"
                                    placeholder="Ketik poin promo..."
                                    class="h-10 flex-1 text-xs"
                                    @keyup.enter="addHighlight"
                                    :disabled="form.highlights.length >= 3"
                                />
                                <Button 
                                    type="button" 
                                    @click="addHighlight" 
                                    variant="default" 
                                    class="h-10"
                                    :disabled="form.highlights.length >= 3"
                                >
                                    Tambah
                                </Button>
                            </div>
                        </div>

                        <div v-if="form.highlights.length > 0" class="flex flex-wrap gap-2">
                            <Badge 
                                v-for="(hl, idx) in form.highlights" 
                                :key="idx" 
                                variant="outline" 
                                class="flex items-center gap-1.5 border-border bg-muted px-2.5 py-1 text-xs text-foreground"
                            >
                                {{ hl }}
                                <button 
                                    type="button" 
                                    @click="removeHighlight(idx)" 
                                    class="ml-1 text-muted-foreground hover:text-destructive"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                            </Badge>
                        </div>
                    </div>

                    <Separator class="my-2 bg-black/5" />

                    <!-- Teks Tentang Toko & Widget Garansi -->
                    <div class="flex flex-col gap-6">
                        <!-- About Text -->
                        <div class="flex flex-col gap-1.5">
                            <Label for="about_text" class="text-xs font-bold text-[#1c1c22]">
                                Teks Deskripsi (Sub-judul Hero)
                            </Label>
                            <Textarea
                                id="about_text"
                                v-model="form.hero_config.about_text"
                                placeholder="Contoh: Produk berkualitas dengan garansi keaslian dan layanan bebas ongkir."
                                class="min-h-[80px] text-xs"
                            />
                        </div>

                        <!-- Widget Settings -->
                        <div class="flex flex-col gap-3 rounded-xl border border-border bg-muted p-4">
                            <Label class="text-sm font-black text-[#1c1c22]">Widget Garansi (Kanan Atas)</Label>
                            <p class="text-[10px] text-muted-foreground">Sesuaikan teks pada kotak garansi di halaman depan.</p>
                            
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <Label for="widget_title" class="text-[10px] font-bold text-muted-foreground">Judul Widget</Label>
                                    <Input
                                        id="widget_title"
                                        v-model="form.hero_config.widget_title"
                                        placeholder="Contoh: Belanja Aman"
                                        class="h-9 text-xs bg-white"
                                    />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <Label for="widget_subtitle" class="text-[10px] font-bold text-muted-foreground">Fitur Utama</Label>
                                    <Input
                                        id="widget_subtitle"
                                        v-model="form.hero_config.widget_subtitle"
                                        placeholder="Contoh: Escrow & Buyer Protection"
                                        class="h-9 text-xs bg-white"
                                    />
                                </div>
                                <div class="flex flex-col gap-1.5 md:col-span-2">
                                    <Label for="widget_description" class="text-[10px] font-bold text-muted-foreground">Deskripsi Singkat</Label>
                                    <Input
                                        id="widget_description"
                                        v-model="form.hero_config.widget_description"
                                        placeholder="Contoh: Uang kembali jika barang tidak sesuai"
                                        class="h-9 text-xs bg-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Fake Buyer Stats -->
                        <div class="flex flex-col gap-1.5 w-full md:w-1/2">
                            <Label for="fake_buyer_count" class="text-xs font-bold text-[#1c1c22]">
                                Teks Jumlah Pembeli Fiktif (Opsional)
                            </Label>
                            <Input
                                id="fake_buyer_count"
                                v-model="form.hero_config.fake_buyer_count"
                                placeholder="Contoh: 54rb+"
                                class="h-10 text-xs"
                            />
                            <p class="text-[10px] text-muted-foreground">Teks ini akan menggantikan angka pesanan asli di statistik hero.</p>
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
                        class="flex items-center justify-between rounded-xl border border-border bg-[#faf9f6] p-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-black font-bold text-accent shadow-xs"
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
                                                ? 'bg-black text-accent'
                                                : 'bg-muted text-foreground'
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
                                <p class="mt-0.5 text-xs text-muted-foreground">
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
                            placeholder="PT Contoh Dagang Indonesia"
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
                    variant="default"
                    size="lg"
                    class="h-11 gap-2 bg-black px-8 text-sm font-bold text-accent shadow-md hover:bg-foreground/90"
                    :disabled="form.processing"
                    @click="submitForm"
                >
                    <Save class="h-5 w-5 text-accent" />
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
