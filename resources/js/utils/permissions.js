export function hasAnyPermission(user, requiredPermissions = []) {
    if (! requiredPermissions.length) {
        return true;
    }

    const permissionCodes = new Set(
        (user?.permissions ?? []).map((permission) => permission.code)
    );

    return requiredPermissions.some((permissionCode) => permissionCodes.has(permissionCode));
}