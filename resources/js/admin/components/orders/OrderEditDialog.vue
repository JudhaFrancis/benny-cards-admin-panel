<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog
      as="div"
      @close="$emit('openChange', false)"
      class="relative z-[60]"
    >
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
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" />
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
              class="w-full max-w-5xl transform rounded-[2.5rem] bg-white shadow-2xl transition-all border border-slate-100 flex flex-col h-[85vh] overflow-visible"
            >
              <!-- Header -->
              <div
                class="relative bg-primary text-white overflow-hidden shrink-0 rounded-t-[2.5rem]"
              >
                <!-- Decorative Background -->
                <div
                  class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent"
                ></div>
                <div
                  class="absolute -right-20 -top-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl"
                ></div>

                <div
                  class="relative px-8 py-6 flex items-center justify-between"
                >
                  <div class="flex items-center gap-5">
                    <div
                      class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner"
                    >
                      <PencilIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        Edit Order #{{
                          editedOrder?.order_number || editedOrder?.id
                        }}
                      </DialogTitle>
                      <div class="flex items-center gap-3 mt-1.5">
                        <span
                          class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10 backdrop-blur-sm"
                        >
                          <CalendarIcon class="h-3.5 w-3.5" />
                          {{ formatDate(editedOrder?.order_date) }}
                        </span>
                        <span
                          class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10 backdrop-blur-sm"
                        >
                          <TruckIcon class="h-3.5 w-3.5" />
                          {{ editedOrder?.tracking_number || "No Tracking" }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <button
                    @click="$emit('openChange', false)"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white transition-all border border-white/5 disabled:opacity-50"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>

                <!-- Tabs -->
                <div
                  class="px-8 mt-2 flex gap-1 overflow-x-auto custom-scrollbar pb-0"
                >
                  <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="relative px-6 py-3 text-sm font-bold transition-all rounded-t-2xl whitespace-nowrap"
                    :class="[
                      activeTab === tab.id
                        ? 'bg-white text-primary shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] translate-y-[1px] z-10'
                        : 'text-white/60 hover:text-white hover:bg-white/5',
                    ]"
                  >
                    {{ tab.label }}
                  </button>
                </div>
              </div>

              <!-- Content -->
              <div
                class="flex-1 overflow-y-auto bg-slate-50/50 custom-scrollbar p-0 relative"
              >
                <!-- Main Content Container with white background for active tab illusion -->
                <div class="bg-white min-h-full p-8 pb-40 rounded-b-[2rem]">
                  <div v-if="editedOrder" class="max-w-4xl mx-auto">
                    <Transition
                      mode="out-in"
                      enter-active-class="transition duration-200 ease-out"
                      enter-from-class="transform opacity-0 translate-y-2"
                      enter-to-class="transform opacity-100 translate-y-0"
                      leave-active-class="transition duration-150 ease-in"
                      leave-from-class="transform opacity-100 translate-y-0"
                      leave-to-class="transform opacity-0 -translate-y-2"
                    >
                      <component
                        :is="activeTabComponent"
                        :order="editedOrder"
                        @update:order="(val) => (editedOrder = val)"
                        @save="handleSave"
                        @success="handleSuccess"
                        @cancel="$emit('openChange', false)"
                        :is-saving="isSaving"
                        :key="activeTab"
                      />
                    </Transition>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  X as XIcon,
  Pencil as PencilIcon,
  Calendar as CalendarIcon,
  Truck as TruckIcon,
} from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import EditOrderItems from "./edit-tabs/EditOrderItems.vue";
import EditCustomerDetails from "./edit-tabs/EditCustomerDetails.vue";
import EditOrderTracking from "./edit-tabs/EditOrderTracking.vue";

const props = defineProps({
  order: {
    type: Object,
    default: null,
  },
  open: {
    type: Boolean,
    required: true,
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["openChange", "save", "refresh"]);
const toast = useToast();

const activeTab = ref("items");
const editedOrder = ref(null);

const tabs = [
  { id: "items", label: "Order Items" },
  { id: "customer", label: "Customer Details" },
  { id: "tracking", label: "Order Tracking" },
];

const activeTabComponent = computed(() => {
  switch (activeTab.value) {
    case "items":
      return EditOrderItems;
    case "customer":
      return EditCustomerDetails;
    case "tracking":
      return EditOrderTracking;
    default:
      return EditOrderItems;
  }
});

watch(
  () => props.order,
  (val) => {
    if (val) {
      // Deep copy to avoid mutating prop directly
      editedOrder.value = JSON.parse(JSON.stringify(val));

      // Initialize tracking object if it doesn't exist
      if (!editedOrder.value.tracking) {
        editedOrder.value.tracking = {};
      }
    }
  },
  { immediate: true },
);

// Reset tab when modal opens
watch(
  () => props.open,
  (val) => {
    if (val) activeTab.value = "items";
  },
);

// Watch for dispatch expense changes to update total amount
watch(
  () => editedOrder.value?.tracking?.dispatch_mode?.expense,
  (newVal) => {
    if (!editedOrder.value) return;
    
    // Calculate items subtotal
    const itemsSubtotal = (editedOrder.value.items || []).reduce((acc, item) => {
       return acc + ((parseFloat(item.unit_price) || 0) * (parseInt(item.quantity) || 0));
    }, 0);
    
    const discount = parseFloat(editedOrder.value.discount) || 0;
    const expense = parseFloat(newVal) || 0; // Use the new expense value
    
    const newTotal = Math.max(0, itemsSubtotal - discount + expense);
    editedOrder.value.total_amount = newTotal;
    
    // Update balance due
    const paid = parseFloat(editedOrder.value.paid_amount) || 0;
    editedOrder.value.balance_due = Math.max(0, newTotal - paid);
  }
);

const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};

const handleSave = async (updatedOrderData) => {
  // Handle Tracking Tab Save Separately
  if (activeTab.value === "tracking") {
    try {
      // payload comes from EditOrderTracking emit('save', payload)
      const payload = updatedOrderData || editedOrder.value.tracking;

      const res = await axios.put(
        `/api/v1/orders/${props.order.id}/tracking`,
        payload,
      );

      if (res.data.success) {
        toast.success("Tracking info updated");
        // Update local state with fresh data from server
        if (res.data.data) {
          editedOrder.value = {
            ...res.data.data,
            // Ensure tracking exists even if not in response
            tracking: res.data.data.tracking || {},
          };
        }

        // Explicitly close if saving the last section (Payment)
        if (payload.payment_info) {
          handleSuccess();
        }
      }
    } catch (e) {
      console.error(e);
      toast.error("Failed to update tracking info");
    }
    return;
  }

  // If the child component emits specific data, use it. Otherwise use local state.
  const dataToEmit = updatedOrderData || editedOrder.value;
  emit("save", dataToEmit);
};

const handleSuccess = () => {
  emit("refresh");
  emit("openChange", false);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
