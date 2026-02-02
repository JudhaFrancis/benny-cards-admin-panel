import { ref } from "vue";

const toasts = ref([]);

export function useToast() {
    const addToast = (message, type = "success", duration = 3000) => {
        const id = Date.now();
        toasts.value.push({ id, message, type });

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }
    };

    const removeToast = (id) => {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    };

    const success = (message, duration) => addToast(message, "success", duration);
    const error = (message, duration) => addToast(message, "error", duration);

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
    };
}
