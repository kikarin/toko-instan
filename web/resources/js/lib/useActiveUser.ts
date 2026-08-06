import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { currentUser } from '@/lib/firebase';

export interface ActiveUser {
    name: string | null;
    email: string | null;
    displayName: string | null;
    photoURL: string | null;
}

export function useActiveUser(): ComputedRef<ActiveUser | null> {
    const page = usePage();

    return computed(() => {
        const laravelUser = page.props.auth?.user;
        const fireUser = currentUser.value;

        if (!laravelUser && !fireUser) {
            return null;
        }

        return {
            name: laravelUser?.name ?? null,
            email: laravelUser?.email ?? fireUser?.email ?? null,
            displayName:
                fireUser?.displayName ??
                laravelUser?.name ??
                laravelUser?.email ??
                null,
            photoURL: fireUser?.photoURL ?? laravelUser?.avatar ?? null,
        };
    });
}
