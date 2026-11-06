import { ref, computed } from "vue";

const user = ref(JSON.parse(localStorage.getItem("auth_user") || "null"));

export function useAuth() {
    const setUser = (userData) => {
        // Profile updates return the user without branch access: keep the last known one
        if (userData && userData.branches === undefined && user.value?.id === userData.id) {
            userData = { ...userData, branches: user.value.branches, all_branches: user.value.all_branches };
        }
        user.value = userData;
        if (userData) {
            localStorage.setItem("auth_user", JSON.stringify(userData));
        } else {
            localStorage.removeItem("auth_user");
        }
    };

    const logout = () => {
        user.value = null;
        localStorage.removeItem("auth_token");
        localStorage.removeItem("auth_user");
        localStorage.removeItem("branch_scope");
        window.location.href = "/login";
    };

    return {
        user: computed(() => user.value),
        setUser,
        logout,
    };
}
