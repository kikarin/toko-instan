import { router, usePage } from '@inertiajs/vue3';
import { computed, ref  } from 'vue';
import type {ComputedRef} from 'vue';
import { toast } from '@/components/ui/sonner';
import { useActiveUser } from '@/composables/useActiveUser';
import { useCart } from '@/composables/useCart';
import type { CartItem } from '@/types/cart';

export const COURIERS = [
    {
        id: 'jne',
        name: 'JNE Reguler',
        price: 15000,
        priceFmt: 'Rp 15.000',
        est: '2-3 Hari',
    },
    {
        id: 'jnt',
        name: 'J&T Express',
        price: 18000,
        priceFmt: 'Rp 18.000',
        est: '1-2 Hari',
    },
    {
        id: 'sicepat',
        name: 'SiCepat BEST',
        price: 22000,
        priceFmt: 'Rp 22.000',
        est: 'Besok Sampai',
    },
];

export const PAYMENT_METHODS = [
    {
        id: 'qris',
        name: 'QRIS (All Bank & E-Wallet)',
        desc: 'BCA, Mandiri, GoPay, ShopeePay',
        icon: '📱',
    },
    {
        id: 'va',
        name: 'Virtual Account Bank',
        desc: 'BCA, Mandiri, BNI, BRI Auto Detect',
        icon: '🏦',
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

    const couriers = COURIERS;
    const paymentMethods = PAYMENT_METHODS;

    const selectedCourier = ref(COURIERS[0]?.name ?? '');
    const selectedPayment = ref('qris');

    const subtotal = computed(() =>
        cartItems.value.reduce((acc, item) => acc + item.price * item.qty, 0),
    );

    const currentShippingFee = computed(() => {
        const found = couriers.find((c) =>
            selectedCourier.value.includes(c.name),
        );

        return found ? found.price : (COURIERS[0]?.price ?? 0);
    });

    const grandTotal = computed(() => subtotal.value + currentShippingFee.value);

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

        isLoading.value = true;

        const storeSlug = (page.props.store as any)?.slug ?? '';

        router.post(
            `/${storeSlug}/checkout`,
            {
                customer_name: customerName.value,
                customer_email: customerEmail.value,
                customer_phone: customerPhone.value,
                shipping_address: shippingAddress.value,
                shipping_courier: selectedCourier.value,
                payment_method: selectedPayment.value,
                items: cartItems.value.map((item) => ({
                    id: item.id,
                    qty: item.qty,
                })),
                notes: notes.value,
            },
            {
                onSuccess: () => {
                    clearCart();
                    toast.success(`Pesanan ${storeName.value} berhasil dibuat!`);
                },
                onFinish: () => {
                    isLoading.value = false;
                },
                onError: () => {
                    toast.error(
                        'Gagal membuat pesanan. Silakan periksa kembali data Anda.',
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
        couriers,
        paymentMethods,
        subtotal,
        currentShippingFee,
        grandTotal,
        fmtRp,
        handleCheckoutSubmit,
    };
}