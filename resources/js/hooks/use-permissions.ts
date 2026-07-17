import { usePage } from '@inertiajs/react';

/**
 * Owners bypass every permission check on the backend (see `Gate::before`
 * in AppServiceProvider), but `permissionNames` only reflects assigned
 * Spatie permissions. Mirror the bypass here so owners without an explicit
 * role still see permission-gated UI.
 */
export function usePermissions() {
    const { auth, permissionNames } = usePage().props;

    const can = (permission: string) =>
        auth.user.is_owner || permissionNames.includes(permission);

    return { isOwner: auth.user.is_owner, can };
}
