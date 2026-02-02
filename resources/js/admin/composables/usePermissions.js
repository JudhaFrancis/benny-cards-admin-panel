import { computed } from "vue";
import { useAuth } from "./useAuth";

export function usePermissions() {
    const { user } = useAuth();

    const isModerator = computed(() => user.value?.role === "moderator");
    const isAdmin = computed(() => user.value?.role === "admin");
    const isStaff = computed(() => user.value?.role === "staff");

    const canEdit = computed(() => !isModerator.value);
    const canDelete = computed(() => !isModerator.value);
    const canAdd = computed(() => !isModerator.value);

    // Allow View for everyone (including moderators)
    const canView = computed(() => true);

    return {
        isModerator,
        isAdmin,
        isStaff,
        canEdit,
        canDelete,
        canAdd,
        canView,
    };
}
