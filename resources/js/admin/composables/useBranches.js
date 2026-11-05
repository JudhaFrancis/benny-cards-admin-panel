import { ref, computed } from "vue";
import axios from "axios";

/**
 * Shared branch list (from the branches table), loaded once and reused by every picker / filter.
 * Branches are { id, name, is_active, sort_order, orders_count }.
 */
const branches = ref([]);
let loadPromise = null;

export function useBranches() {
  const fetchBranches = async (force = false) => {
    if (loadPromise && !force) return loadPromise;
    loadPromise = axios
      .get("/api/v1/branches")
      .then((res) => {
        branches.value = Array.isArray(res.data?.data) ? res.data.data : [];
        return branches.value;
      })
      .catch((err) => {
        console.error("Failed to load branches", err);
        loadPromise = null;
        return branches.value;
      });
    return loadPromise;
  };

  const activeBranches = computed(() => branches.value.filter((b) => b.is_active));

  /** Options for select filters: { value: id, label: name } */
  const branchOptions = computed(() =>
    activeBranches.value.map((b) => ({ value: b.id, label: b.name }))
  );

  const branchName = (id) => branches.value.find((b) => b.id === Number(id))?.name || "";

  return {
    branches,
    activeBranches,
    branchOptions,
    branchName,
    fetchBranches,
  };
}
