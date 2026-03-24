<template>
  <div class="flex flex-col h-full bg-white">
    <!-- Header with Stepper Progress -->
    <div class="px-8 pt-8 pb-6 border-b border-slate-100 flex-shrink-0">
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-2xl font-black text-slate-900 tracking-tight">
            Order Tracking
          </h2>
          <p class="text-sm text-slate-400 font-medium mt-1">
            Real-time production and delivery status
          </p>
        </div>
        <div class="flex items-center gap-3">
          <div class="flex -space-x-2">
            <template v-for="section in trackingSections" :key="section.id">
              <div
                v-if="completedSections.includes(section.id)"
                class="w-8 h-8 rounded-full border-2 border-white bg-emerald-500 flex items-center justify-center text-white"
                :title="section.label"
              >
                <CheckIcon class="h-4 w-4" />
              </div>
            </template>
          </div>
          <span
            class="text-xs font-black text-slate-400 uppercase tracking-widest pl-2 border-l border-slate-100"
          >
            {{ completedSections.length }}/{{ trackingSections.length }} Steps
          </span>
        </div>
      </div>

      <!-- Horizontal Stepper -->
      <div class="relative">
        <div
          class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 rounded-full overflow-hidden"
        >
          <div
            class="h-full bg-primary transition-all duration-700 ease-out shadow-[0_0_10px_rgba(var(--primary-rgb),0.3)]"
            :style="{
              width: `${(selectedSectionIndex / (trackingSections.length - 1)) * 100}%`,
            }"
          ></div>
        </div>

        <div class="relative flex justify-between">
          <button
            v-for="(section, index) in trackingSections"
            :key="section.id"
            @click="selectedSection = section.id"
            class="group relative flex flex-col items-center gap-3 active:scale-95 transition-all duration-300"
            :class="{ 'z-10': selectedSection === section.id }"
          >
            <!-- Step Circle -->
            <div
              class="w-12 h-12 rounded-[1.25rem] border-4 flex items-center justify-center transition-all duration-500 shadow-sm"
              :class="[
                selectedSection === section.id
                  ? 'bg-primary border-primary ring-8 ring-primary/5 scale-110 shadow-lg'
                  : completedSections.includes(section.id)
                    ? 'bg-emerald-500 border-emerald-500 scale-100'
                    : 'bg-white border-slate-100 group-hover:border-slate-300 scale-100',
              ]"
            >
              <component
                :is="section.icon"
                class="h-5 w-5 transition-all duration-300"
                :class="[
                  selectedSection === section.id ||
                  completedSections.includes(section.id)
                    ? 'text-white'
                    : 'text-slate-400 group-hover:text-slate-600',
                ]"
              />

              <!-- Checkmark for completed but not selected -->
              <div
                v-if="
                  completedSections.includes(section.id) &&
                  selectedSection !== section.id
                "
                class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-white border-2 border-emerald-500 flex items-center justify-center shadow-sm"
              >
                <CheckIcon class="h-3 w-3 text-emerald-600 stroke-[3]" />
              </div>
            </div>

            <!-- Step Label -->
            <span
              class="absolute -bottom-8 whitespace-nowrap text-[10px] font-black uppercase tracking-widest transition-all duration-300 pointer-events-none"
              :class="[
                selectedSection === section.id
                  ? 'text-primary opacity-100 translate-y-0'
                  : 'text-slate-400 opacity-60 translate-y-1',
              ]"
            >
              {{ section.shortLabel }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/30">
      <div class="max-w-4xl mx-auto p-8 pt-12">
        <div
          class="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 p-8 min-h-[500px] animate-in fade-in slide-in-from-bottom-4 duration-500"
        >
          <!-- Section Title -->
          <div
            class="flex items-center gap-4 mb-10 pb-6 border-b border-slate-50"
          >
            <div class="p-3 bg-primary/10 rounded-2xl">
              <component
                :is="activeSectionData?.icon"
                class="h-6 w-6 text-primary"
              />
            </div>
            <div>
              <h3 class="text-xl font-black text-slate-900 leading-none">
                {{ activeSectionData?.label }}
              </h3>
              <p class="text-sm text-slate-400 mt-2 font-medium">
                Tracking Section Information
              </p>
            </div>
            <div
              v-if="completedSections.includes(selectedSection)"
              class="ml-auto px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center gap-2"
            >
              <CheckCircleIcon class="h-4 w-4" />
              <span class="text-xs font-black uppercase tracking-widest"
                >Completed</span
              >
            </div>
          </div>

          <!-- Dynamic Section Component -->
          <keep-alive>
            <component :is="activeSectionComponent" :order="order" />
          </keep-alive>
        </div>
      </div>
    </div>

    <!-- Quick Navigation Footer -->
    <div
      class="px-8 py-4 bg-white border-t border-slate-100 flex items-center justify-between flex-shrink-0"
    >
      <button
        @click="navigate(-1)"
        :disabled="selectedSectionIndex === 0"
        class="flex items-center gap-2 px-6 py-2.5 rounded-xl border border-slate-100 text-slate-400 font-bold hover:bg-slate-50 transition-all disabled:opacity-30"
      >
        <ArrowLeftIcon class="h-4 w-4" /> Previous
      </button>

      <div class="flex gap-2">
        <div
          v-for="(s, i) in trackingSections"
          :key="s.id"
          class="w-1.5 h-1.5 rounded-full transition-all duration-300"
          :class="[
            i === selectedSectionIndex ? 'w-6 bg-primary' : 'bg-slate-200',
          ]"
        ></div>
      </div>

      <button
        @click="navigate(1)"
        :disabled="selectedSectionIndex === trackingSections.length - 1"
        class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 shadow-lg shadow-slate-900/10 transition-all disabled:opacity-30"
      >
        Next <ArrowRightIcon class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import {
  ClipboardList as ClipboardListIcon,
  User as UserIcon,
  CreditCard as CreditCardIcon,
  Settings as SettingsIcon,
  Check as CheckIcon,
  Clock as ClockIcon,
  CheckCircle as CheckCircleIcon,
  ChevronLeft as ArrowLeftIcon,
  ChevronRight as ArrowRightIcon,
  Printer as PrinterIcon,
  Layers as LayersIcon,
  Package as PackageIcon,
  Truck as TruckIcon,
  Navigation as NavigationIcon,
  Bus as BusIcon,
  IndianRupee as IndianRupeeIcon,
} from "lucide-vue-next";

