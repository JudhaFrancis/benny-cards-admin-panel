<template>
  <div class="space-y-8">
    <!-- Progress Timeline Header -->
    <div class="relative px-2">
      <div class="flex gap-3 overflow-x-auto custom-scrollbar pb-4 -mx-2 px-2 snap-x">
        <button v-for="(section, index) in trackingSections" :key="section.id"
          @click="handleSectionClick(section.id, index)" :disabled="!isSectionUnlocked(index)"
          class="flex-shrink-0 snap-start flex flex-col items-center gap-2 group min-w-[5rem] transition-opacity duration-300"
          :class="{
            'opacity-50 cursor-not-allowed': !isSectionUnlocked(index),
          }">
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 shadow-sm border"
            :class="[
              selectedSection === section.id
                ? 'bg-primary text-white border-primary shadow-primary/30 scale-110 z-10'
                : completedSections.includes(section.id)
                  ? 'bg-emerald-50 text-emerald-600 border-emerald-200'
                  : 'bg-white text-slate-400 border-slate-100 group-hover:border-primary/30 group-hover:text-primary',
            ]">
            <component :is="section.icon" class="h-5 w-5" />
          </div>
          <span
            class="text-[10px] font-bold uppercase tracking-wider text-center max-w-[5rem] leading-tight transition-colors"
            :class="selectedSection === section.id ? 'text-primary' : 'text-slate-400'
              ">
            {{ section.shortLabel || section.label }}
          </span>
          <div v-if="completedSections.includes(section.id)"
            class="absolute top-0 right-3 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></div>
        </button>
      </div>
    </div>

    <!-- Active Section Content Card -->
    <Transition mode="out-in" enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2">
      <div :key="selectedSection"
        class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-visible">
        <!-- Section Header -->
        <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50 rounded-t-3xl">
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-2xl bg-primary/5 text-primary">
              <component :is="activeSectionIcon" class="h-6 w-6" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ activeSectionLabel }}
              </h3>
              <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-1">
                Step {{ activeSectionIndex + 1 }} of
                {{ trackingSections.length }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-2xl bg-white border border-slate-200 shadow-sm">
              <ActivityIcon class="h-3.5 w-3.5 text-slate-400" />
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-r border-slate-100 pr-2 mr-1">Status</span>
              <select 
                :value="currentStageStatus"
                @change="handleStatusChange($event.target.value)"
                class="text-xs font-bold bg-transparent border-none focus:ring-0 cursor-pointer pr-8"
                :class="statusStyles[currentStageStatus.toLowerCase()] || 'text-slate-600'"
              >
                <option value="Pending">Pending</option>
                <option value="Process">Processing</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>

            <div v-if="completedSections.includes(selectedSection)"
              class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
              <CheckIcon class="h-4 w-4" />
              <span class="text-xs font-black uppercase tracking-wider">Filled</span>
            </div>
          </div>
        </div>

        <!-- Section Body (Dynamic Component) -->
        <div class="p-8 min-h-[300px]">
          <component :is="activeSectionComponent" :order="order" :staff-options="staffOptions"
            @update:order="(val) => $emit('update:order', val)" />
        </div>

        <!-- Section Footer -->
        <div class="px-8 py-5 border-t border-slate-50 bg-slate-50/30 flex justify-between items-center rounded-b-3xl">
          <button v-if="activeSectionIndex > 0" @click="selectPreviousSection"
            class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-2">
            ← Previous Step
          </button>
          <div v-else></div>
          <!-- Spacer -->

          <div class="flex gap-3">
            <button @click="handleSaveSection" :disabled="isSaving || !!savingSectionId"
              class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95 text-sm flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
              <Loader2Icon v-if="isSaving || !!savingSectionId" class="h-4 w-4 animate-spin" />
              <CheckIcon v-else class="h-4 w-4" />
              {{
                isSaving || !!savingSectionId
                  ? "Saving..."
                  : activeSectionIndex < trackingSections.length - 1 ? "Save & Next" : "Save & Finish" }} </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Footer Actions (Global Cancel/Save) -->
    <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
      <button @click="$emit('cancel')"
        class="px-8 py-3.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all active:scale-95 text-sm">
        Close Editor
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import { useToast } from "../../../composables/useToast";
import { useOrderValidation } from "../../../composables/useOrderValidation";
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
  Activity as ActivityIcon,
} from "lucide-vue-next";

