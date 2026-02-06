<template>
  <div class="space-y-8">
    <!-- Progress Timeline Header -->
    <div class="relative px-2">
      <div
        class="flex gap-3 overflow-x-auto custom-scrollbar pb-4 -mx-2 px-2 snap-x"
      >
        <button
          v-for="(section, index) in trackingSections"
          :key="section.id"
          @click="selectedSection = section.id"
          class="flex-shrink-0 snap-start flex flex-col items-center gap-2 group min-w-[5rem]"
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 shadow-sm border"
            :class="[
              selectedSection === section.id
                ? 'bg-primary text-white border-primary shadow-primary/30 scale-110 z-10'
                : completedSections.includes(section.id)
                  ? 'bg-emerald-50 text-emerald-600 border-emerald-200'
                  : 'bg-white text-slate-400 border-slate-100 group-hover:border-primary/30 group-hover:text-primary',
            ]"
          >
            <component :is="section.icon" class="h-5 w-5" />
          </div>
          <span
            class="text-[10px] font-bold uppercase tracking-wider text-center max-w-[5rem] leading-tight transition-colors"
            :class="
              selectedSection === section.id ? 'text-primary' : 'text-slate-400'
            "
          >
            {{ section.shortLabel || section.label }}
          </span>
          <div
            v-if="completedSections.includes(section.id)"
            class="absolute top-0 right-3 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"
          ></div>
        </button>
      </div>
    </div>

    <!-- Active Section Content Card -->
    <Transition
      mode="out-in"
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        :key="selectedSection"
        class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden"
      >
        <!-- Section Header -->
        <div
          class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50"
        >
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-2xl bg-primary/5 text-primary">
              <component :is="activeSectionIcon" class="h-6 w-6" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ activeSectionLabel }}
              </h3>
              <p
                class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-1"
              >
                Step {{ activeSectionIndex + 1 }} of
                {{ trackingSections.length }}
              </p>
            </div>
          </div>

          <div
            v-if="completedSections.includes(selectedSection)"
            class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100"
          >
            <CheckIcon class="h-4 w-4" />
            <span class="text-xs font-black uppercase tracking-wider"
              >Completed</span
            >
          </div>
        </div>

        <!-- Section Body (Dynamic Component) -->
        <div class="p-8 min-h-[300px]">
          <component
            :is="activeSectionComponent"
            :order="order"
            @update:order="(val) => $emit('update:order', val)"
          />
        </div>

        <!-- Section Footer -->
        <div
          class="px-8 py-5 border-t border-slate-50 bg-slate-50/30 flex justify-between items-center"
        >
          <button
            v-if="activeSectionIndex > 0"
            @click="selectPreviousSection"
            class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-2"
          >
            ← Previous Step
          </button>
          <div v-else></div>
          <!-- Spacer -->

          <div class="flex gap-3">
            <button
              @click="handleSaveSection"
              class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95 text-sm flex items-center gap-2"
            >
              <CheckIcon class="h-4 w-4" />
              Save &
              {{
                activeSectionIndex < trackingSections.length - 1
                  ? "Next"
                  : "Finish"
              }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Footer Actions (Global Cancel/Save) -->
    <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
      <button
        @click="$emit('cancel')"
        class="px-8 py-3.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all active:scale-95 text-sm"
      >
        Close Editor
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import {
  ClipboardList as ClipboardListIcon,
  User as UserIcon,
  CreditCard as CreditCardIcon,
  Briefcase as BriefcaseIcon,
  Printer as PrinterIcon,
  Package as PackageIcon,
  Box as BoxIcon,
  MapPin as MapPinIcon,
  Truck as TruckIcon,
  FileText as FileTextIcon,
  DollarSign as DollarSignIcon,
  Check as CheckIcon,
} from "lucide-vue-next";

// Import tracking section components
import OrderDetailsSection from "./tracking-edit-tabs/OrderDetailsSection.vue";
import ClientInfoSection from "./tracking-edit-tabs/ClientInfoSection.vue";
import CardSpecsSection from "./tracking-edit-tabs/CardSpecsSection.vue";
import WorkAssignSection from "./tracking-edit-tabs/WorkAssignSection.vue";
import DesignPrintSection from "./tracking-edit-tabs/DesignPrintSection.vue";
import OrderPrintingSection from "./tracking-edit-tabs/OrderPrintingSection.vue";
import PackagingLogisticsSection from "./tracking-edit-tabs/PackagingLogisticsSection.vue";
import PackagingStatusSection from "./tracking-edit-tabs/PackagingStatusSection.vue";
import DeliveryLocationSection from "./tracking-edit-tabs/DeliveryLocationSection.vue";
import DispatchModeSection from "./tracking-edit-tabs/DispatchModeSection.vue";
import DispatchDetailsSection from "./tracking-edit-tabs/DispatchDetailsSection.vue";
import PaymentSection from "./tracking-edit-tabs/PaymentSection.vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order", "save", "cancel"]);

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
    label: "Work Assign Process",
    shortLabel: "Assign",
    icon: BriefcaseIcon,
  },
  {
    id: "design-print",
    label: "Design – Checked & Given to Print",
    shortLabel: "Design",
    icon: PrinterIcon,
  },
  {
    id: "order-printing",
    label: "Order & Printing Status",
    shortLabel: "Printing",
    icon: PackageIcon,
  },
  {
    id: "packaging-logistics",
    label: "Packaging & Logistics",
    shortLabel: "Logistics",
    icon: BoxIcon,
  },
  {
    id: "packaging-status",
    label: "Packaging Status",
    shortLabel: "Packing",
    icon: BoxIcon,
  },
  {
    id: "delivery-location",
    label: "Delivery Location",
    shortLabel: "Location",
    icon: MapPinIcon,
  },
  {
    id: "dispatch-mode",
    label: "Mode of Dispatch",
    shortLabel: "Dispatch",
    icon: TruckIcon,
  },
  {
    id: "dispatch-details",
    label: "Dispatch Details",
    shortLabel: "Details",
    icon: FileTextIcon,
  },
  {
    id: "payment",
    label: "Payment",
    shortLabel: "Payment",
    icon: DollarSignIcon,
  },
];

