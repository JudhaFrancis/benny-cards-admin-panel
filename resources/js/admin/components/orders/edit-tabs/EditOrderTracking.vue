<template>
  <div class="space-y-6">
    <!-- Progress Overview -->
    <div class="flex flex-wrap gap-2">
      <button
        v-for="section in trackingSections"
        :key="section.id"
        @click="selectedSection = section.id"
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all duration-300 border"
        :class="[
          selectedSection === section.id
            ? 'bg-primary text-white border-primary shadow-lg shadow-primary/10 scale-105'
            : completedSections.includes(section.id)
              ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
              : 'bg-slate-50 text-slate-400 border-slate-100',
        ]"
      >
        <CheckIcon
          v-if="completedSections.includes(section.id)"
          class="h-3 w-3"
        />
        {{ section.label.split(" ")[0] }}
      </button>
    </div>

    <!-- Section Content -->
    <div
      class="bg-white rounded-[2rem] border border-slate-100 shadow-soft-xl overflow-hidden animate-in fade-in duration-500"
    >
      <!-- Section Header -->
      <div
        class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50"
      >
        <div class="flex items-center gap-4">
          <div class="p-2.5 rounded-2xl bg-primary/10">
            <component :is="activeSectionIcon" class="h-5 w-5 text-primary" />
          </div>
          <div>
            <h3 class="font-bold text-slate-900">{{ activeSectionLabel }}</h3>
            <p
              class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5"
            >
              Phase {{ activeSectionIndex + 1 }} of
              {{ trackingSections.length }}
            </p>
          </div>
        </div>
        <div
          v-if="completedSections.includes(selectedSection)"
          class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100"
        >
          <CheckIcon class="h-3 w-3" />
          <span class="text-[10px] font-bold uppercase tracking-wider"
            >Completed</span
          >
        </div>
      </div>

      <!-- Section Body -->
      <div class="p-8">
        <component
          :is="activeSectionComponent"
          :order="order"
          @update:order="(val) => $emit('update:order', val)"
        />
      </div>

      <!-- Section Footer -->
      <div
        class="px-8 py-5 border-t border-slate-50 bg-slate-50/30 flex justify-end gap-3"
      >
        <button
          @click="$emit('cancel')"
          class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all"
        >
          Cancel
        </button>
        <button
          @click="handleSaveSection"
          class="px-8 py-3 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-[0.98]"
        >
          Save Changes
        </button>
      </div>
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
import OrderDetailsSection from "./tracting-edit-taps/OrderDetailsSection.vue";
import ClientInfoSection from "./tracting-edit-taps/ClientInfoSection.vue";
import CardSpecsSection from "./tracting-edit-taps/CardSpecsSection.vue";
import WorkAssignSection from "./tracting-edit-taps/WorkAssignSection.vue";
import DesignPrintSection from "./tracting-edit-taps/DesignPrintSection.vue";
import OrderPrintingSection from "./tracting-edit-taps/OrderPrintingSection.vue";
import PackagingLogisticsSection from "./tracting-edit-taps/PackagingLogisticsSection.vue";
import PackagingStatusSection from "./tracting-edit-taps/PackagingStatusSection.vue";
import DeliveryLocationSection from "./tracting-edit-taps/DeliveryLocationSection.vue";
import DispatchModeSection from "./tracting-edit-taps/DispatchModeSection.vue";
import DispatchDetailsSection from "./tracting-edit-taps/DispatchDetailsSection.vue";
import PaymentSection from "./tracting-edit-taps/PaymentSection.vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order", "save", "cancel"]);

const selectedSection = ref("order-details");
const completedSections = ref([]);

const trackingSections = [
  { id: "order-details", label: "Order Details", icon: ClipboardListIcon },
  { id: "client-info", label: "Client Information", icon: UserIcon },
  { id: "card-specs", label: "Card Specifications", icon: CreditCardIcon },
  { id: "work-assign", label: "Work Assign Process", icon: BriefcaseIcon },
  {
    id: "design-print",
    label: "Design – Checked & Given to Print",
    icon: PrinterIcon,
  },
  { id: "order-printing", label: "Order & Printing Status", icon: PackageIcon },
  { id: "packaging-logistics", label: "Packaging & Logistics", icon: BoxIcon },
  { id: "packaging-status", label: "Packaging Status", icon: BoxIcon },
  { id: "delivery-location", label: "Delivery Location", icon: MapPinIcon },
  { id: "dispatch-mode", label: "Mode of Dispatch", icon: TruckIcon },
  { id: "dispatch-details", label: "Dispatch Details", icon: FileTextIcon },
  { id: "payment", label: "Payment", icon: DollarSignIcon },
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
  emit("save");
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
select:focus {
  outline: none;
}
</style>
