<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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
} from 'lucide-vue-next';
import { ref } from 'vue';
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
import { toast } from '@/components/ui/sonner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

interface ThemeColors {
    primary: string;
    secondary: string;
    accent: string;
    strong: string;
}

interface ThemeInfo {
    key: string;
    label: string;
    colors: ThemeColors;
    font: string;
}

interface ProductOption {
    id: number;
    name: string;
    price: string;
    img: string | null;
}

interface Testimonial {
    name: string;
    role: string;
    text: string;
    rating: number;
}

interface Showcase {
    hero: {
        title: string;
        subtitle: string;
        cta_label: string;
        image: string | null;
    };
    about: { title: string; text: string };
    testimonials: Testimonial[];
    contact: { show: boolean };
}

interface Props {
    store: any;
    theme: ThemeInfo;
    showcase: Showcase;
    themes: Record<string, ThemeInfo>;
    products: ProductOption[];
}

const props = defineProps<Props>();

const DEFAULT_COLORS: ThemeColors = {
    primary: '#3F9AAE',
    secondary: '#79C9C5',
    accent: '#FFE2AF',
    strong: '#F96E5B',
};

const selectedTheme = ref(props.theme?.key || 'teal');
const hero = ref({
    title: props.showcase?.hero?.title ?? '',
    subtitle: props.showcase?.hero?.subtitle ?? '',
    cta_label: props.showcase?.hero?.cta_label ?? '',
    image: props.showcase?.hero?.image ?? '',
});
const about = ref({ ...(props.showcase?.about ?? {}) });
const contactShow = ref(props.showcase?.contact?.show ?? true);
const featuredIds = ref<number[]>(
    props.store?.showcase?.featured_product_ids ?? [],
);
const testimonials = ref<Testimonial[]>(
    (props.showcase?.testimonials ?? []).map((t) => ({
        ...t,
        rating: t.rating || 5,
    })),
);

const previewColors = ref<ThemeColors>(
    props.theme?.colors ?? { ...DEFAULT_COLORS },
);

const isCustom = ref(props.theme?.key === 'custom');

const previewStyle = ref({
    '--brand': previewColors.value.primary,
    '--brand-secondary': previewColors.value.secondary,
    '--brand-accent': previewColors.value.accent,
    '--brand-strong': previewColors.value.strong,
});

function selectTheme(key: string) {
    selectedTheme.value = key;
    isCustom.value = key === 'custom';
    const cfg = props.themes[key];

    if (cfg) {
        previewColors.value = { ...cfg.colors };
        refreshPreviewStyle();
    }
}

function onColorInput(token: keyof ThemeColors, e: Event) {
    const target = e.target as HTMLInputElement;

    if (!isCustom.value) {
        isCustom.value = true;
        selectedTheme.value = 'custom';
    }

    previewColors.value = { ...previewColors.value, [token]: target.value };
    refreshPreviewStyle();
}

function refreshPreviewStyle() {
    previewStyle.value = {
        '--brand': previewColors.value.primary,
        '--brand-secondary': previewColors.value.secondary,
        '--brand-accent': previewColors.value.accent,
        '--brand-strong': previewColors.value.strong,
    };

    if (typeof document !== 'undefined') {
        const root = document.documentElement;
        root.style.setProperty('--brand', previewColors.value.primary);
        root.style.setProperty(
            '--brand-secondary',
            previewColors.value.secondary,
        );
        root.style.setProperty('--brand-accent', previewColors.value.accent);
        root.style.setProperty('--brand-strong', previewColors.value.strong);
        root.style.setProperty(
            '--brand-soft',
            hexToRgba(previewColors.value.primary, 0.15),
        );

        root.style.setProperty(
            '--sidebar-primary',
            previewColors.value.primary,
        );
        root.style.setProperty(
            '--sidebar-accent',
            hexToRgba(previewColors.value.primary, 0.15),
        );
        root.style.setProperty(
            '--sidebar-accent-foreground',
            previewColors.value.primary,
        );
        root.style.setProperty('--sidebar-ring', previewColors.value.primary);
    }
}