const activeSectionLabel = computed(() => {
  return (
    trackingSections.find((s) => s.id === selectedSection.value)?.label || ""
  );
});

const activeSectionIcon = computed(() => {
  return (
    trackingSections.find((s) => s.id === selectedSection.value)?.icon ||
    ClipboardListIcon
  );
});

const activeSectionIndex = computed(() => {
  return trackingSections.findIndex((s) => s.id === selectedSection.value);
});

const handleSaveSection = () => {
  if (!completedSections.value.includes(selectedSection.value)) {
    completedSections.value.push(selectedSection.value);
  }

  // Auto-advance
  if (activeSectionIndex.value < trackingSections.length - 1) {
    selectedSection.value = trackingSections[activeSectionIndex.value + 1].id;
  }

  emit("save");
};

const selectPreviousSection = () => {
  if (activeSectionIndex.value > 0) {
    selectedSection.value = trackingSections[activeSectionIndex.value - 1].id;
  }
};

const activeSectionComponent = computed(() => {
  switch (selectedSection.value) {
    case "order-details":
      return OrderDetailsSection;
    case "client-info":
      return ClientInfoSection;
    case "card-specs":
      return CardSpecsSection;
    case "work-assign":
      return WorkAssignSection;
    case "design-print":
      return DesignPrintSection;
    case "order-printing":
      return OrderPrintingSection;
    case "packaging-logistics":
      return PackagingLogisticsSection;
    case "packaging-status":
      return PackagingStatusSection;
    case "delivery-location":
      return DeliveryLocationSection;
    case "dispatch-mode":
      return DispatchModeSection;
    case "dispatch-details":
      return DispatchDetailsSection;
    case "payment":
      return PaymentSection;
    default:
      return OrderDetailsSection;
  }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
