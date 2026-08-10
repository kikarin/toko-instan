import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { currentUser } from '@/lib/firebase';
import type { ActiveUser } from '@/types';

export function useActiveUser(): ComputedRef<ActiveUser | null> {
    const page = usePage();

    return computed(() => {
        const laravelUser = page.props.auth?.user;
        const fireUser = currentUser.value;

        if (!laravelUser && !fireUser) {
            return null;
        }

        const impersonating = page.props.auth?.impersonating;

        return {
            name: laravelUser?.name ?? null,
            email: laravelUser?.email ?? fireUser?.email ?? null,
            displayName:
                impersonating
                    ? laravelUser?.name ?? laravelUser?.email ?? null
                    : fireUser?.displayName ??
                      laravelUser?.name ??
                      laravelUser?.email ??
                      null,
            photoURL:
                impersonating
                    ? laravelUser?.avatar ?? null
                    : fireUser?.photoURL ?? laravelUser?.avatar ?? null,
        };
    });
}
