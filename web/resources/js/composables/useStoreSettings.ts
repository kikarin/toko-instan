import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { StoreData } from '@/types/store';

export function useStoreSettings(store: StoreData) {
    const activeTab = ref<'profil' | 'banner' | 'kontak' | 'pajak'>('profil');

    const initialForm = {
        name: store?.name || '',
        slug: store?.slug || '',
        category: store?.category || '',
        description: store?.description || '',
        logo: store?.logo || '',
        avatar_hue: store?.avatar_hue || 220,
        banner_url: store?.banner_url || '',
        banner_urls: store?.banner_urls || [],
        existing_banners: store?.banner_urls || [],
        highlights: store?.highlights || [],
        hero_config: store?.hero_config || {
            about_text: '',
            widget_title: '',
            widget_subtitle: '',
            widget_description: '',
            fake_buyer_count: '',
        },
        phone: store?.phone || '',
        email: store?.email || '',
        address: store?.address || '',
        instagram: store?.instagram || '',
        tiktok: store?.tiktok || '',
        headline: store?.headline || '',
        is_active: store?.is_active ?? true,
        npwp: store?.npwp || '',
        nik: store?.nik || '',
        is_pkp: store?.is_pkp ?? true,
        tax_name: store?.tax_name || '',
        tax_address: store?.tax_address || '',
    };

    const form = useForm(initialForm);
    const formWithFiles = form as typeof form & {
        banner_file?: File | null;
        banner_files?: File[];
        logo_file?: File | null;
    };

    const bannerPreviewUrls = ref<string[]>([...(store?.banner_urls || [])]);

    const bannerPreviewUrl = ref<string | null>(null);
    const logoPreviewUrl = ref<string | null>(null);

    function handleBannerFileChange(e: Event) {
        const target = e.target as HTMLInputElement;

        if (target.files && target.files.length > 0) {
            if (!formWithFiles.banner_files) {
                formWithFiles.banner_files = [];
            }
            
            for (let i = 0; i < target.files.length; i++) {
                const file = target.files[i];
                formWithFiles.banner_files.push(file);
                const url = URL.createObjectURL(file);
                bannerPreviewUrls.value.push(url);
            }
        }
        
        // Reset input value to allow selecting the same file again
        target.value = '';
    }

    const newBannerUrl = ref('');

    function addBannerUrl() {
        if (!newBannerUrl.value) {
return;
}
        
        // Push to existing banners array so it gets submitted
        form.existing_banners.push(newBannerUrl.value);
        bannerPreviewUrls.value.push(newBannerUrl.value);
        
        newBannerUrl.value = ''; // Reset input
    }

    function removeBanner(index: number) {
        // If it's an existing banner (URL from DB), remove it from existing_banners
        if (index < form.existing_banners.length) {
            form.existing_banners.splice(index, 1);
            bannerPreviewUrls.value.splice(index, 1);
        } else {
            // It's a newly uploaded file
            const newFileIndex = index - form.existing_banners.length;

            if (formWithFiles.banner_files) {
                formWithFiles.banner_files.splice(newFileIndex, 1);
            }

            bannerPreviewUrls.value.splice(index, 1);
        }
    }

    const newHighlight = ref('');
    
    function addHighlight() {
        if (!newHighlight.value.trim()) {
return;
}
        
        if (form.highlights.length >= 3) {
            toast.error('Maksimal 3 highlights');

            return;
        }

        form.highlights.push(newHighlight.value.trim());
        newHighlight.value = '';
    }

    function removeHighlight(index: number) {
        form.highlights.splice(index, 1);
    }

    function handleLogoFileChange(e: Event) {
        const target = e.target as HTMLInputElement;

        if (target.files && target.files[0]) {
            const file = target.files[0];
            formWithFiles.logo_file = file;
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
                banner_file: formWithFiles.banner_file,
                banner_files: formWithFiles.banner_files,
                logo_file: formWithFiles.logo_file,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success(
                        'Pengaturan & Branding Toko berhasil disimpan!',
                    );
                },
                onError: () => {
                    toast.error(
                        'Gagal menyimpan pengaturan toko. Periksa kembali form.',
                    );
                },
            },
        );
    }

    return {
        activeTab,
        form,
        bannerPreviewUrl,
        logoPreviewUrl,
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
    };
}