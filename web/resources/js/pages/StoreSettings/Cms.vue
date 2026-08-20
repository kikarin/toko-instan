<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Palette,
    Star,
    Sparkles,
    ImageIcon,
    Save,
    Plus,
    Trash2,
    Home,
    BadgeCheck,
    Quote,
    ShoppingBag,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import {
    COLOR_TOKENS as colorTokens,
    useStoreCms,
} from '@/composables/useStoreCms';
import AppLayout from '@/layouts/AppLayout.vue';
import type { ProductOption } from '@/types/product';
import type { ThemeInfo } from '@/types/store';
import type { StoreShowcase } from '@/types/store';

interface Props {
    store: any;
    theme: ThemeInfo;
    showcase: StoreShowcase;
    themes: Record<string, ThemeInfo>;
    products: ProductOption[];
}

const props = defineProps<Props>();

const {
    selectedTheme,
    hero,
    about,
    contactShow,
    featuredIds,
    testimonials,
    previewColors,
    isCustom,
    previewStyle,
    selectTheme,
    onColorInput,
    toggleFeatured,
    addTestimonial,
    removeTestimonial,
    submit,
} = useStoreCms(props);
</script>

<template>

    <Head title="Tampilan & Konten — Toko Instan" />

    <AppLayout title="Tampilan & Konten" activePage="Tampilan & Konten">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-5 p-4 sm:p-6">
            <div>
                <p class="mb-1 text-xs font-extrabold tracking-widest text-primary uppercase">
                    Seller · Storefront
                </p>
                <h1 class="flex items-center gap-2 text-2xl font-extrabold text-foreground">
                    <Palette class="h-6 w-6" /> Tampilan & Konten Toko
                </h1>
                <p class="mt-1 text-xs text-muted-foreground">
                    Atur tema, warna, dan isi halaman toko Anda di storefront.
                </p>
            </div>

            <div class="grid gap-5 lg:grid-cols-[1fr_260px]">
                <div class="flex flex-col gap-5">
                    <!-- Theme picker -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Sparkles class="h-4 w-4 text-primary" />
                                Pilih Tema
                            </CardTitle>
                            <CardDescription class="text-xs">
                                Tema menentukan suasana halaman toko Anda.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                                <button v-for="(cfg, key) in themes" :key="key" type="button" @click="selectTheme(key)"
                                    class="flex cursor-pointer flex-col gap-2 rounded-2xl border p-3 text-left transition-all"
                                    :class="selectedTheme === key && !isCustom
                                            ? 'border-primary ring-2 ring-primary/30'
                                            : 'border-border hover:border-border/80'
                                        ">
                                    <span class="flex gap-1.5">
                                        <span class="h-4 w-4 rounded-full" :style="{
                                            backgroundColor:
                                                cfg.colors.primary,
                                        }" />
                                        <span class="h-4 w-4 rounded-full" :style="{
                                            backgroundColor:
                                                cfg.colors.secondary,
                                        }" />
                                        <span class="h-4 w-4 rounded-full" :style="{
                                            backgroundColor:
                                                cfg.colors.accent,
                                        }" />
                                        <span class="h-4 w-4 rounded-full" :style="{
                                            backgroundColor:
                                                cfg.colors.strong,
                                        }" />
                                    </span>
                                    <span class="text-xs font-bold text-foreground">{{ cfg.label }}</span>
                                    <span class="text-[10px] text-muted-foreground">{{
                                        cfg.font
                                        }}</span>
                                </button>

                                <!-- Custom preset -->
                                <button type="button" @click="selectTheme('custom')"
                                    class="flex cursor-pointer flex-col gap-2 rounded-2xl border border-dashed p-3 text-left transition-all"
                                    :class="isCustom
                                            ? 'border-primary ring-2 ring-primary/30'
                                            : 'border-border hover:border-border/80'
                                        ">
                                    <span class="flex items-center gap-1.5">
                                        <Palette class="h-4 w-4 text-primary" />
                                        <span class="text-xs font-bold text-foreground">Custom</span>
                                    </span>
                                    <span class="text-[10px] text-muted-foreground">Ubah warna sesuai keinginan</span>
                                </button>
                            </div>

                            <!-- Custom color pickers -->
                            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-3">
                                <div v-for="(cb, ci) in colorTokens" :key="ci" class="flex items-center gap-2">
                                    <Label class="w-16 text-xs">{{
                                        cb.label
                                        }}</Label>
                                    <input type="color" :value="previewColors[cb.token]"
                                        @input="onColorInput(cb.token, $event)"
                                        class="h-9 w-12 cursor-pointer rounded-lg border border-border bg-transparent" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Hero -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Home class="h-4 w-4 text-primary" /> Hero
                                Banner
                            </CardTitle>
                            <CardDescription class="text-xs">
                                Banner utama di atas halaman toko.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-3">
                            <div class="flex flex-col gap-1.5">
                                <Label>Gambar banner (URL)</Label>
                                <Input v-model="hero.image" placeholder="https://.../banner.jpg" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label>Judul</Label>
                                <Input v-model="hero.title" placeholder="Temukan Produk Terbaik" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label>Sub judul</Label>
                                <Textarea v-model="hero.subtitle" rows="2" placeholder="Deskripsi singkat toko Anda" />
                            </div>
                            <div class="flex flex-col gap-1.5 sm:max-w-xs">
                                <Label>Label tombol</Label>
                                <Input v-model="hero.cta_label" placeholder="Lihat Produk" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Featured products -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Star class="h-4 w-4 text-primary" /> Produk
                                Unggulan
                            </CardTitle>
                            <CardDescription class="text-xs">
                                Pilih produk yang ditampilkan di bagian
                                unggulan.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="products.length" class="grid gap-2 sm:grid-cols-2">
                                <label v-for="p in products" :key="p.id"
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border p-2.5 transition-all"
                                    :class="featuredIds.includes(p.id)
                                            ? 'border-primary bg-primary/5'
                                            : 'border-border hover:border-border/80'
                                        ">
                                    <input type="checkbox" class="h-4 w-4 accent-primary"
                                        :checked="featuredIds.includes(p.id)" @change="toggleFeatured(p.id)" />
                                    <img v-if="p.img" :src="p.img" class="h-9 w-9 rounded-lg object-cover" alt="" />
                                    <div v-else
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted">
                                        <ImageIcon class="h-4 w-4 text-muted-foreground/50" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-bold text-foreground">
                                            {{ p.name }}
                                        </p>
                                        <p class="text-[10px] text-muted-foreground">
                                            {{ p.price }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                            <p v-else class="text-xs text-muted-foreground">
                                Belum ada produk aktif. Tambahkan produk dulu di
                                halaman Produk.
                            </p>
                        </CardContent>
                    </Card>

                    <!-- About -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <BadgeCheck class="h-4 w-4 text-primary" />
                                Tentang Toko
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-3">
                            <div class="flex flex-col gap-1.5">
                                <Label>Judul</Label>
                                <Input v-model="about.title" placeholder="Tentang Toko Ini" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label>Deskripsi</Label>
                                <Textarea v-model="about.text" rows="4" placeholder="Ceritakan tentang toko Anda..." />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Testimonials -->
                    <Card>
                        <CardHeader class="flex flex-row items-start justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2 text-sm">
                                    <Quote class="h-4 w-4 text-primary" />
                                    Testimoni
                                </CardTitle>
                                <CardDescription class="text-xs">Ulasan dari pelanggan
                                    Anda.</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" class="text-xs" @click="addTestimonial">
                                <Plus class="h-3.5 w-3.5" /> Tambah
                            </Button>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-3">
                            <div v-for="(t, i) in testimonials" :key="i"
                                class="relative flex flex-col gap-2 rounded-2xl border border-border p-3.5">
                                <button type="button"
                                    class="absolute top-2.5 right-2.5 cursor-pointer text-muted-foreground/80 hover:text-destructive"
                                    @click="removeTestimonial(i)">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <div class="flex flex-col gap-1">
                                        <Label>Nama</Label>
                                        <Input v-model="t.name" placeholder="Budi" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <Label>Peran (opsional)</Label>
                                        <Input v-model="t.role" placeholder="Pembeli sejak 2024" />
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <Label>Ulasan</Label>
                                    <Textarea v-model="t.text" rows="2" placeholder="Produk bagus, recommended!" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <Label class="text-xs">Rating</Label>
                                    <select v-model.number="t.rating"
                                        class="h-8 rounded-lg border border-border bg-card px-2 text-xs">
                                        <option v-for="n in 5" :key="n" :value="n">
                                            {{ n }} bintang
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <p v-if="!testimonials.length" class="text-xs text-muted-foreground">
                                Belum ada testimoni. Klik "Tambah" untuk
                                menambahkan.
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="flex items-center justify-between p-4">
                            <div>
                                <p class="text-sm font-bold text-foreground">
                                    Tampilkan kontak
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Info kontak Anda dari pengaturan toko.
                                </p>
                            </div>
                            <Switch :checked="contactShow" @update:checked="
                                (v: boolean) => (contactShow = v)
                            " />
                        </CardContent>
                    </Card>

                    <Button class="gap-2 text-sm font-bold" @click="submit">
                        <Save class="h-4 w-4" /> Simpan Perubahan
                    </Button>
                </div>

                <!-- Live preview -->
                <div class="lg:sticky lg:top-24 lg:self-start">
                    <p class="mb-2 text-[10px] font-black tracking-widest text-muted-foreground uppercase">
                        Pratinjau
                    </p>
                    <div class="overflow-hidden rounded-2xl border border-border shadow-lg"
                        :style="previewStyle">
                        <div class="bg-[var(--primary)] px-5 py-6">
                            <p class="flex items-center gap-1 text-[9px] font-bold tracking-widest text-[var(--accent)] uppercase">
                                <Sparkles class="h-3 w-3" /> Halaman Toko
                            </p>
                            <h2 class="mt-1 text-base font-black text-primary-foreground drop-shadow-sm">
                                {{ hero.title || 'Judul Hero' }}
                            </h2>
                            <p class="mt-1 text-[11px] leading-relaxed text-primary-foreground/90 drop-shadow-sm">
                                {{
                                    hero.subtitle ||
                                    'Sub judul hero akan tampil di sini.'
                                }}
                            </p>
                            <div class="mt-3 inline-flex h-8 items-center rounded-lg px-4 text-[11px] font-bold text-white shadow-md transition-transform hover:scale-105"
                                :style="{ backgroundColor: 'var(--destructive)' }">
                                {{ hero.cta_label || 'Lihat Produk' }}
                            </div>
                        </div>
                        <div class="border-t border-border bg-card px-5 py-4">
                            <p class="text-xs font-black text-foreground">
                                {{ about.title || 'Tentang Toko' }}
                            </p>
                            <p class="mt-1 text-[11px] text-muted-foreground">
                                {{ about.text || 'Deskripsi toko Anda...' }}
                            </p>
                            <div class="mt-3 flex items-center gap-1.5">
                                <span class="flex h-4 w-4 items-center justify-center rounded-full" :style="{ backgroundColor: 'var(--accent)' }">
                                    <ShoppingBag class="h-2.5 w-2.5 text-white" />
                                </span>
                                <span class="text-[10px] font-semibold text-foreground">
                                    {{ featuredIds.length }} produk unggulan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
