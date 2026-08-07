import { useCartStore } from '@/stores/useCartStore';
import { storeToRefs } from 'pinia';

export function useCart() {
    const store = useCartStore();
    const { items, totalCount, totalAmount, formattedTotalAmount } =
        storeToRefs(store);

    return {
        items,
        totalCount,
        totalAmount,
        formattedTotalAmount,
        addItem: store.addItem,
        updateQty: store.updateQty,
        removeItem: store.removeItem,
        clear: store.clearCart,
    };
}
