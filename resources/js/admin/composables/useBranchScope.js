import { ref, computed, watch } from "vue";
import { useAuth } from "./useAuth";

/**
 * Header branch picker.
 *
 * The user's branches come from users.branch_ids (sent on login / me as user.branches).
 * The pick is saved per user in localStorage and sent on every request as X-Branch-Ids
 * ("all" or "1,3"). The server always limits it to the user's own branches.
 */
const STORAGE_KEY = "branch_scope";

// Bumped on every change; the layout keys the page on it so lists re-fetch
const scopeVersion = ref(0);
const selected = ref(null); // null = all of the user's branches; otherwise array of ids

const readSaved = (userId) => {
    try {
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || "null");
        return saved && saved.userId === userId ? saved.ids : null;
    } catch {
        return null;
    }
};

const save = (userId, ids) => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({ userId, ids }));
    } catch {
        /* storage unavailable */
    }
};

/** Value for the X-Branch-Ids header (read straight from storage so it works before any component mounts). */
export function branchScopeHeader() {
    try {
        const user = JSON.parse(localStorage.getItem("auth_user") || "null");
        const ids = readSaved(user?.id);
        return Array.isArray(ids) && ids.length ? ids.join(",") : "all";
    } catch {
        return "all";
    }
}

export function clearBranchScope() {
    selected.value = null;
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch {
        /* ignore */
    }
}

export function useBranchScope() {
    const { user } = useAuth();

    const branches = computed(() => user.value?.branches || []);
    const allowedIds = computed(() => branches.value.map((b) => Number(b.id)));
    const hasAllBranches = computed(() => !!user.value?.all_branches);

    // Restore the saved pick, dropping branches the user no longer has
    const restore = () => {
        const saved = readSaved(user.value?.id);
        const valid = Array.isArray(saved) ? saved.map(Number).filter((id) => allowedIds.value.includes(id)) : [];
        selected.value = valid.length && valid.length < allowedIds.value.length ? valid : null;
    };
    if (selected.value === null) restore();
    watch(allowedIds, restore);

    const selectedIds = computed(() => selected.value ?? allowedIds.value);
    const isAll = computed(() => selected.value === null);

    const setSelection = (ids) => {
        const clean = [...new Set((ids || []).map(Number))].filter((id) => allowedIds.value.includes(id));
        // Nothing or everything picked = all of the user's branches
        selected.value = clean.length && clean.length < allowedIds.value.length ? clean : null;
        save(user.value?.id, selected.value);
        scopeVersion.value++;
    };

    const label = computed(() => {
        if (!branches.value.length) return "No branch assigned";
        if (isAll.value) return hasAllBranches.value ? "All Branches" : branches.value.map((b) => b.name).join(", ");
        return branches.value.filter((b) => selectedIds.value.includes(Number(b.id))).map((b) => b.name).join(", ");
    });

    return { branches, allowedIds, hasAllBranches, selectedIds, isAll, setSelection, label, scopeVersion };
}