// Import tracking section components
import OrderDetailsSection from "./tracking-edit-tabs/client-information/OrderDetailsSection.vue";
import ClientInfoSection from "./tracking-edit-tabs/client-information/ClientInfoSection.vue";
import CardSpecsSection from "./tracking-edit-tabs/client-information/CardSpecsSection.vue";
import WorkAssignSection from "./tracking-edit-tabs/designing/WorkAssignSection.vue";
import DesignPrintSection from "./tracking-edit-tabs/designing/DesignPrintSection.vue";
import OrderPrintingSection from "./tracking-edit-tabs/printing/OrderPrintingSection.vue";
import PackagingLogisticsSection from "./tracking-edit-tabs/packaging/PackagingLogisticsSection.vue";
import PackagingStatusSection from "./tracking-edit-tabs/packaging/PackagingStatusSection.vue";
import DeliveryLocationSection from "./tracking-edit-tabs/dispatch-delivery/DeliveryLocationSection.vue";
import DispatchModeSection from "./tracking-edit-tabs/dispatch-delivery/DispatchModeSection.vue";
import DispatchDetailsSection from "./tracking-edit-tabs/dispatch-delivery/DispatchDetailsSection.vue";
import PaymentSection from "./tracking-edit-tabs/payment/PaymentSection.vue";

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

const { getSectionErrors, validateStage, trackingSections, sectionMap } = useOrderValidation();
const { error: toastError, success: toastSuccess } = useToast();

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

// trackingSections moved to composable

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

const currentStageStatus = computed(() => {
  const config = sectionMap[selectedSection.value];
  if (!config) return "Pending";
  const stageData = props.order[config.relation];
  return stageData?.status || "Pending";
});

const statusStyles = {
  pending: "text-amber-500",
  process: "text-blue-500",
  completed: "text-emerald-500",
  cancelled: "text-rose-500",
};

// sectionMap moved to composable

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
// getSectionErrors removed, now using composable version

const savingSectionId = ref(null);

// Initialize completed sections based on existing data
const calculateCompletedStatus = () => {
  const filled = [];

  for (const section of trackingSections) {
    const config = sectionMap[section.id];
    const sectionData = config.isArray
      ? props.order[config.relation]
      : props.order[config.relation]?.[config.key];

    if (
      sectionData &&
      (Array.isArray(sectionData) ? sectionData.length > 0 : (sectionData._audit || Object.keys(sectionData).length > 0)) &&
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
  () => props.order,
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
watch(() => props.order, (newOrder) => {
  if (newOrder && typeof newOrder === 'object') {
    Object.keys(sectionMap).forEach(key => {
      const config = sectionMap[key];
      if (config.isArray) return;

      // 1. Ensure the relation exists as an object
      if (!newOrder[config.relation] || Array.isArray(newOrder[config.relation])) {
        newOrder[config.relation] = {};
      }

      // 2. Ensure the key inside the relation exists as an object
      const relation = newOrder[config.relation];
      if (!relation[config.key] || Array.isArray(relation[config.key])) {
        relation[config.key] = {};
      }
    });
  }
}, { immediate: true, deep: true });

const handleSaveSection = () => {
  const errors = getSectionErrors(selectedSection.value, props.order);

  if (errors.length > 0) {
    toastError("Please fill in the required fields:\n- " + errors.join("\n- "));
    return;
  }

  // Set saving state
  savingSectionId.value = selectedSection.value;

  // Emit Save
  const config = sectionMap[selectedSection.value];
  if (config) {
    if (config.isArray) {
      emit("save", { [config.relation]: props.order[config.relation] });
    } else {
      const sectionData = props.order[config.relation]?.[config.key] || {};
      emit("save", { [config.key]: sectionData });
    }
  } else {
    emit("save");
  }
};

const handleStatusChange = (newStatus) => {
  const config = sectionMap[selectedSection.value];
  if (!config) return;

  if (newStatus === "Completed") {
    const allErrors = validateStage(config.relation, props.order, sectionMap, trackingSections);

    if (allErrors.length > 0) {
      toastError("Cannot complete stage. Please fill mandatory fields:\n- " + allErrors.join("\n- "));
      return;
    }
  }

  // Emit Save with status update
  savingSectionId.value = selectedSection.value;
  emit("save", { status: newStatus, _stage: config.relation });
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
