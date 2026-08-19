import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import type { ComputedRef } from 'vue';
import { toast } from '@/components/ui/sonner';
import { useActiveUser } from '@/composables/useActiveUser';
import { useCart } from '@/composables/useCart';
import type { CartItem } from '@/types/cart';

export interface ShippingQuote {
    id: string;
    courier: string;
    service: string;
    name: string;
    cost: number;
    cost_fmt: string;
    etd: string;
    description: string;
}

export const PAYMENT_METHODS = [
    {
        id: 'qris',
        name: 'QRIS (All Bank & E-Wallet)',
        desc: 'BCA, Mandiri, GoPay, ShopeePay via Midtrans',
        icon: '📱',
    },
    {
        id: 'va',
        name: 'Virtual Account Bank',
        desc: 'BCA, Mandiri, BNI, BRI via Midtrans',
        icon: '🏦',
    },
    {
        id: 'ewallet',
        name: 'E-Wallet',
        desc: 'GoPay, ShopeePay via Midtrans',
        icon: '💳',
    },
    {
        id: 'transfer',
        name: 'Transfer Manual',
        desc: 'Transfer bank lalu upload bukti',
        icon: '📄',
    },
    {
        id: 'cod',
        name: 'Bayar di Tempat (COD)',
        desc: 'Bayar tunai ke kurir saat barang sampai',
        icon: '💵',
    },
];

