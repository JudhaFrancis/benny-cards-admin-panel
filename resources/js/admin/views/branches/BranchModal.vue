<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translateY(20px)"
            enter-to="opacity-100 scale-100 translateY(0)"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translateY(0)"
            leave-to="opacity-0 scale-95 translateY(20px)"
          >
            <DialogPanel
              class="w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-gray-100 flex flex-col"
              :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            >
              <!-- Header -->
              <div class="relative bg-primary text-white overflow-hidden shrink-0 sticky top-0 z-10">
                <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent"></div>
                <div class="absolute -right-20 -top-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>

                <div class="relative px-8 py-8 flex items-center justify-between">
                  <div class="flex items-center gap-5">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner">
                      <StoreIcon v-if="!editBranch" class="h-7 w-7 text-white" />
                      <Edit3Icon v-else class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle as="h3" class="text-2xl font-bold tracking-tight text-white mb-1">
                        {{ editBranch ? "Edit Branch" : "Add New Branch" }}
                      </DialogTitle>
                      <p class="text-sm text-white/80">
                        {{ editBranch ? "Rename the branch or change its status." : "Create a branch orders can be placed in." }}
                      </p>
                    </div>
                  </div>
                  <button
                    @click="handleClose"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-white/80 hover:text-white transition-all border border-white/5 active:scale-95"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Form -->
              <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <form @submit.prevent="handleSubmit" class="space-y-8">
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2.5 ml-1">
                      <div class="flex items-center gap-2">
                        <TypeIcon class="h-4 w-4 text-gray-400" />
                        Branch Name
                        <span class="text-rose-500">*</span>
                      </div>
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      maxlength="100"
                      placeholder="e.g. Nagercoil, Chennai, Online"
                      class="w-full px-5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-gray-400"
                      required
                    />
                    <p v-if="editBranch && editBranch.orders_count > 0 && form.name.trim() !== editBranch.name" class="mt-2 ml-1 text-[11px] text-amber-600">
                      Renaming also updates the branch name on its {{ editBranch.orders_count }} orders.
                    </p>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2.5 ml-1">
                      <div class="flex items-center gap-2">
                        <ActivityIcon class="h-4 w-4 text-gray-400" />
                        Status
                      </div>
                    </label>
                    <ContextDropdown v-model="form.status" :options="statusOptions" :icon="ActivityIcon" />
                  </div>
                </form>
              </div>

              <!-- Footer -->
              <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/50 flex justify-end gap-3 sticky bottom-0 z-10">
                <button
                  type="button"
                  @click="handleClose"
                  class="px-6 py-3 rounded-2xl text-gray-600 font-semibold hover:bg-gray-100 transition-all active:scale-95"
                >
                  Cancel
                </button>
                <button
                  @click="handleSubmit"
                  :disabled="loading || !form.name.trim()"
                  class="px-8 py-3 bg-primary text-white rounded-2xl font-bold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-primary/25 flex items-center gap-2 active:scale-95"
                >
                  <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                  {{ editBranch ? "Save Changes" : "Create Branch" }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, reactive } from "vue";
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from "@headlessui/vue";
import {
  X as XIcon,
  Store as StoreIcon,
  Edit3 as Edit3Icon,
  Type as TypeIcon,
  Activity as ActivityIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import ContextDropdown from "../../components/ui/dropdowns/ContextDropdown.vue";

const props = defineProps({
  isOpen: Boolean,
  editBranch: Object,
});

const emit = defineEmits(["close", "refresh"]);
const { success: toastSuccess, error: toastError } = useToast();

const loading = ref(false);
const form = reactive({ name: "", status: "active" });

const statusOptions = [
  {
    label: "Active",
    value: "active",
    description: "Shown in Order Placed In pickers and filters.",
    badge: "Active",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Inactive",
    value: "inactive",
    description: "Hidden from pickers; existing orders keep this branch.",
    badge: "Hidden",
    badgeClass: "bg-gray-200 text-gray-500",
  },
];

watch(
  () => props.isOpen,
  (open) => {
    if (!open) return;
    form.name = props.editBranch?.name || "";
    form.status = props.editBranch ? (props.editBranch.is_active ? "active" : "inactive") : "active";
  },
);

const handleClose = () => {
  if (!loading.value) emit("close");
};

const handleSubmit = async () => {
  if (!form.name.trim()) return;
  loading.value = true;
  try {
    const payload = { name: form.name.trim(), is_active: form.status === "active" };
    const response = props.editBranch
      ? await axios.put(`/api/v1/branches/${props.editBranch.id}`, payload)
      : await axios.post("/api/v1/branches", payload);

    if (response.data.success) {
      toastSuccess(response.data.message);
      emit("refresh");
      loading.value = false;
      handleClose();
    }
  } catch (error) {
    const errors = error.response?.data?.errors;
    toastError((errors && Object.values(errors)[0]?.[0]) || error.response?.data?.message || "Failed to save branch");
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