function hexToRgba(hex: string, alpha: number): string {
    const clean = hex.replace('#', '');

    if (clean.length !== 6) {
        return 'rgba(224, 124, 40, 0.15)';
    }

    const n = parseInt(clean, 16);

    return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${alpha})`;
}

const colorTokens: { token: keyof ThemeColors; label: string }[] = [
    { token: 'primary', label: 'Utama' },
    { token: 'secondary', label: 'Sekunder' },
    { token: 'accent', label: 'Aksen' },
    { token: 'strong', label: 'CTA' },
];

function toggleFeatured(id: number) {
    featuredIds.value = featuredIds.value.includes(id)
        ? featuredIds.value.filter((x) => x !== id)
        : [...featuredIds.value, id];
}

function addTestimonial() {
    testimonials.value.push({ name: '', role: '', text: '', rating: 5 });
}

function removeTestimonial(i: number) {
    testimonials.value.splice(i, 1);
}

function submit() {
    router.put(
        '/store-cms',
        {
            theme: selectedTheme.value,
            theme_colors: { ...previewColors.value },
            showcase: {
                hero: hero.value,
                about: about.value,
                contact: { show: contactShow.value },
                featured_product_ids: featuredIds.value,
                testimonials: testimonials.value.filter((t) => t.text?.trim()),
            },
        },
        {
            preserveScroll: true,
            onSuccess: () => toast.success('Tampilan & konten disimpan!'),
            onError: () =>
                toast.error('Gagal menyimpan. Periksa kembali isian Anda.'),
        },
    );
}
</script>

<template>
    <Head title="Tampilan & Konten — Toko Instan" />

    <AppLayout title="Tampilan & Konten" activePage="Tampilan & Konten">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-5 p-4 sm:p-6">
            <div>
                <p
                    class="mb-1 text-xs font-extrabold tracking-widest text-[#e07c28] uppercase"
                >
                    Seller · Storefront
                </p>
                <h1
                    class="flex items-center gap-2 text-2xl font-extrabold text-[#1c1c22]"
                >
                    <Palette class="h-6 w-6" /> Tampilan & Konten Toko
                </h1>
                <p class="mt-1 text-xs text-[#9090a0]">
                    Atur tema, warna, dan isi halaman toko Anda di storefront.
                </p>
            </div>

            <div class="grid gap-5 lg:grid-cols-[1fr_260px]">
                <div class="flex flex-col gap-5">
                    <!-- Theme picker -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Sparkles class="h-4 w-4 text-[#e07c28]" />
                                Pilih Tema
                            </CardTitle>
                            <CardDescription class="text-xs">
                                Tema menentukan suasana halaman toko Anda.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3"
                            >
                                <button
                                    v-for="(cfg, key) in themes"
                                    :key="key"
                                    type="button"
                                    @click="selectTheme(key)"
                                    class="flex cursor-pointer flex-col gap-2 rounded-2xl border p-3 text-left transition-all"
                                    :class="
                                        selectedTheme === key && !isCustom
                                            ? 'border-[#e07c28] ring-2 ring-[#e07c28]/30'
                                            : 'border-black/8 hover:border-black/20'
                                    "
                                >
                                    <span class="flex gap-1.5">
                                        <span
                                            class="h-4 w-4 rounded-full"
                                            :style="{
                                                backgroundColor:
                                                    cfg.colors.primary,
                                            }"
                                        />
                                        <span
                                            class="h-4 w-4 rounded-full"
                                            :style="{
                                                backgroundColor:
                                                    cfg.colors.secondary,
                                            }"
                                        />
                                        <span
                                            class="h-4 w-4 rounded-full"
                                            :style="{
                                                backgroundColor:
                                                    cfg.colors.accent,
                                            }"
                                        />
                                        <span
                                            class="h-4 w-4 rounded-full"
                                            :style="{
                                                backgroundColor:
                                                    cfg.colors.strong,
                                            }"
                                        />
                                    </span>
                                    <span
                                        class="text-xs font-bold text-[#1c1c22]"
                                        >{{ cfg.label }}</span
                                    >
                                    <span class="text-[10px] text-[#9090a0]">{{
                                        cfg.font
                                    }}</span>
                                </button>

                                <!-- Custom preset -->
                                <button
                                    type="button"
                                    @click="selectTheme('custom')"
                                    class="flex cursor-pointer flex-col gap-2 rounded-2xl border border-dashed p-3 text-left transition-all"
                                    :class="
                                        isCustom
                                            ? 'border-[#e07c28] ring-2 ring-[#e07c28]/30'
                                            : 'border-black/15 hover:border-black/30'
                                    "
                                >
                                    <span class="flex items-center gap-1.5">
                                        <Palette
                                            class="h-4 w-4 text-[#e07c28]"
                                        />
                                        <span
                                            class="text-xs font-bold text-[#1c1c22]"
                                            >Custom</span
                                        >
                                    </span>
                                    <span class="text-[10px] text-[#9090a0]"
                                        >Ubah warna sesuai keinginan</span
                                    >
                                </button>
                            </div>

                            <!-- Custom color pickers -->
                            <div
                                class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-3"
                            >
                                <div
                                    v-for="(cb, ci) in colorTokens"
                                    :key="ci"
                                    class="flex items-center gap-2"
                                >
                                    <Label class="w-16 text-xs">{{
                                        cb.label
                                    }}</Label>
                                    <input
                                        type="color"
                                        :value="previewColors[cb.token]"
                                        @input="onColorInput(cb.token, $event)"
                                        class="h-9 w-12 cursor-pointer rounded-lg border border-black/10 bg-transparent"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Hero -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Home class="h-4 w-4 text-[#e07c28]" /> Hero
                                Banner
                            </CardTitle>
                            <CardDescription class="text-xs">
                                Banner utama di atas halaman toko.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-3">
                            <div class="flex flex-col gap-1.5">
                                <Label>Gambar banner (URL)</Label>
                                <Input
                                    v-model="hero.image"
                                    placeholder="https://.../banner.jpg"
                                />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label>Judul</Label>
                                <Input
                                    v-model="hero.title"
                                    placeholder="Temukan Produk Terbaik"
                                />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label>Sub judul</Label>
                                <Textarea
                                    v-model="hero.subtitle"
                                    rows="2"
                                    placeholder="Deskripsi singkat toko Anda"
                                />
                            </div>
                            <div class="flex flex-col gap-1.5 sm:max-w-xs">
                                <Label>Label tombol</Label>
                                <Input
                                    v-model="hero.cta_label"
                                    placeholder="Lihat Produk"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Featured products -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Star class="h-4 w-4 text-[#e07c28]" /> Produk
                                Unggulan
                            </CardTitle>
                            <CardDescription class="text-xs">
                                Pilih produk yang ditampilkan di bagian
                                unggulan.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="products.length"
                                class="grid gap-2 sm:grid-cols-2"
                            >
                                <label
                                    v-for="p in products"
                                    :key="p.id"
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border p-2.5 transition-all"
                                    :class="
                                        featuredIds.includes(p.id)
                                            ? 'border-[#e07c28] bg-[#e07c28]/5'
                                            : 'border-black/8 hover:border-black/20'
                                    "
                                >
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 accent-[#e07c28]"
                                        :checked="featuredIds.includes(p.id)"
                                        @change="toggleFeatured(p.id)"
                                    />
                                    <img
                                        v-if="p.img"
                                        :src="p.img"
                                        class="h-9 w-9 rounded-lg object-cover"
                                        alt=""
                                    />
                                    <div
                                        v-else
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#f5f4f0]"
                                    >
                                        <ImageIcon
                                            class="h-4 w-4 text-[#c8c8d5]"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-xs font-bold text-[#1c1c22]"
                                        >
                                            {{ p.name }}
                                        </p>
                                        <p class="text-[10px] text-[#9090a0]">
                                            {{ p.price }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                            <p v-else class="text-xs text-[#9090a0]">
                                Belum ada produk aktif. Tambahkan produk dulu di
                                halaman Produk.
                            </p>
                        </CardContent>
                    </Card>

                    <!-- About -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <BadgeCheck class="h-4 w-4 text-[#e07c28]" />
                                Tentang Toko
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-3">
                            <div class="flex flex-col gap-1.5">
                                <Label>Judul</Label>
                                <Input
                                    v-model="about.title"
                                    placeholder="Tentang Toko Ini"
                                />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <Label>Deskripsi</Label>
                                <Textarea
                                    v-model="about.text"
                                    rows="4"
                                    placeholder="Ceritakan tentang toko Anda..."
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Testimonials -->
                    <Card>
                        <CardHeader
                            class="flex flex-row items-start justify-between"
                        >
                            <div>
                                <CardTitle
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <Quote class="h-4 w-4 text-[#e07c28]" />
                                    Testimoni
                                </CardTitle>
                                <CardDescription class="text-xs"
                                    >Ulasan dari pelanggan
                                    Anda.</CardDescription
                                >
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                class="text-xs"
                                @click="addTestimonial"
                            >
                                <Plus class="h-3.5 w-3.5" /> Tambah
                            </Button>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-3">
                            <div
                                v-for="(t, i) in testimonials"
                                :key="i"
                                class="relative flex flex-col gap-2 rounded-2xl border border-black/8 p-3.5"
                            >
                                <button
                                    type="button"
                                    class="absolute top-2.5 right-2.5 cursor-pointer text-[#9090a0] hover:text-red-500"
                                    @click="removeTestimonial(i)"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <div class="flex flex-col gap-1">
                                        <Label>Nama</Label>
                                        <Input
                                            v-model="t.name"
                                            placeholder="Budi"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <Label>Peran (opsional)</Label>
                                        <Input
                                            v-model="t.role"
                                            placeholder="Pembeli sejak 2024"
                                        />
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <Label>Ulasan</Label>
                                    <Textarea
                                        v-model="t.text"
                                        rows="2"
                                        placeholder="Produk bagus, recommended!"
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <Label class="text-xs">Rating</Label>
                                    <select
                                        v-model.number="t.rating"
                                        class="h-8 rounded-lg border border-black/10 bg-white px-2 text-xs"
                                    >
                                        <option
                                            v-for="n in 5"
                                            :key="n"
                                            :value="n"
                                        >
                                            {{ n }} bintang
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <p
                                v-if="!testimonials.length"
                                class="text-xs text-[#9090a0]"
                            >
                                Belum ada testimoni. Klik "Tambah" untuk
                                menambahkan.
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent
                            class="flex items-center justify-between p-4"
                        >
                            <div>
                                <p class="text-sm font-bold text-[#1c1c22]">
                                    Tampilkan kontak
                                </p>
                                <p class="text-xs text-[#9090a0]">
                                    Info kontak Anda dari pengaturan toko.
                                </p>
                            </div>
                            <Switch
                                :checked="contactShow"
                                @update:checked="
                                    (v: boolean) => (contactShow = v)
                                "
                            />
                        </CardContent>
                    </Card>

                    <Button class="gap-2 text-sm font-bold" @click="submit">
                        <Save class="h-4 w-4" /> Simpan Perubahan
                    </Button>
                </div>

                <!-- Live preview -->
                <div class="lg:sticky lg:top-24 lg:self-start">
                    <p
                        class="mb-2 text-[10px] font-black tracking-widest text-[#9090a0] uppercase"
                    >
                        Pratinjau
                    </p>
                    <div
                        class="overflow-hidden rounded-2xl border border-black/8 bg-[#16131f] shadow-lg"
                        :style="previewStyle"
                    >
                        <div
                            class="bg-gradient-to-br from-[#16131f] via-[#231e35] to-[#16131f] px-5 py-6"
                        >
                            <p
                                class="text-[9px] font-bold tracking-widest text-white/40 uppercase"
                            >
                                Halaman Toko
                            </p>
                            <h2 class="mt-1 text-base font-black text-white">
                                {{ hero.title || 'Judul Hero' }}
                            </h2>
                            <p
                                class="mt-1 text-[11px] leading-relaxed text-white/50"
                            >
                                {{
                                    hero.subtitle ||
                                    'Sub judul hero akan tampil di sini.'
                                }}
                            </p>
                            <div
                                class="mt-3 inline-flex h-8 items-center rounded-lg px-3 text-[11px] font-bold text-white"
                                :style="{ backgroundColor: 'var(--brand)' }"
                            >
                                {{ hero.cta_label || 'Lihat Produk' }}
                            </div>
                        </div>
                        <div class="border-t border-white/8 bg-white px-5 py-4">
                            <p class="text-xs font-black text-[#1c1c22]">
                                {{ about.title || 'Tentang Toko' }}
                            </p>
                            <p class="mt-1 text-[11px] text-[#9090a0]">
                                {{ about.text || 'Deskripsi toko Anda...' }}
                            </p>
                            <div class="mt-3 flex items-center gap-1.5">
                                <span
                                    class="h-2.5 w-2.5 rounded-full"
                                    :style="{ backgroundColor: 'var(--brand)' }"
                                />
                                <span
                                    class="text-[10px] font-semibold text-[#4a4a57]"
                                >
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
