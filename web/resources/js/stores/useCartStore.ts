import { useStorage } from '@vueuse/core';
import { defineStore } from 'pinia';
import { computed } from 'vue';
import type { CartItem } from '@/components/marketplace/CartDrawer.vue';

export const useCartStore = defineStore('cart', () => {
    const items = useStorage<CartItem[]>('toko-instan:cart', []);

    const totalCount = computed(() =>
        items.value.reduce((acc, item) => acc + item.qty, 0),
    );

    const totalAmount = computed(() =>
        items.value.reduce((acc, item) => acc + item.price * item.qty, 0),
    );

    const formattedTotalAmount = computed(
        () => 'Rp ' + totalAmount.value.toLocaleString('id-ID'),
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

    function clearCart() {
        items.value = [];
    }

    return {
        items,
        totalCount,
        totalAmount,
        formattedTotalAmount,
        addItem,
        updateQty,
        removeItem,
        clearCart,
    };
});
