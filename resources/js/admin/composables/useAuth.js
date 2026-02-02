import { ref, computed } from "vue";

const user = ref(JSON.parse(localStorage.getItem("auth_user") || "null"));

export function useAuth() {
    const setUser = (userData) => {
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
        window.location.href = "/login";
    };

    return {
        user: computed(() => user.value),
        setUser,
        logout,
    };
}
