import { onMounted, onUnmounted, ref } from 'vue';

const MOBILE_BREAKPOINT = 768; // md breakpoint

export function useIsMobile(breakpoint = MOBILE_BREAKPOINT) {
    const isMobile = ref(false);

    function checkMobile() {
        isMobile.value = window.innerWidth < breakpoint;
    }

    onMounted(() => {
        checkMobile();
        window.addEventListener('resize', checkMobile);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', checkMobile);
    });

    return { isMobile };
}
