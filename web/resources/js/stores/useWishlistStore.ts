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

    function replaceItems(products: WishlistItem[]) {
        items.value = products;
    }

    function syncToBackend(id: number, added: boolean) {
        fetch(`/wishlist/${id}`, {
            method: added ? 'POST' : 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie
                        .split('; ')
                        .find((row) => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1] ?? '',
                ),
            },
        }).catch(() => {
            // Sync best-effort; lokal tetap berjalan untuk tamu.
        });
    }

    function toggleWishlist(product: WishlistItem): boolean {
        const index = items.value.findIndex((item) => item.id === product.id);

        if (index >= 0) {
            items.value.splice(index, 1);
            syncToBackend(product.id, false);

            return false; // Removed
        } else {
            items.value.push(product);
            syncToBackend(product.id, true);

            return true; // Added
        }
    }

    function removeItem(id: number) {
        items.value = items.value.filter((item) => item.id !== id);
        syncToBackend(id, false);
    }

    function clearWishlist() {
        items.value.forEach((item) => syncToBackend(item.id, false));
        items.value = [];
    }

    return {
        items,
        count,
        isInWishlist,
        replaceItems,
        toggleWishlist,
        removeItem,
        clearWishlist,
    };
});
