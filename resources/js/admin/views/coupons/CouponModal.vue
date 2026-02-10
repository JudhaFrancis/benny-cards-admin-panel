<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
      <!-- Backdrop -->
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
              <!-- Sticky Header -->
              <div
                class="relative bg-primary text-white overflow-hidden shrink-0 sticky top-0 z-10"
              >
                <div
                  class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent"
                ></div>
                <div
                  class="absolute -right-20 -top-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl"
                ></div>

                <div
                  class="relative px-8 py-8 flex items-center justify-between"
                >
                  <div class="flex items-center gap-5">
                    <div
                      class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner"
                    >
                      <TicketIcon
                        v-if="!editCoupon"
                        class="h-7 w-7 text-white"
                      />
                      <Edit3Icon v-else class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        {{ editCoupon ? "Edit Coupon" : "Add New Coupon" }}
                      </DialogTitle>
                      <p class="text-sm text-white/80">
                        {{
                          editCoupon
                            ? "Update coupon code and discount settings."
                            : "Create a new promotional discount coupon."
                        }}
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

              <!-- Scrollable Form Content -->
              <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <form @submit.prevent="handleSubmit" class="space-y-8">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Code -->
                    <div class="md:col-span-2">
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        <div class="flex items-center gap-2">
                          <TicketIcon class="h-4 w-4 text-gray-400" />
                          Coupon Code
                          <span class="text-rose-500">*</span>
                        </div>
                      </label>
                      <input
                        v-model="form.code"
                        type="text"
                        placeholder="e.g. SAVE20, WELCOME2026"
                        class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-gray-400 font-bold uppercase tracking-wider"
                        required
                      />
                    </div>

                    <!-- Type -->
                    <div>
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        <div class="flex items-center gap-2">
                          <TagIcon class="h-4 w-4 text-gray-400" />
                          Discount Type
                          <span class="text-rose-500">*</span>
                        </div>
                      </label>
                      <ContextDropdown
                        v-model="form.type"
                        :options="typeOptions"
                        :icon="TagIcon"
                      />
                    </div>

                    <!-- Value -->
                    <div>
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        <div class="flex items-center gap-2">
                          <HashIcon class="h-4 w-4 text-gray-400" />
                          Value
                          <span class="text-rose-500">*</span>
                        </div>
                      </label>
                      <div class="relative group">
                        <input
                          v-model="form.value"
                          type="number"
                          step="0.01"
                          placeholder="0.00"
                          class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-gray-400"
                          required
                        />
                        <div
                          class="absolute inset-y-0 right-4 flex items-center pointer-events-none"
                        >
                          <span class="text-gray-400 font-bold">
                            {{ form.type === "percent" ? "%" : "₹" }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2">
                      <label
                        class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1"
                      >
                        <div class="flex items-center gap-2">
                          <ActivityIcon class="h-4 w-4 text-gray-400" />
                          Status
                        </div>
                      </label>
                      <ContextDropdown
                        v-model="form.status"
                        :options="statusOptions"
                        :icon="ActivityIcon"
                      />
                    </div>
                  </div>
                </form>
              </div>

              <!-- Sticky Footer -->
              <div
                class="px-8 py-6 border-t border-gray-50 bg-gray-50/50 flex justify-end gap-3 sticky bottom-0 z-10"
              >
                <button
                  type="button"
                  @click="handleClose"
                  class="px-6 py-3 rounded-2xl text-gray-600 font-semibold hover:bg-gray-100 transition-all active:scale-95"
                >
                  Cancel
                </button>
                <button
                  @click="handleSubmit"
                  :disabled="loading"
                  class="px-8 py-3 bg-primary text-white rounded-2xl font-bold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-primary/25 flex items-center gap-2 active:scale-95"
                >
                  <span
                    v-if="loading"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
                  ></span>
                  {{ editCoupon ? "Update Coupon" : "Create Coupon" }}
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
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  X as XIcon,
  Ticket as TicketIcon,
  Edit3 as Edit3Icon,
  Tag as TagIcon,
  Hash as HashIcon,
  Activity as ActivityIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import ContextDropdown from "../../components/ui/ContextDropdown.vue";

const props = defineProps({
  isOpen: Boolean,
  editCoupon: Object,
});

const emit = defineEmits(["close", "refresh"]);
const { success: toastSuccess, error: toastError } = useToast();

const loading = ref(false);
const form = reactive({
  code: "",
  type: "fixed",
  value: "",
  status: "active",
});

const typeOptions = [
  {
    label: "Fixed Amount",
    value: "fixed",
    description: "Deduct a specific amount in ₹.",
    badge: "₹ INR",
    badgeClass: "bg-blue-100 text-blue-700",
  },
  {
    label: "Percentage",
    value: "percent",
    description: "Deduct a percentage of the total.",
    badge: "% OFF",
    badgeClass: "bg-purple-100 text-purple-700",
  },
];

const statusOptions = [
  {
    label: "Active",
    value: "active",
    description: "Coupon can be used by customers.",
    badge: "Live",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Inactive",
    value: "inactive",
    description: "Coupon is disabled.",
    badge: "Hidden",
    badgeClass: "bg-gray-200 text-gray-500",
  },
];

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      if (props.editCoupon) {
        form.code = props.editCoupon.code;
        form.type = props.editCoupon.type;
        form.value = props.editCoupon.value;
        form.status = props.editCoupon.status;
      } else {
        form.code = "";
        form.type = "fixed";
        form.value = "";
        form.status = "active";
      }
    }
  },
);

const handleClose = () => {
  if (!loading.value) {
    emit("close");
  }
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    const url = props.editCoupon
      ? `/api/v1/coupons/${props.editCoupon.id}`
      : "/api/v1/coupons";
    const method = props.editCoupon ? "put" : "post";

    const response = await axios({
      method,
      url,
      data: { ...form },
    });

    if (response.data.success) {
      toastSuccess(response.data.message);
      emit("refresh");
      loading.value = false;
      handleClose();
    }
  } catch (error) {
    console.error("Coupon submission failed", error);
    toastError(error.response?.data?.message || "Failed to save coupon");
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