// Section Views
import ViewOrderDetailsSection from "./tracking-view-tabs/ViewOrderDetailsSection.vue";
import ViewClientInfoSection from "./tracking-view-tabs/ViewClientInfoSection.vue";
import ViewCardSpecsSection from "./tracking-view-tabs/ViewCardSpecsSection.vue";
import ViewWorkAssignSection from "./tracking-view-tabs/ViewWorkAssignSection.vue";
import ViewDesignPrintSection from "./tracking-view-tabs/ViewDesignPrintSection.vue";
import ViewOrderPrintingSection from "./tracking-view-tabs/ViewOrderPrintingSection.vue";
import ViewPackagingLogisticsSection from "./tracking-view-tabs/ViewPackagingLogisticsSection.vue";
import ViewPackagingStatusSection from "./tracking-view-tabs/ViewPackagingStatusSection.vue";
import ViewDeliveryLocationSection from "./tracking-view-tabs/ViewDeliveryLocationSection.vue";
import ViewDispatchModeSection from "./tracking-view-tabs/ViewDispatchModeSection.vue";
import ViewDispatchDetailsSection from "./tracking-view-tabs/ViewDispatchDetailsSection.vue";
import ViewPaymentSection from "./tracking-view-tabs/ViewPaymentSection.vue";

const props = defineProps({
  order: { type: Object, required: true },
});

const selectedSection = ref("order-details");
const completedSections = ref([]);

const trackingSections = [
  {
    id: "order-details",
    label: "Order Details",
    shortLabel: "Details",
    icon: ClipboardListIcon,
  },
  {
    id: "client-info",
    label: "Client Information",
    shortLabel: "Client",
    icon: UserIcon,
  },
  {
    id: "card-specs",
    label: "Card Specifications",
    shortLabel: "Specs",
    icon: CreditCardIcon,
  },
  {
    id: "work-assign",
    label: "Work Assignment",
    shortLabel: "Design",
    icon: SettingsIcon,
  },
  {
    id: "design-print",
    label: "Design & Print Stat",
    shortLabel: "D&P",
    icon: PrinterIcon,
  },
  {
    id: "order-printing",
    label: "Order Printing Status",
    shortLabel: "Print",
    icon: LayersIcon,
  },
  {
    id: "packaging-logistics",
    label: "Packaging & Logistics",
    shortLabel: "Logis",
    icon: PackageIcon,
  },
  {
    id: "packaging-status",
    label: "Packaging Status",
    shortLabel: "Pack",
    icon: PackageIcon,
  },
  {
    id: "delivery-location",
    label: "Delivery Location",
    shortLabel: "Loc",
    icon: NavigationIcon,
  },
  {
    id: "dispatch-mode",
    label: "Dispatch Mode",
    shortLabel: "Mode",
    icon: TruckIcon,
  },
  {
    id: "dispatch-details",
    label: "Dispatch Details",
    shortLabel: "Disp",
    icon: BusIcon,
  },
  {
    id: "payment",
    label: "Payment Information",
    shortLabel: "Pay",
    icon: IndianRupeeIcon,
  },
];

