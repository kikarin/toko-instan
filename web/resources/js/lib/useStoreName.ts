import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Reads the active store (shared via Inertia props) so every page
 * shows the store's real name instead of a hardcoded brand.
 */
export function useStoreName() {
    const page = usePage();

    const storeName = computed(() => {
        const store = page.props.store as { name?: string } | undefined;

        return store?.name ?? 'Toko Resmi';
    });

    return {
        storeName,
    };
}