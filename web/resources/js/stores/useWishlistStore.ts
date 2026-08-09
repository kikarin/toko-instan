import { router } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import { defineStore } from 'pinia';
import { computed } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { WishlistItem } from '@/types/product';

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
        const storeSlug = (router.page.props.store as any)?.slug ?? '';
        const url = storeSlug ? `/${storeSlug}/wishlist/${id}` : `/wishlist/${id}`;
        
        fetch(url, {
            method: added ? 'POST' : 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie
                        .split('; ')
                        .find((row) => row.startsWith('XSRF-TOKEN='))
                        ?.substring('XSRF-TOKEN='.length) ?? '',
                ),
            },
        }).then((res) => {
            if (res.status === 401) {
                // Not authenticated
                toast.error('Silakan login terlebih dahulu.');
                // Revert local state
                if (added) {
                    items.value = items.value.filter(i => i.id !== id);
                }
            } else if (res.status === 403) {
                toast.error('Akses ditolak.');
                if (added) items.value = items.value.filter(i => i.id !== id);
            } else if (!res.ok) {
                toast.error('Gagal menyimpan wishlist.');
                if (added) items.value = items.value.filter(i => i.id !== id);
            }
        }).catch((err) => {
            console.error('Wishlist sync error:', err);
            toast.error('Gagal terhubung ke server.');
            if (added) items.value = items.value.filter(i => i.id !== id);
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
