import { useStorage } from '@vueuse/core';
import { computed } from 'vue';
import type { CartItem } from '@/components/marketplace/CartDrawer.vue';

interface CartOptions {
    storageKey?: string;
}

export function useCart(options: CartOptions = {}) {
    const storageKey = options.storageKey ?? 'toko-instan:cart';

    const items = useStorage<CartItem[]>(storageKey, []);

    const totalCount = computed(() =>
        items.value.reduce((acc, item) => acc + item.qty, 0),
    );

    function addItem(item: CartItem, addQty = 1) {
        const existing = items.value.find((i) => i.id === item.id);

        if (existing) {
            existing.qty += addQty;
        } else {
            items.value.push({ ...item, qty: addQty });
        }
    }

    function updateQty(id: number, delta: number) {
        const item = items.value.find((i) => i.id === id);

        if (!item) {
            return;
        }

        item.qty += delta;

        if (item.qty <= 0) {
            removeItem(id);
        }
    }

    function removeItem(id: number) {
        items.value = items.value.filter((i) => i.id !== id);
    }

    function clear() {
        items.value = [];
    }

    return { items, totalCount, addItem, updateQty, removeItem, clear };
}
