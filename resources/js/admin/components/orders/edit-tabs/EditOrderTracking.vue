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
          @click="handleSectionClick(section.id, index)"
          :disabled="!isSectionUnlocked(index)"
          class="flex-shrink-0 snap-start flex flex-col items-center gap-2 group min-w-[5rem] transition-opacity duration-300"
          :class="{
            'opacity-50 cursor-not-allowed': !isSectionUnlocked(index),
          }"
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
        class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-visible"
      >
        <!-- Section Header -->
        <div
          class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50 rounded-t-3xl"
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
            :staff-options="staffOptions"
            @update:order="(val) => $emit('update:order', val)"
          />
        </div>

        <!-- Section Footer -->
        <div
          class="px-8 py-5 border-t border-slate-50 bg-slate-50/30 flex justify-between items-center rounded-b-3xl"
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
              :disabled="isSaving || !!savingSectionId"
              class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95 text-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <Loader2Icon
                v-if="isSaving || !!savingSectionId"
                class="h-4 w-4 animate-spin"
              />
              <CheckIcon v-else class="h-4 w-4" />
              {{
                isSaving || !!savingSectionId
                  ? "Saving..."
                  : activeSectionIndex < trackingSections.length - 1
                    ? "Save & Next"
                    : "Save & Finish"
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
import { ref, computed, onMounted, watch } from "vue";
import { useToast } from "../../../composables/useToast";

const { error: toastError } = useToast();

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
  IndianRupee as IndianRupeeIcon,
  Check as CheckIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";
import axios from "axios";

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
const staffOptions = ref([]);

const fetchStaff = async () => {
  try {
    const response = await axios.get("/api/v1/users", {
      params: { per_page: 100 }, // No role_id filter here, we'll filter in JS
    });
    if (response.data.success) {
      staffOptions.value = response.data.data.data
        .filter((user) => {
          const roleName = user.role?.name?.toLowerCase() || '';
          return roleName !== 'user'; // Allow all roles EXCEPT 'user'
        })
        .map((user) => ({
          label: user.name,
          value: user.name,
        }));
    }
  } catch (e) {
    console.error("Failed to fetch staff", e);
  }
};

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
    icon: IndianRupeeIcon,
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

const sectionMap = {
  "order-details": "job_details",
  "client-info": "client_info",
  "card-specs": "card_specs",
  "work-assign": "work_assign",
  "design-print": "design_print",
  "order-printing": "printing_status",
  "packaging-logistics": "packaging_logistics",
  "packaging-status": "packaging_status",
  "delivery-location": "delivery_location",
  "dispatch-mode": "dispatch_mode",
  "dispatch-details": "dispatch_details",
  payment: "payment_info",
};

// Check if a section is unlocked (accessible)
// Check if a section is unlocked (accessible)
const isSectionUnlocked = (index) => {
  const sectionId = trackingSections[index].id;
  
  // First section is always unlocked
  if (index === 0) return true;

  // EXCEPTION: Payment section ONLY unlocks if its predecessor (Dispatch Details) is completed.
  // We don't auto-unlock it based on DB data alone.
  if (sectionId === 'payment') {
      const prevSectionId = trackingSections[index - 1].id;
      return completedSections.value.includes(prevSectionId);
  }

  // For all other sections (Smart Unlocking):
  // 1. Unlock if this section already has valid data in DB
  if (completedSections.value.includes(sectionId)) return true;
  
  // 2. Unlock if previous section is completed (to allow moving forward)
  const prevSectionId = trackingSections[index - 1].id;
  return completedSections.value.includes(prevSectionId);
};

// Handle Tab Clicks (Prevent access to locked sections)
const handleSectionClick = (sectionId, index) => {
  if (isSectionUnlocked(index)) {
    selectedSection.value = sectionId;
  }
};

// Helper to get validation errors for a specific section
const getSectionErrors = (sectionId) => {
  const tracking = props.order.tracking || {};
  let errors = [];

  switch (sectionId) {
    case "order-details":
      const details = tracking.job_details || {};
      if (!props.order.order_date) errors.push("Order Date");
      if (!details.order_taken_by) errors.push("Order Taken By");
      if (!details.order_placed_in) errors.push("Order Placed In");
      if (!details.reference) errors.push("Reference");
      break;

    case "client-info":
      const client = tracking.client_info || {};
      if (!client.name) errors.push("Name");
      if (!client.address) errors.push("Place (Address)");
      if (!client.phone) errors.push("Contact No");
      if (!client.occasion) errors.push("Occasion");
      if (!client.expected_delivery_date) errors.push("Expected Delivery Date");
      break;

    case "card-specs":
      const specs = tracking.card_specs || {};
      if (!specs.type) errors.push("Product Type (Customize or Ready Made)");
      if (!specs.card_size) errors.push("Card Size");
      if (!specs.quantity) errors.push("Quantity");
      if (!specs.specifications) errors.push("Specifications");
      // Inner GSM, Envelope GSM, Card Lamination, and Envelope Lamination are all optional
      break;

    case "work-assign":
      const work = tracking.work_assign || {};
      if (!work.assigned_to) errors.push("Assigned To");
      if (!work.assigned_date) errors.push("Assigned Date");
      if (!work.deadline) errors.push("Deadline");
      if (!work.content_by) errors.push("Content By");
      if (!work.completed_by) errors.push("Completed By");
      break;

    case "design-print":
      const design = tracking.design_print || {};
      if (!design.design_outputs)
        errors.push("Design Outputs (Select at least one)");
      if (!design.print_addons)
        errors.push("Print & Add-ons (Select at least one)");
      break;

    case "order-printing":
      const printing = tracking.printing_status || {};

      if (!printing.assigned_date) errors.push("Assigned Date");

      // Validate Customize section if any data is present
      const hasCustomizeData =
        printing.customize_sent_to_print_date ||
        printing.customize_delivery_date ||
        printing.customize_follow_up;

      if (hasCustomizeData) {
        if (!printing.customize_sent_to_print_date)
          errors.push("Sent to Print Date");
        if (!printing.customize_delivery_date) errors.push("Delivery Date");

        const followUpCount = printing.customize_follow_up
          ? printing.customize_follow_up.split(",").filter((d) => d).length
          : 0;
        if (followUpCount < 1)
          errors.push("Customize Card: At least Day 1 status must be checked");
      }

      // Validate Readymade section if any data is present
      const hasReadymadeData =
        printing.readymade_ordered ||
        printing.readymade_sub_received ||
        printing.readymade_sent_to_print ||
        printing.readymade_follow_up;

      if (hasReadymadeData) {
        const followUpCount = printing.readymade_follow_up
          ? printing.readymade_follow_up.split(",").filter((d) => d).length
          : 0;
        if (followUpCount < 1)
          errors.push("Readymade Card: At least Day 1 status must be checked");
      }

      // Fallback: If absolutely nothing is entered, but a card type is known,
      // it should at least validate that type if we want strictness.
      // But for now, let it be flexible as long as something is being tracked.
      break;

    case "packaging-logistics":
      const logistics = tracking.packaging_logistics || {};
      if (!logistics.crafted_by) errors.push("Crafted By");
      if (!logistics.names) errors.push("Names");
      if (!logistics.date) errors.push("Date");
      if (!logistics.qty_cards) errors.push("Qty of Cards");
      if (!logistics.logistics_details)
        errors.push("Envelope / Ribbon / Tag / Sticker");
      break;

    case "packaging-status":
      const packing = tracking.packaging_status || {};
      if (!packing.packed_by) errors.push("Packed By");
      break;

    case "delivery-location":
      const loc = tracking.delivery_location || {};
      if (!loc.place_name) errors.push("Place Name");
      break;

    case "dispatch-mode":
      const dispMode = tracking.dispatch_mode || {};
      if (!dispMode.date) errors.push("Dispatch Details with Date");
      if (!dispMode.expense) errors.push("Dispatch Expense");
      if (!dispMode.signature_name) errors.push("Signature & Name");
      break;

    case "dispatch-details":
      const dispDet = tracking.dispatch_details || {};
      const modesStr = tracking.dispatch_mode?.modes || "";
      const modes = modesStr.split(",");

      if (modes.includes("Bus")) {
        const bus = dispDet.bus || {};
        if (!bus.bus_no) errors.push("Bus No");
        if (!bus.reaching_time) errors.push("Bus Reaching Time");
        if (!bus.contact_no) errors.push("Bus Contact No");
      }
      if (modes.includes("Courier")) {
        const courier = dispDet.courier || {};
        if (!courier.name) errors.push("Courier Name");
        if (!courier.tracking_no) errors.push("Courier Tracking No");
      }
      if (modes.includes("Transport")) {
        const transport = dispDet.transport || {};
        if (!transport.name) errors.push("Transport Name");
        if (!transport.lr_number) errors.push("Transport LR Number");
      }
      break;

    case "payment":
      const paymentInfo = tracking.payment_info || {};
      const payments = Array.isArray(paymentInfo)
        ? paymentInfo
        : paymentInfo.payments || [];

      if (!Array.isArray(payments) || payments.length === 0) {
        errors.push("At least one payment record is required");
      } else {
        payments.forEach((p, i) => {
          const prefix = `Payment ${i + 1}: `;
          if (!p.payment_method) errors.push(prefix + "Payment Via");
          if (!p.payment_date) errors.push(prefix + "Payment Date");
          if (!p.amount) errors.push(prefix + "Amount");
          if (!p.signature_name) errors.push(prefix + "Signature & Name");
        });
      }
      break;
  }

  return errors;
};

const savingSectionId = ref(null);

// Initialize completed sections based on existing data
const calculateCompletedStatus = () => {
  const tracking = props.order.tracking || {};
  const filled = [];

  for (const section of trackingSections) {
    const dbCol = sectionMap[section.id];
    const sectionData = tracking[dbCol];

    if (
      sectionData && 
      (sectionData._audit || Object.keys(sectionData).length > 0) &&
      getSectionErrors(section.id).length === 0
    ) {
      filled.push(section.id);
    }
  }
  return filled;
};

const initCompletedSections = () => {
  const filled = calculateCompletedStatus();
  completedSections.value = filled;

  // Auto-select the first incomplete section
  const firstIncomplete = trackingSections.find((s) => !filled.includes(s.id));
  if (firstIncomplete) {
    selectedSection.value = firstIncomplete.id;
  } else {
    selectedSection.value = trackingSections[trackingSections.length - 1].id;
  }
};

// Watch for tracking updates from parent (after a save)
watch(
  () => props.order.tracking,
  (newTracking) => {
    const newlyFilled = calculateCompletedStatus();
    const oldFilledCount = completedSections.value.length;
    completedSections.value = newlyFilled;

    // If we just finished a save for a section
    if (savingSectionId.value) {
      const isNowCompleted = newlyFilled.includes(savingSectionId.value);

      if (isNowCompleted) {
        const currentIndex = trackingSections.findIndex(
          (s) => s.id === savingSectionId.value,
        );

        // Auto-advance if not the last section
        if (currentIndex < trackingSections.length - 1) {
          selectedSection.value = trackingSections[currentIndex + 1].id;
        } else {
          // If it was the last section (Payment), emit success to close modal
          emit("success");
        }
      }
      // Reset saving state
      savingSectionId.value = null;
    }
  },
  { deep: true },
);

onMounted(() => {
  initCompletedSections();
  fetchStaff();
});

// Defensive fix for Laravel returning [] for empty JSON objects
watch(() => props.order?.tracking, (newTracking) => {
  if (newTracking && typeof newTracking === 'object') {
    Object.keys(sectionMap).forEach(key => {
      const dbCol = sectionMap[key];
      if (Array.isArray(newTracking[dbCol]) && newTracking[dbCol].length === 0) {
        newTracking[dbCol] = {};
      }
    });
  }
}, { immediate: true, deep: true });

const handleSaveSection = () => {
  const errors = getSectionErrors(selectedSection.value);

  if (errors.length > 0) {
    toastError("Please fill in the required fields:\n- " + errors.join("\n- "));
    return;
  }

  // Set saving state
  savingSectionId.value = selectedSection.value;

  // Emit Save
  const dbColumn = sectionMap[selectedSection.value];
  if (dbColumn) {
    const trackingData = props.order.tracking || {};
    const sectionData = trackingData[dbColumn] || {};
    emit("save", { [dbColumn]: sectionData });
  } else {
    emit("save");
  }
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
