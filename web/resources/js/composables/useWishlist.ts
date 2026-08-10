import { storeToRefs } from 'pinia';
import { useWishlistStore } from '@/stores/useWishlistStore';

export function useWishlist() {
    const store = useWishlistStore();
    const { items, count } = storeToRefs(store);

    return {
        items,
        count,
        isInWishlist: store.isInWishlist,
        replaceItems: store.replaceItems,
        toggleWishlist: store.toggleWishlist,
        removeItem: store.removeItem,
        clear: store.clearWishlist,
    };
}
