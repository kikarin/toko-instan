import { router, usePage } from '@inertiajs/vue3';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import { toast } from '@/components/ui/sonner';

export interface SellerNotice {
    id: string;
    title: string;
    body: string;
    url?: string | null;
    read_at?: string | null;
    created_at?: string;
}

export function useSellerNotifications() {
    const page = usePage();
    const open = ref(false);
    const unread = ref(Number((page.props.notifications as { unread?: number } | null)?.unread ?? 0));
    const items = ref<SellerNotice[]>([]);
    let echo: Echo<'reverb'> | null = null;
    let poll: ReturnType<typeof setInterval> | null = null;

    async function refresh() {
        try {
            const res = await fetch('/notifications', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
            if (!res.ok) {
                return;
            }
            const data = await res.json();
            unread.value = Number(data.unread ?? 0);
            items.value = data.notifications ?? [];
        } catch {
            // ignore
        }
    }

    async function markRead() {
        await fetch('/notifications/read', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': String((page.props as { csrf_token?: string }).csrf_token ?? ''),
            },
        });
        unread.value = 0;
        items.value = items.value.map((n) => ({ ...n, read_at: n.read_at ?? 'now' }));
    }

    function close() {
        open.value = false;
    }

    function toggle() {
        open.value = !open.value;
        if (open.value) {
            refresh().then(() => markRead());
        }
    }

    function visit(url?: string | null) {
        close();
        if (url) {
            router.visit(url);
        }
    }

    function onKeydown(e: KeyboardEvent) {
        if (e.key === 'Escape') {
            close();
        }
    }

    onMounted(() => {
        const user = (page.props.auth as { user?: { id?: number; role?: string } } | undefined)?.user;
        if (user?.role !== 'seller' || !user.id) {
            return;
        }

        document.addEventListener('keydown', onKeydown);
        refresh();

        const key = import.meta.env.VITE_REVERB_APP_KEY as string | undefined;
        const host = import.meta.env.VITE_REVERB_HOST as string | undefined;

        if (key && host) {
            window.Pusher = Pusher;
            echo = new Echo({
                broadcaster: 'reverb',
                key,
                wsHost: host,
                wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
                wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
            });

            echo.private(`seller.${user.id}`).listen('.seller.notified', (payload: { title?: string; body?: string }) => {
                unread.value += 1;
                toast.message(payload.title ?? 'Notifikasi', { description: payload.body });
                refresh();
            });
        } else {
            poll = setInterval(refresh, 20000);
        }
    });

    onUnmounted(() => {
        document.removeEventListener('keydown', onKeydown);
        echo?.disconnect();
        if (poll) {
            clearInterval(poll);
        }
    });

    return reactive({ open, unread, items, toggle, close, visit });
}

declare global {
    interface Window {
        Pusher: typeof Pusher;
    }
}
