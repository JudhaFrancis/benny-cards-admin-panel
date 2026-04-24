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
      <div class="max-w-6xl mx-auto p-8 pt-12">
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
            <div v-if="activeSectionStatus" class="ml-auto">
              <span
                class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border shadow-sm transition-all"
                :class="[
                  activeSectionStatus.toLowerCase() === 'completed' ||
                  activeSectionStatus.toLowerCase() === 'paid'
                    ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                    : 'bg-amber-50 text-amber-600 border-amber-100',
                ]"
              >
                {{ activeSectionStatus }}
              </span>
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
import ViewClientInformationTab from "./tracking-view-tabs/client-information/ViewClientInformationTab.vue";
import ViewDesigningTab from "./tracking-view-tabs/designing/ViewDesigningTab.vue";
import ViewOrderPrintingSection from "./tracking-view-tabs/printing/ViewOrderPrintingSection.vue";
import ViewPackagingTab from "./tracking-view-tabs/packaging/ViewPackagingTab.vue";
import ViewDispatchDeliveryTab from "./tracking-view-tabs/dispatch-delivery/ViewDispatchDeliveryTab.vue";
import ViewPaymentSection from "./tracking-view-tabs/payment/ViewPaymentSection.vue";

const props = defineProps({
  order: { type: Object, required: true },
});

const selectedSection = ref("client-information");
const completedSections = ref([]);

const trackingSections = [
  {
    id: "client-information",
    label: "Client Information",
    shortLabel: "Client",
    icon: UserIcon,
  },
  {
    id: "designing",
    label: "Designing",
    shortLabel: "Design",
    icon: SettingsIcon,
  },
  {
    id: "printing",
    label: "Printing",
    shortLabel: "Print",
    icon: PrinterIcon,
  },
  {
    id: "packaging",
    label: "Packaging",
    shortLabel: "Pack",
    icon: PackageIcon,
  },
  {
    id: "dispatch-delivery",
    label: "Dispatch & Delivery",
    shortLabel: "Disp",
    icon: TruckIcon,
  },
  {
    id: "payment",
    label: "Payments",
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
    case "client-information":
      return ViewClientInformationTab;
    case "designing":
      return ViewDesigningTab;
    case "printing":
      return ViewOrderPrintingSection;
    case "packaging":
      return ViewPackagingTab;
    case "dispatch-delivery":
      return ViewDispatchDeliveryTab;
    case "payment":
      return ViewPaymentSection;
    default:
      return ViewClientInformationTab;
  }
});

const activeSectionStatus = computed(() => {
  const order = props.order;
  switch (selectedSection.value) {
    case "client-information":
      return order.client_information?.status;
    case "designing":
      return order.designing?.status;
    case "printing":
      return order.printing?.status;
    case "packaging":
      return order.packaging?.status;
    case "dispatch-delivery":
      return order.dispatch_delivery?.status;
    case "payment":
      return order.payment_status;
    default:
      return null;
  }
});

const calculateCompletedStatus = () => {
  const order = props.order;
  const completed = [];

  // Client Information
  if (order.client_information?.status === "Completed") {
    completed.push("client-information");
  }

  // Designing
  if (order.designing?.status === "Completed") {
    completed.push("designing");
  }

  // Printing
  if (order.printing?.status === "Completed") {
    completed.push("printing");
  }

  // Packaging
  if (order.packaging?.status === "Completed") {
    completed.push("packaging");
  }

  // Dispatch & Delivery
  if (order.dispatch_delivery?.status === "Completed") {
    completed.push("dispatch-delivery");
  }

  // Payment
  if (order.payment_status === "paid") {
    completed.push("payment");
  }

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
