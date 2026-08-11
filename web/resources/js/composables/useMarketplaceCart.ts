import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import { useCart } from '@/composables/useCart';
import { useActiveUser } from '@/composables/useActiveUser';
import type { ProductDetail } from '@/types/product';

export function useMarketplaceCart() {
    const activeUser = useActiveUser();
    const {
        items: cartItems,
        totalCount: totalCartCount,
        addItem,
        updateQty,
        removeItem,
        isOpen: isCartOpen,
        openCart,
    } = useCart();

    function addToCart(product: ProductDetail, addQty = 1) {
        if (!activeUser.value) {
            toast.error('Silakan login untuk menambahkan ke keranjang');
            const store = usePage().props.store;
            router.visit(store?.slug ? `/${store.slug}/login` : '/login');
            return;
        }

        const rawPrice =
            product.priceNum ||
            parseInt(product.price.replace(/[^\d]/g, ''), 10) ||
            100000;

        addItem(
            {
                id: product.id,
                name: product.name,
                price: rawPrice,
                formattedPrice: product.price,
                img: product.img,
                store: product.store,
                qty: addQty,
            },
            addQty,
        );

        openCart();
        toast.success(`${product.name} ditambahkan ke keranjang!`);
    }

    function updateCartQty(id: number, delta: number) {
        updateQty(id, delta);
    }

    function removeFromCart(id: number) {
        removeItem(id);
    }

    function goCheckout() {
        isCartOpen.value = false;
        const page = usePage();
        const storeSlug = page.props.store?.slug ?? '';
        router.visit(`/${storeSlug}/checkout`);
    }

    return {
        cartItems,
        totalCartCount,
        isCartOpen,
        openCart,
        addToCart,
        updateCartQty,
        removeFromCart,
        goCheckout,
    };
}
