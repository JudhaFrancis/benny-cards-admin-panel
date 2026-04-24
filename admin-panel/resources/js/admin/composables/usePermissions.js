import { computed } from "vue";
import { useAuth } from "./useAuth";

export function usePermissions() {
    const { user } = useAuth();

    const permissions = computed(() => {
        return user.value?.permission_names || user.value?.permissions || [];
    });
    const userRole = computed(() => user.value?.role?.name?.toLowerCase() || user.value?.role?.toLowerCase() || "");
    const isSuperAdmin = computed(() => userRole.value === "super-admin");

    const hasPermission = (module, action = 'list') => {
        if (isSuperAdmin.value) return true;
        const permissionName = `${module.toLowerCase()}-${action.toLowerCase()}`;
        return permissions.value.includes(permissionName);
    };

    const getModulePermissions = (module) => {
        return {
            canView: computed(() => hasPermission(module, 'view')),
            canAdd: computed(() => hasPermission(module, 'create')),
            canEdit: computed(() => hasPermission(module, 'edit')),
            canDelete: computed(() => hasPermission(module, 'delete')),
        };
    };

    return {
        isSuperAdmin,
        permissions,
        hasPermission,
        getModulePermissions,
    };
}
