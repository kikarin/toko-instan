import { useStorage } from '@vueuse/core';
import { defineStore } from 'pinia';
import { computed } from 'vue';

export interface WishlistItem {
    id: number;
    name: string;
    price: string;
    priceNum?: number;
    sold: number;
    rating: number;
    store: string;
    storeSlug?: string;
    img: string;
    tag: string | null;
    cat: string;
    discount?: number;
}

export const useWishlistStore = defineStore('wishlist', () => {
    const items = useStorage<WishlistItem[]>('toko-instan:wishlist', []);

    const count = computed(() => items.value.length);

    function isInWishlist(id: number): boolean {
        return items.value.some((item) => item.id === id);
    }

    function toggleWishlist(product: WishlistItem): boolean {
        const index = items.value.findIndex((item) => item.id === product.id);
        if (index >= 0) {
            items.value.splice(index, 1);
            return false; // Removed
        } else {
            items.value.push(product);
            return true; // Added
        }
    }

    function removeItem(id: number) {
        items.value = items.value.filter((item) => item.id !== id);
    }

    function clearWishlist() {
        items.value = [];
    }

    return {
        items,
        count,
        isInWishlist,
        toggleWishlist,
        removeItem,
        clearWishlist,
    };
});
