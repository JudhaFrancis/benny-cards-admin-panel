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
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" />
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
              class="w-full max-w-4xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all border border-slate-100 flex flex-col h-[85vh]"
            >
              <!-- Header -->
              <div
                class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-white sticky top-0 z-10"
              >
                <div class="flex items-center gap-4">
                  <div
                    class="w-12 h-12 rounded-2xl bg-primary/5 flex items-center justify-center text-primary"
                  >
                    <PencilIcon class="h-6 w-6" />
                  </div>
                  <div>
                    <DialogTitle
                      as="h3"
                      class="text-2xl font-bold text-slate-900 tracking-tight"
                    >
                      Edit Order - {{ editedOrder?.id }}
                    </DialogTitle>
                    <p class="text-sm text-slate-500 mt-0.5">
                      Modify items, customer info, or track progress.
                    </p>
                  </div>
                </div>
                <button
                  @click="$emit('openChange', false)"
                  class="p-3 rounded-2xl hover:bg-slate-100 text-slate-400 transition-all active:scale-95"
                >
                  <XIcon class="h-6 w-6" />
                </button>
              </div>

              <!-- Navigation Tabs -->
              <div class="px-8 pt-4 bg-white border-b border-slate-50">
                <div class="flex gap-8">
                  <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="pb-4 text-sm font-bold uppercase tracking-wider transition-all relative"
                    :class="
                      activeTab === tab.id
                        ? 'text-primary'
                        : 'text-slate-400 hover:text-slate-600'
                    "
                  >
                    {{ tab.label }}
                    <div
                      v-if="activeTab === tab.id"
                      class="absolute bottom-0 left-0 right-0 h-1 bg-primary rounded-full animate-in slide-in-from-bottom-1 duration-300"
                    />
                  </button>
                </div>
              </div>

              <!-- Content -->
              <div
                class="flex-1 overflow-y-auto p-8 custom-scrollbar bg-slate-50/20"
              >
                <div v-if="editedOrder">
                  <EditOrderItems
                    v-if="activeTab === 'items'"
                    :order="editedOrder"
                    @update:order="(val) => (editedOrder = val)"
                    @save="handleSave"
                  />
                  <EditCustomerDetails
                    v-if="activeTab === 'customer'"
                    :order="editedOrder"
                    @update:order="(val) => (editedOrder = val)"
                    @save="handleSave"
                  />
                  <EditOrderTracking
                    v-if="activeTab === 'tracking'"
                    :order="editedOrder"
                    @update:order="(val) => (editedOrder = val)"
                    @save="handleSave"
                  />
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
import { ref, watch } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import { X as XIcon, Pencil as PencilIcon } from "lucide-vue-next";
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
});

const emit = defineEmits(["openChange", "save"]);

const activeTab = ref("items");
const editedOrder = ref(null);

const tabs = [
  { id: "items", label: "Order Items" },
  { id: "customer", label: "Customer Details" },
  { id: "tracking", label: "Order Tracking" },
];

watch(
  () => props.order,
  (val) => {
    if (val) {
      editedOrder.value = JSON.parse(JSON.stringify(val));
    }
  },
  { immediate: true },
);

const handleSave = () => {
  emit("save", editedOrder.value);
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
