import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { Product } from '@/types/product';

interface ProductFormProps {
    product?: Product | null;
    categories?: string[];
    labels?: string[];
    brands?: string[];
}

export function useProductForm(props: ProductFormProps) {
    const isEdit = ref(!!props.product);
    const name = ref(props.product?.name ?? '');

    const categoryOptions = computed(() => {
        if (props.categories && props.categories.length > 0) {
            return props.categories;
        }

        return ['Sneakers', 'Apparel', 'Accessories', 'Sportswear', 'Running'];
    });

    const labelOptions = computed(() => {
        if (props.labels && props.labels.length > 0) {
            return props.labels;
        }

        return [
            'BESTSELLER',
            'NEW ARRIVAL',
            'PROMO 8.8',
            'GARANSI RESMI',
            'LIMITED EDITION',
        ];
    });

    const brandOptions = computed(() => {
        if (props.brands && props.brands.length > 0) {
            return props.brands;
        }

        return ['Nike', 'Jordan', 'Adidas', 'Puma', 'Converse'];
    });

    // Single category from catalog dropdown
    const selectedCategory = ref<string>(
        props.product?.category
            ? props.product.category.split(',')[0].trim()
            : (categoryOptions.value[0] ?? 'Sneakers'),
    );

    const selectedTag = ref<string>(
        props.product?.tag ? props.product.tag.split(',')[0].trim() : '',
    );

    const selectedBrand = ref<string>(
        props.product?.brand
            ? props.product.brand
            : (brandOptions.value[0] ?? 'Nike'),
    );

    const price = ref(props.product ? String(props.product.price) : '');
    const stock = ref(props.product ? String(props.product.stock) : '10');
    const isActive = ref(props.product?.is_active ?? true);
    const img = ref(props.product?.img ?? '');
    const description = ref(
        props.product?.description ??
            'Produk original berkualitas tinggi dengan jaminan garansi keaslian 100%, material daya tahan maksimal, dan kenyamanan optimal.',
    );
    const sku = ref(props.product?.sku ?? '');
    const weightGram = ref(
        props.product?.weight_gram ? String(props.product.weight_gram) : '500',
    );
    const isLoading = ref(false);
    const uploading = ref(false);
    const errors = ref<Record<string, string>>({});
    const fileInput = ref<HTMLInputElement | null>(null);

    // Formatted Price Computed for Live Preview
    const formattedPricePreview = computed(() => {
        const num = Number(price.value);

        if (!num || Number.isNaN(num)) {
            return 'Rp 0';
        }

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(num);
    });

    async function uploadImage(e: Event) {
        const target = e.target as HTMLInputElement;
        const file = target.files?.[0];

        if (!file) {
            return;
        }

        uploading.value = true;

        try {
            const formData = new FormData();
            formData.append('file', file);

            const xsrfToken = document.cookie
                .split('; ')
                .find((row) => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1];

            const response = await fetch('/uploads', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': xsrfToken
                        ? decodeURIComponent(xsrfToken)
                        : '',
                },
                body: formData,
            });

            const result = await response.json();

            if (!response.ok || !result.url) {
                throw new Error(result.message ?? 'Upload gambar gagal.');
            }

            img.value = result.url;
            toast.success('Gambar produk berhasil diunggah!');
        } catch {
            toast.error('Gagal mengunggah gambar produk.');
        } finally {
            uploading.value = false;
            target.value = '';
        }
    }

    function back() {
        router.visit('/products');
    }

    function submit() {
        if (!selectedCategory.value) {
            toast.error('Pilih kategori produk terlebih dahulu.');

            return;
        }

        const payload = {
            name: name.value,
            category: selectedCategory.value,
            price: price.value,
            stock: stock.value,
            is_active: isActive.value,
            tag:
                selectedTag.value && selectedTag.value !== 'none'
                    ? selectedTag.value
                    : null,
            img: img.value,
            description: description.value,
            sku: sku.value,
            brand: selectedBrand.value || null,
            weight_gram: weightGram.value,
        };

        const options = {
            onStart: () => {
                isLoading.value = true;
                errors.value = {};
            },
            onSuccess: () => {
                toast.success(
                    isEdit.value
                        ? 'Produk berhasil diperbarui!'
                        : 'Produk baru berhasil ditambahkan!',
                );
            },
            onError: (errs: Record<string, string>) => {
                errors.value = errs;
                toast.error('Periksa kembali data produk yang diisi.');
            },
            onFinish: () => {
                isLoading.value = false;
            },
        };

        if (isEdit.value && props.product) {
            router.put(`/products/${props.product.id}`, payload, options);
        } else {
            router.post('/products', payload, options);
        }
    }

    return {
        isEdit,
        name,
        categoryOptions,
        labelOptions,
        brandOptions,
        selectedCategory,
        selectedTag,
        selectedBrand,
        price,
        stock,
        isActive,
        img,
        description,
        sku,
        weightGram,
        isLoading,
        uploading,
        errors,
        fileInput,
        formattedPricePreview,
        uploadImage,
        back,
        submit,
    };
}