export function useCheckout(cartItems: ComputedRef<CartItem[]>) {
    const { clear: clearCart } = useCart();
    const activeUser = useActiveUser();
    const page = usePage();
    const storeName = computed(() => {
        const store = page.props.store as { name?: string } | undefined;

        return store?.name ?? 'Toko';
    });

    const isEmpty = computed(() => cartItems.value.length === 0);

    // Manual input fields
    const manualName = ref(
        (activeUser.value as any)?.displayName ||
            (activeUser.value as any)?.name ||
            '',
    );
    const manualEmail = ref(activeUser.value?.email || '');
    const manualPhone = ref((activeUser.value as any)?.phone || '');
    const manualAddress = ref('');
    const manualProvince = ref('');
    const manualCity = ref('');
    const manualDistrict = ref('');
    const manualPostalCode = ref('');
    
    // Address selection state
    const selectedAddressId = ref<number | 'manual'>('manual');
    const availableAddresses = ref<any[]>([]);

    const customerName = computed(() => {
        if (selectedAddressId.value !== 'manual') {
            const addr = availableAddresses.value.find(a => a.id === selectedAddressId.value);

            return addr ? addr.recipient_name : manualName.value;
        }

        return manualName.value;
    });

    const customerEmail = computed(() => manualEmail.value); // Email is usually fixed to the user

    const customerPhone = computed(() => {
        if (selectedAddressId.value !== 'manual') {
            const addr = availableAddresses.value.find(a => a.id === selectedAddressId.value);

            return addr ? addr.phone : manualPhone.value;
        }

        return manualPhone.value;
    });

    const shippingAddress = computed(() => {
        if (selectedAddressId.value !== 'manual') {
            const addr = availableAddresses.value.find(a => a.id === selectedAddressId.value);

            if (addr) {
                const districtPart = addr.district ? `${addr.district}, ` : '';

                return `[${addr.label}] ${addr.address}, ${districtPart}${addr.city}, ${addr.province}, ${addr.postal_code}`;
            }
        }
        
        const parts = [
            manualAddress.value,
            manualDistrict.value,
            manualCity.value,
            manualProvince.value,
            manualPostalCode.value
        ].filter(Boolean);
        
        return parts.join(', ');
    });

    const notes = ref('');
    const isLoading = ref(false);
    const isLoadingRates = ref(false);

    const couriers = ref<ShippingQuote[]>([]);
    const paymentMethods = PAYMENT_METHODS;

    const selectedCourier = ref('');
    const selectedPayment = ref('qris');

    const destinationCity = computed(() => {
        if (selectedAddressId.value !== 'manual') {
            const addr = availableAddresses.value.find(a => a.id === selectedAddressId.value);

            return addr?.city ?? '';
        }

        return manualCity.value;
    });

    const destinationPostalCode = computed(() => {
        if (selectedAddressId.value !== 'manual') {
            const addr = availableAddresses.value.find(a => a.id === selectedAddressId.value);

            return addr?.postal_code ?? '';
        }

        return manualPostalCode.value;
    });

    const selectedRate = computed(() =>
        couriers.value.find((c) => String(c.id) === String(selectedCourier.value)) ?? null,
    );

    const subtotal = computed(() =>
        cartItems.value.reduce((acc, item) => acc + item.price * item.qty, 0),
    );

    const currentShippingFee = computed(() => selectedRate.value?.cost ?? 0);

    const voucherCode = ref('');
    const appliedVoucher = ref<{ code: string; name: string; discount: number } | null>(null);
    const isApplyingVoucher = ref(false);

    const discount = computed(() => appliedVoucher.value?.discount ?? 0);

    const ppnRate = computed(() => {
        const store = page.props.store as { is_pkp?: boolean; ppn_rate?: number } | undefined;

        return store?.is_pkp ? Number(store.ppn_rate ?? 11) : 0;
    });

    const tax = computed(() => {
        if (!ppnRate.value) {
            return 0;
        }

        return Math.round(Math.max(0, subtotal.value - discount.value) * ppnRate.value / 100);
    });

    const grandTotal = computed(
        () => Math.max(0, subtotal.value - discount.value) + currentShippingFee.value + tax.value,
    );

    async function loadShippingRates() {
        const city = destinationCity.value.trim();
        const storeSlug =
            (page.props.store as { slug?: string } | undefined)?.slug ||
            window.location.pathname.split('/').filter(Boolean)[0] ||
            '';

        if (!city || !storeSlug || cartItems.value.length === 0) {
            couriers.value = [];
            selectedCourier.value = '';

            return;
        }

        isLoadingRates.value = true;

        try {
            const csrf = String((page.props as { csrf_token?: string }).csrf_token ?? '');
            const xsrf = decodeURIComponent(
                document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
            );
            const res = await fetch(`/${storeSlug}/shipping/quote`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'X-XSRF-TOKEN': xsrf,
                },
                body: JSON.stringify({
                    destination_city: city,
                    destination_postal_code: String(destinationPostalCode.value || ''),
                    items: cartItems.value.map((item) => ({
                        id: Number(item.id),
                        qty: Number(item.qty) || 1,
                    })),
                }),
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                const firstError = Object.values((data as { errors?: Record<string, string[]> }).errors ?? {})[0];
                const message = Array.isArray(firstError)
                    ? String(firstError[0])
                    : ((data as { message?: string }).message ?? `Gagal memuat ongkir (${res.status}).`);
                throw new Error(message);
            }

            couriers.value = (data as { rates?: ShippingQuote[] }).rates ?? [];
            if ((data as { digital_only?: boolean }).digital_only) {
                toast.message('Keranjang produk digital — tidak perlu ongkir.');
            }
            if (!couriers.value.some((c) => String(c.id) === String(selectedCourier.value))) {
                selectedCourier.value = couriers.value[0] ? String(couriers.value[0].id) : '';
            }
        } catch (e) {
            couriers.value = [];
            toast.error(
                e instanceof Error
                    ? e.message
                    : 'Gagal memuat ongkir. Coba pilih kota lagi.',
            );
        } finally {
            isLoadingRates.value = false;
        }
    }

    let quoteTimer: ReturnType<typeof setTimeout> | null = null;

    watch(
        [manualCity, manualPostalCode, selectedAddressId, destinationCity, cartItems],
        () => {
            if (quoteTimer) {
                clearTimeout(quoteTimer);
            }

            quoteTimer = setTimeout(() => {
                loadShippingRates();
            }, 200);
        },
        { deep: true, immediate: true },
    );

    function setManualCity(value: string) {
        manualCity.value = value;
        loadShippingRates();
    }

    async function applyVoucher() {
        const code = voucherCode.value.trim();
        const storeSlug = (page.props.store as { slug?: string } | undefined)?.slug ?? '';

        if (!code || !storeSlug) {
            return;
        }

        isApplyingVoucher.value = true;

        try {
            const csrf = String((page.props as { csrf_token?: string }).csrf_token ?? '');
            const res = await fetch(`/${storeSlug}/vouchers/preview`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ code, subtotal: subtotal.value }),
            });
            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                appliedVoucher.value = null;
                throw new Error((data as { message?: string }).message ?? 'Voucher tidak valid.');
            }

            appliedVoucher.value = {
                code: String((data as { code: string }).code),
                name: String((data as { name: string }).name),
                discount: Number((data as { discount: number }).discount),
            };
            toast.success('Voucher diterapkan.');
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Voucher gagal.');
        } finally {
            isApplyingVoucher.value = false;
        }
    }

    function clearVoucher() {
        appliedVoucher.value = null;
        voucherCode.value = '';
    }

    function fmtRp(val: number) {
        return 'Rp ' + val.toLocaleString('id-ID');
    }

    function handleCheckoutSubmit() {
        if (isEmpty.value) {
            toast.error('Keranjang belanja Anda kosong!');

            return;
        }

        if (
            !customerName.value ||
            !customerEmail.value ||
            !customerPhone.value ||
            !shippingAddress.value
        ) {
            toast.error(
                'Lengkapi nama, email, nomor HP, dan alamat pengiriman Anda.',
            );

            return;
        }

        if (couriers.value.length > 0 && !selectedRate.value) {
            toast.error('Pilih kurir pengiriman terlebih dahulu.');

            return;
        }

        isLoading.value = true;

        const storeSlug = (page.props.store as any)?.slug ?? '';

        router.post(
            `/${storeSlug}/checkout`,
            {
                customer_name: customerName.value,
                customer_email: customerEmail.value,
                customer_phone: customerPhone.value,
                shipping_address: shippingAddress.value,
                shipping_courier: selectedRate.value?.name ?? selectedCourier.value,
                shipping_rate_id: selectedRate.value?.id,
                shipping_service: selectedRate.value?.service,
                shipping_cost: selectedRate.value?.cost ?? 0,
                destination_city: destinationCity.value,
                destination_postal_code: destinationPostalCode.value,
                payment_method: selectedPayment.value,
                items: cartItems.value.map((item) => ({
                    id: item.id,
                    qty: item.qty,
                })),
                notes: notes.value,
                voucher_code: appliedVoucher.value?.code ?? '',
            },
            {
                onSuccess: () => {
                    clearCart();
                    toast.success(`Pesanan ${storeName.value} berhasil dibuat!`);
                },
                onFinish: () => {
                    isLoading.value = false;
                },
                onError: (errors) => {
                    const values = Object.values(errors);
                    const first = values
                        .flatMap((value) => (Array.isArray(value) ? value : [value]))
                        .find((value) => typeof value === 'string' && value.length > 0);

                    toast.error(
                        typeof first === 'string'
                            ? first
                            : 'Gagal membuat pesanan. Silakan periksa kembali data Anda.',
                    );
                },
            },
        );
    }

    return {
        isEmpty,
        manualName,
        manualEmail,
        manualPhone,
        manualAddress,
        manualProvince,
        manualCity,
        manualDistrict,
        manualPostalCode,
        customerName,
        customerEmail,
        customerPhone,
        shippingAddress,
        selectedAddressId,
        availableAddresses,
        selectedCourier,
        selectedPayment,
        notes,
        isLoading,
        isLoadingRates,
        couriers,
        paymentMethods,
        subtotal,
        currentShippingFee,
        discount,
        tax,
        ppnRate,
        voucherCode,
        appliedVoucher,
        isApplyingVoucher,
        applyVoucher,
        clearVoucher,
        grandTotal,
        fmtRp,
        handleCheckoutSubmit,
        loadShippingRates,
        setManualCity,
    };
}