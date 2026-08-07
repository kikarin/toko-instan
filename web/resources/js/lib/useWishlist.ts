import { useWishlistStore } from '@/stores/useWishlistStore';
import { storeToRefs } from 'pinia';

export function useWishlist() {
    const store = useWishlistStore();
    const { items, count } = storeToRefs(store);

    return {
        items,
        count,
        isInWishlist: store.isInWishlist,
        toggleWishlist: store.toggleWishlist,
        removeItem: store.removeItem,
        clear: store.clearWishlist,
    };
}