const selectedSectionIndex = computed(() =>
  trackingSections.findIndex((s) => s.id === selectedSection.value),
);

const activeSectionData = computed(() =>
  trackingSections.find((s) => s.id === selectedSection.value),
);

const activeSectionComponent = computed(() => {
  switch (selectedSection.value) {
    case "order-details":
      return ViewOrderDetailsSection;
    case "client-info":
      return ViewClientInfoSection;
    case "card-specs":
      return ViewCardSpecsSection;
    case "work-assign":
      return ViewWorkAssignSection;
    case "design-print":
      return ViewDesignPrintSection;
    case "order-printing":
      return ViewOrderPrintingSection;
    case "packaging-logistics":
      return ViewPackagingLogisticsSection;
    case "packaging-status":
      return ViewPackagingStatusSection;
    case "delivery-location":
      return ViewDeliveryLocationSection;
    case "dispatch-mode":
      return ViewDispatchModeSection;
    case "dispatch-details":
      return ViewDispatchDetailsSection;
    case "payment":
      return ViewPaymentSection;
    default:
      return ViewOrderDetailsSection;
  }
});

const calculateCompletedStatus = () => {
  const order = props.order;
  const completed = [];

  if (order.order_date || order.client_information?.job_details)
    completed.push("order-details");
  if (order.client_information?.client_info?._audit || order.client_information?.client_info) completed.push("client-info");
  if (order.client_information?.card_specs?._audit || order.client_information?.card_specs) completed.push("card-specs");
  
  if (order.designing?.work_assign?._audit || order.designing?.work_assign) completed.push("work-assign");
  if (order.designing?.design_print?._audit || order.designing?.design_print) completed.push("design-print");
  
  if (order.printing?.printing_status?._audit || order.printing?.printing_status) completed.push("order-printing");
  
  if (order.packaging?.packaging_logistics?._audit || order.packaging?.packaging_logistics)
    completed.push("packaging-logistics");
  if (order.packaging?.packaging_status?._audit || order.packaging?.packaging_status) completed.push("packaging-status");
  
  if (order.dispatch_delivery?.delivery_location?._audit || order.dispatch_delivery?.delivery_location) completed.push("delivery-location");
  if (order.dispatch_delivery?.dispatch_mode?._audit || order.dispatch_delivery?.dispatch_mode) completed.push("dispatch-mode");
  if (order.dispatch_delivery?.dispatch_details?._audit || order.dispatch_delivery?.dispatch_details) completed.push("dispatch-details");
  
  if (order.payments && order.payments.length > 0) completed.push("payment");

  return completed;
};

const navigate = (direction) => {
  const newIndex = selectedSectionIndex.value + direction;
  if (newIndex >= 0 && newIndex < trackingSections.length) {
    selectedSection.value = trackingSections[newIndex].id;
  }
};

onMounted(() => {
  completedSections.value = calculateCompletedStatus();

  // Set default section to last completed or first if none
  if (completedSections.value.length > 0) {
    const lastFilled =
      completedSections.value[completedSections.value.length - 1];
    const nextIndex =
      trackingSections.findIndex((s) => s.id === lastFilled) + 1;
    if (nextIndex < trackingSections.length) {
      selectedSection.value = trackingSections[nextIndex].id;
    } else {
      selectedSection.value = lastFilled;
    }
  }
});
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
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

@keyframes progressPulse {
  0% {
    opacity: 0.6;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.6;
  }
}

.bg-primary {
  --primary-rgb: 79, 70, 229; /* Approx primary color indigo-600 */
}
</style>
