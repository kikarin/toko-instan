import { useIntersectionObserver } from '@vueuse/core';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import type { Ref } from 'vue';

export interface UsePaginationOptions {
    itemsPerPage?: number;
    initialMobileCount?: number;
    mobileStep?: number;
}

export function useProductPagination<T>(
    items: Ref<T[]>,
    options: UsePaginationOptions = {},
) {
    const itemsPerPage = options.itemsPerPage ?? 8;
    const initialMobileCount = options.initialMobileCount ?? 6;
    const mobileStep = options.mobileStep ?? 6;

    const isMobile = ref(false);
    const currentPage = ref(1);
    const visibleMobileCount = ref(initialMobileCount);
    const isLoadingMore = ref(false);

    function checkMobile() {
        isMobile.value = window.innerWidth < 768; // md breakpoint
    }

    onMounted(() => {
        checkMobile();
        window.addEventListener('resize', checkMobile);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', checkMobile);
    });

    // Reset pagination when items list or filter changes
    watch(
        () => items.value,
        () => {
            currentPage.value = 1;
            visibleMobileCount.value = initialMobileCount;
        },
        { deep: true },
    );

    const totalPages = computed(() =>
        Math.max(1, Math.ceil(items.value.length / itemsPerPage)),
    );

    const hasMoreMobile = computed(
        () => visibleMobileCount.value < items.value.length,
    );

    const displayedProducts = computed(() => {
        if (isMobile.value) {
            return items.value.slice(0, visibleMobileCount.value);
        } else {
            const start = (currentPage.value - 1) * itemsPerPage;

            return items.value.slice(start, start + itemsPerPage);
        }
    });

    function loadMoreMobile() {
        if (!isMobile.value || isLoadingMore.value || !hasMoreMobile.value) {
            return;
        }

        isLoadingMore.value = true;
        setTimeout(() => {
            visibleMobileCount.value += mobileStep;
            isLoadingMore.value = false;
        }, 500); // Realistic 500ms native fetch delay
    }

    function setPage(page: number) {
        if (page >= 1 && page <= totalPages.value) {
            currentPage.value = page;
            window.scrollTo({ top: 400, behavior: 'smooth' });
        }
    }

    // Target ref for mobile infinite scroll trigger
    const loadMoreTriggerRef = ref<HTMLElement | null>(null);

    useIntersectionObserver(loadMoreTriggerRef, ([entry]) => {
        if (entry?.isIntersecting && isMobile.value) {
            loadMoreMobile();
        }
    });

    return {
        isMobile,
        currentPage,
        totalPages,
        displayedProducts,
        isLoadingMore,
        hasMoreMobile,
        loadMoreTriggerRef,
        setPage,
        loadMoreMobile,
    };
}
