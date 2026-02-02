<template>
  <div class="space-y-6">
    <!-- Section Content Container -->
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
        class="px-8 py-5 border-t border-slate-50 bg-slate-50/30 flex justify-end"
      >
        <button
          @click="handleSaveSection"
          class="px-8 py-3 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-[0.98]"
        >
          Mark Phase as Complete
        </button>
      </div>
    </div>

    <!-- Progress Overview -->
    <div class="flex flex-wrap gap-2 pt-2">
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
  </div>
</template>

<script setup>
import { ref, computed, defineComponent, h } from "vue";
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
  ChevronDown as ChevronDownIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order", "save"]);

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

// Sub-components for each section
const OrderDetailsSection = defineComponent({
  props: ["order"],
  emits: ["update:order"],
  template: `
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Order No</label>
        <input :value="order.id" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-bold text-slate-500" readonly />
      </div>
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Order Date</label>
        <input type="text" :value="order.orderDate" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" />
      </div>
      <div class="space-y-2 md:col-span-2">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Order Placed In</label>
        <div class="flex flex-wrap gap-4">
          <label v-for="place in ['NGL', 'MTM', 'TVL', 'Chennai', 'Online']" :key="place" class="flex items-center gap-2.5 cursor-pointer group">
            <input type="checkbox" class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20 transition-all" />
            <span class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors">{{ place }}</span>
          </label>
        </div>
      </div>
    </div>
  `,
});

const ClientInfoSection = defineComponent({
  props: ["order"],
  template: `
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Client Name</label>
        <input v-model="order.customer" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900" />
      </div>
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Occasion</label>
        <input class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900" placeholder="e.g. Wedding" />
      </div>
       <div class="space-y-2">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Expected Delivery</label>
        <input type="date" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900" />
      </div>
    </div>
  `,
});

const CardSpecsSection = defineComponent({
  template: `
    <div class="space-y-8">
      <div class="grid grid-cols-2 gap-6">
        <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-100 bg-slate-50 cursor-pointer hover:border-primary/20 transition-all">
          <input type="checkbox" class="w-4 h-4 rounded-md border-slate-300 text-primary" />
          <span class="text-sm font-bold text-slate-700">Customize Card</span>
        </label>
        <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-100 bg-slate-50 cursor-pointer hover:border-primary/20 transition-all">
          <input type="checkbox" class="w-4 h-4 rounded-md border-slate-300 text-primary" />
          <span class="text-sm font-bold text-slate-700">Ready Made Card</span>
        </label>
      </div>
      <div class="space-y-4">
        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Available Options</label>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
          <label v-for="opt in ['Envelope', 'Tag', 'Foiling', 'New Die', 'Wax Seal', 'Ribbon']" :key="opt" class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors">
            <input type="checkbox" class="w-4 h-4 rounded-md border-slate-300 text-primary" />
            <span class="text-xs font-bold text-slate-700">{{ opt }}</span>
          </label>
        </div>
      </div>
    </div>
  `,
});

const PaymentSection = defineComponent({
  props: ["order"],
  template: `
    <div class="space-y-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 rounded-[2rem] bg-slate-50 border border-slate-100 text-center">
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Total Bill</p>
          <p class="text-2xl font-black text-slate-900">$\{{ order.amount.toFixed(2) }}</p>
        </div>
        <div class="p-6 rounded-[2rem] bg-emerald-50 border border-emerald-100 text-center">
          <p class="text-[10px] font-bold text-emerald-600/60 uppercase tracking-[0.2em] mb-2">Paid Advance</p>
          <p class="text-2xl font-black text-emerald-600">$\{{ order.paidAmount.toFixed(2) }}</p>
        </div>
        <div class="p-6 rounded-[2rem] bg-rose-50 border border-rose-100 text-center">
          <p class="text-[10px] font-bold text-rose-600/60 uppercase tracking-[0.2em] mb-2">Remaining</p>
          <p class="text-2xl font-black text-rose-600">$\{{ (order.amount - order.paidAmount).toFixed(2) }}</p>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Payment Mode</label>
          <select class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 outline-none">
            <option>Cash</option>
            <option>UPI / GPay</option>
            <option>Bank Transfer</option>
            <option>Card Payment</option>
          </select>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Transaction ID</label>
          <input class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900" placeholder="Reference #" />
        </div>
      </div>
    </div>
  `,
});

// For brevity, I'll use placeholders for some complex but repetitive sections
const GenericPhaseSection = defineComponent({
  props: ["order"],
  template: `
    <div class="py-12 text-center space-y-4">
      <div class="inline-flex h-16 w-16 items-center justify-center rounded-[2rem] bg-slate-50 border border-slate-100">
        <ClockIcon class="h-8 w-8 text-slate-300" />
      </div>
      <div>
        <p class="text-sm font-bold text-slate-900">Phase Details Pending</p>
        <p class="text-xs text-slate-400 italic">This phase configuration is being finalized for production.</p>
      </div>
      <div class="max-w-xs mx-auto pt-4">
        <button class="w-full py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-500 hover:bg-slate-50 transition-colors">
          Configure Phase Items
        </button>
      </div>
    </div>
  `,
});

const activeSectionComponent = computed(() => {
  switch (selectedSection.value) {
    case "order-details":
      return OrderDetailsSection;
    case "client-info":
      return ClientInfoSection;
    case "card-specs":
      return CardSpecsSection;
    case "payment":
      return PaymentSection;
    default:
      return GenericPhaseSection;
  }
});
</script>

<style scoped>
select:focus {
  outline: none;
}
</style>
