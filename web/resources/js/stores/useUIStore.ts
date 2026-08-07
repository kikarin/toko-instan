import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { ProductDetail } from '@/components/marketplace/ProductDetailModal.vue';

export const useUIStore = defineStore('ui', () => {
    const isCartOpen = ref(false);
    const activeProductModal = ref<ProductDetail | null>(null);
    const searchQuery = ref('');

    function openCart() {
        isCartOpen.value = true;
    }

    function closeCart() {
        isCartOpen.value = false;
    }

    function toggleCart() {
        isCartOpen.value = !isCartOpen.value;
    }

    function openProductModal(product: ProductDetail) {
        activeProductModal.value = product;
    }

    function closeProductModal() {
        activeProductModal.value = null;
    }

    function setSearchQuery(q: string) {
        searchQuery.value = q;
    }

    return {
        isCartOpen,
        activeProductModal,
        searchQuery,
        openCart,
        closeCart,
        toggleCart,
        openProductModal,
        closeProductModal,
        setSearchQuery,
    };
});
