<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-[60]">
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
              class="w-full max-w-6xl transform rounded-[2.5rem] bg-white shadow-2xl transition-all border border-slate-100 flex flex-col h-[85vh] overflow-hidden"
            >
              <!-- Header -->
              <div
                class="relative bg-primary text-white overflow-hidden shrink-0"
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
                      <PackageIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        Order #{{ order?.order_number || order?.id }}
                      </DialogTitle>
                      <div class="flex items-center gap-3 mt-1.5">
                        <span
                          class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10 backdrop-blur-sm"
                        >
                          <ActivityIcon class="h-3.5 w-3.5" />
                          {{ order?.resolved_status || "New Order" }}
                        </span>
                        <span
                          class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10 backdrop-blur-sm"
                        >
                          <CalendarIcon class="h-3.5 w-3.5" />
                          {{ formatDate(order?.order_date) }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <button
                    @click="$emit('close')"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white transition-all border border-white/5"
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
                class="flex-1 overflow-y-auto bg-slate-50/50 custom-scrollbar p-10 relative"
              >
                <!-- Main Content Container with white background for active tab illusion -->
                <div class="bg-white min-h-full rounded-b-[2rem] p-8" v-if="!order">
                    <div class="space-y-10">
                        <div class="space-y-4">
                            <SkeletonLoader width="250px" height="24px" />
                            <div class="grid grid-cols-3 gap-6">
                                <SkeletonLoader v-for="i in 3" :key="i" height="80px" />
                            </div>
                        </div>
                        <div class="space-y-4">
                            <SkeletonLoader width="200px" height="24px" />
                            <SkeletonLoader height="300px" />
                        </div>
                    </div>
                </div>
                <div class="bg-white min-h-full rounded-b-[2rem]" v-else>
                  <div class="w-full">
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
                        :order="order"
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
import { ref, computed, watch } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  X as XIcon,
  Package as PackageIcon,
  Calendar as CalendarIcon,
  Activity as ActivityIcon,
} from "lucide-vue-next";
import ViewOrderGeneralDetails from "./info-tabs/ViewOrderGeneralDetails.vue";
import ViewOrderTracking from "./info-tabs/ViewOrderTracking.vue";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  order: {
    type: Object,
    default: null,
  },
});

defineEmits(["close"]);

const activeTab = ref("details");

const tabs = [
  { id: "details", label: "Order Details" },
  { id: "tracking", label: "Order Tracking" },
];

const activeTabComponent = computed(() => {
  switch (activeTab.value) {
    case "details":
      return ViewOrderGeneralDetails;
    case "tracking":
      return ViewOrderTracking;
    default:
      return ViewOrderGeneralDetails;
  }
});

// Reset tab when modal opens
watch(
  () => props.isOpen,
  (val) => {
    if (val) activeTab.value = "details";
  },
);

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
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
