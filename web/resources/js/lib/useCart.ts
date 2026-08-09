import { storeToRefs } from 'pinia';
import { useCartStore } from '@/stores/useCartStore';

export function useCart() {
    const store = useCartStore();
    const { items, isOpen, totalCount, totalAmount, formattedTotalAmount } =
        storeToRefs(store);

    return {
        items,
        isOpen,
        totalCount,
        totalAmount,
        formattedTotalAmount,
        addItem: store.addItem,
        updateQty: store.updateQty,
        removeItem: store.removeItem,
        clear: store.clearCart,
        openCart: store.openCart,
        closeCart: store.closeCart,
    };
}
