import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { Product } from '@/types/product';

export interface VariantOption {
    name: string;
    values: string[];
    inputValue?: string;
}

export interface VariantData {
    id: number | null;
    name: string;
    price: string;
    stock: string;
    sku: string;
    img: string;
}

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
    const productType = ref<'physical' | 'digital'>(
        props.product?.type === 'digital' ? 'digital' : 'physical',
    );
    const digitalFilePath = ref(props.product?.digital_file_path ?? '');
    const digitalFileName = ref(props.product?.digital_file_name ?? '');
    const digitalFileMime = ref(props.product?.digital_file_mime ?? '');
    const isLoading = ref(false);
    const uploading = ref(false);
    const uploadingDigital = ref(false);
    const errors = ref<Record<string, string>>({});
    const fileInput = ref<HTMLInputElement | null>(null);
    const digitalFileInput = ref<HTMLInputElement | null>(null);

    const variantOptions = ref<VariantOption[]>(
        props.product?.variant_options?.map(opt => ({
            ...opt,
            inputValue: ''
        })) ?? []
    );

    const variants = ref<VariantData[]>(
        props.product?.variants?.map((v: any) => ({
            id: v.id,
            name: v.name,
            price: v.price ? String(v.price) : '',
            stock: String(v.stock ?? 0),
            sku: v.sku ?? '',
            img: v.img ?? '',
        })) ?? []
    );

    // Watch variant options to regenerate combinations if they change
    // But we only want to generate when options are modified explicitly,
    // so we'll provide a function to do that in the UI.
    function generateVariants() {
        if (variantOptions.value.length === 0) {
            variants.value = [];

            return;
        }

        // Sync values array from rawValues string before generating combinations
        // Not needed anymore since values is directly modified as array.

        const combinations = cartesianProduct(
            variantOptions.value.map((opt) => opt.values)
        );

        const newVariants: VariantData[] = [];

        combinations.forEach((combo) => {
            if (combo.length === 0) {
return;
}

            const name = combo.join(' - ');
            const existing = variants.value.find((v) => v.name === name);

            if (existing) {
                newVariants.push(existing);
            } else {
                newVariants.push({
                    id: null,
                    name,
                    price: '',
                    stock: '0',
                    sku: '',
                    img: '',
                });
            }
        });

        variants.value = newVariants;
    }

    function cartesianProduct(arrays: string[][]): string[][] {
        return arrays.reduce<string[][]>(
            (a, b) => {
                if (b.length === 0) {
return a;
}

                return a.flatMap((d) => b.map((e) => [...d, e]));
            },
            [[]]
        );
    }

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

    function xsrfHeaders(): HeadersInit {
        const xsrfToken = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        return {
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken ? decodeURIComponent(xsrfToken) : '',
        };
    }

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

            const response = await fetch('/uploads', {
                method: 'POST',
                headers: xsrfHeaders(),
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

    async function uploadDigitalFile(e: Event) {
        const target = e.target as HTMLInputElement;
        const file = target.files?.[0];

        if (!file) {
            return;
        }

        uploadingDigital.value = true;

        try {
            const formData = new FormData();
            formData.append('file', file);

            const response = await fetch('/uploads/digital', {
                method: 'POST',
                headers: xsrfHeaders(),
                body: formData,
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok || !result.path) {
                const validationMsg =
                    result?.errors?.file?.[0] ??
                    result?.message ??
                    'Upload file digital gagal.';

                throw new Error(validationMsg);
            }

            digitalFilePath.value = result.path;
            digitalFileName.value = result.name;
            digitalFileMime.value = result.mime;
            toast.success('File digital berhasil diunggah!');
        } catch (err) {
            toast.error(
                err instanceof Error
                    ? err.message
                    : 'Gagal mengunggah file digital.',
            );
        } finally {
            uploadingDigital.value = false;
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
            type: productType.value,
            digital_file_path:
                productType.value === 'digital' ? digitalFilePath.value : null,
            digital_file_name:
                productType.value === 'digital' ? digitalFileName.value : null,
            digital_file_mime:
                productType.value === 'digital' ? digitalFileMime.value : null,
            variant_options: variantOptions.value,
            variants: variants.value,
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
        productType,
        digitalFilePath,
        digitalFileName,
        digitalFileMime,
        isLoading,
        uploading,
        uploadingDigital,
        errors,
        fileInput,
        digitalFileInput,
        formattedPricePreview,
        uploadImage,
        uploadDigitalFile,
        back,
        submit,
        variantOptions,
        variants,
        generateVariants,
    };
}