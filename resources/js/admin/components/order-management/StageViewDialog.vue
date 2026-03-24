<template>
  <InfoModal 
    :is-open="isOpen" 
    :title="`Order #${orderData?.order_number || ''}`" 
    :icon="Package"
    max-width="xl"
    @close="$emit('close')"
  >
    <template #header-extra>
      <span class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2.5 py-1 rounded-lg border border-white/10 backdrop-blur-sm shadow-sm">
        <Activity class="h-3.5 w-3.5" />
        {{ stageStatus }}
      </span>
      <span class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2.5 py-1 rounded-lg border border-white/10 backdrop-blur-sm shadow-sm">
        <Calendar class="h-3.5 w-3.5" />
        {{ formatDate(orderData?.order_date) }}
      </span>
    </template>

    <div v-if="loading" class="flex items-center justify-center p-12">
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
    </div>

    <div v-else-if="orderData" class="space-y-6 px-1">

      <div v-for="section in relevantSections" :key="section.id" class="bg-slate-50/50 rounded-3xl border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3 bg-white/50">
          <div class="p-2 rounded-xl bg-primary/5 text-primary">
            <component :is="section.icon" class="h-5 w-5" />
          </div>
          <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ section.label }}</h3>
        </div>
        <div class="p-6">
          <component :is="section.component" :order="orderData" readonly :hide-audit="true" />
        </div>
      </div>

      <!-- Audit Row -->
      <div v-if="orderData && auditDetails" class="flex flex-wrap items-center justify-between bg-slate-50/50 p-4 rounded-2xl border border-slate-100 mt-2 gap-4">
        <div class="flex items-center gap-2 px-3 py-1.5 bg-white rounded-xl border border-slate-200 shadow-sm">
          <Activity class="h-3.5 w-3.5 text-slate-400" />
          <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100 pr-2 mr-1">Status</span>
          <span 
            :class="[
              'text-xs font-bold transition-colors',
              stageStatus === 'Completed' ? 'text-emerald-600' :
              stageStatus === 'Process' ? 'text-blue-600' :
              'text-amber-600'
            ]"
          >
            {{ stageStatus === 'Process' ? 'Processing' : stageStatus }}
          </span>
        </div>

        <div v-if="auditDetails" class="text-xs text-slate-400 flex items-center gap-2 justify-end">
          <Clock class="h-3.5 w-3.5" />
          <span>Last updated</span>
          <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600">
            {{ formatAuditDate(auditDetails.updated_at || auditDetails.created_at) }}
          </span>
          <span>by</span>
          <span class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2">
            {{ auditDetails.modified_by?.name || auditDetails.added_by?.name || "System" }}
          </span>
        </div>
      </div>
    </div>
  </InfoModal>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Loader2, ClipboardList, User, CreditCard, Briefcase, Printer, Package, Box, MapPin, Truck, FileText, Activity, Calendar, Clock, User as UserIcon } from "lucide-vue-next";
import axios from "axios";
import InfoModal from "../ui/InfoModal.vue";

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric"
  });
};

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const d = date.toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
  const t = date.toLocaleTimeString("en-GB", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  }).toUpperCase();
  return `${d} at ${t}`;
};

// Section Components (View only)
import ViewOrderDetailsSection from "../orders/info-tabs/tracking-view-tabs/ViewOrderDetailsSection.vue";
import ViewClientInfoSection from "../orders/info-tabs/tracking-view-tabs/ViewClientInfoSection.vue";
import ViewCardSpecsSection from "../orders/info-tabs/tracking-view-tabs/ViewCardSpecsSection.vue";
import ViewWorkAssignSection from "../orders/info-tabs/tracking-view-tabs/ViewWorkAssignSection.vue";
import ViewDesignPrintSection from "../orders/info-tabs/tracking-view-tabs/ViewDesignPrintSection.vue";
import ViewOrderPrintingSection from "../orders/info-tabs/tracking-view-tabs/ViewOrderPrintingSection.vue";
import ViewPackagingLogisticsSection from "../orders/info-tabs/tracking-view-tabs/ViewPackagingLogisticsSection.vue";
import ViewPackagingStatusSection from "../orders/info-tabs/tracking-view-tabs/ViewPackagingStatusSection.vue";
import ViewDeliveryLocationSection from "../orders/info-tabs/tracking-view-tabs/ViewDeliveryLocationSection.vue";
import ViewDispatchModeSection from "../orders/info-tabs/tracking-view-tabs/ViewDispatchModeSection.vue";
import ViewDispatchDetailsSection from "../orders/info-tabs/tracking-view-tabs/ViewDispatchDetailsSection.vue";

const props = defineProps({
  isOpen: Boolean,
  orderId: [Number, String],
  stage: String
});

defineEmits(["close"]);

const orderData = ref(null);
const loading = ref(false);

const stageStatus = computed(() => {
  const stageMap = {
    'client-information': 'client_information',
    'designing': 'designing',
    'printing': 'printing',
    'packaging': 'packaging',
    'delivery': 'dispatch_delivery'
  };
  const key = stageMap[props.stage];
  return orderData.value?.[key]?.status || "Pending";
});

const stageRelationKey = computed(() => {
  switch (props.stage) {
    case 'client-information': return 'client_information';
    case 'designing': return 'designing';
    case 'printing': return 'printing';
    case 'packaging': return 'packaging';
    case 'delivery': return 'dispatch_delivery';
    default: return null;
  }
});

const auditDetails = computed(() => {
  return orderData.value?.[stageRelationKey.value];
});

const stageTitle = computed(() => {
  switch (props.stage) {
    case 'client-information': return 'Client Information';
    case 'designing': return 'Designing';
    case 'printing': return 'Printing';
    case 'packaging': return 'Packaging';
    case 'delivery': return 'Dispatch & Delivery';
    default: return 'Order Summary';
  }
});

const relevantSections = computed(() => {
  switch (props.stage) {
    case 'client-information':
      return [
        { id: 'details', label: 'Order Details', icon: ClipboardList, component: ViewOrderDetailsSection },
        { id: 'client', label: 'Client Information', icon: User, component: ViewClientInfoSection },
        { id: 'specs', label: 'Card Specifications', icon: CreditCard, component: ViewCardSpecsSection }
      ];
    case 'designing':
      return [
        { id: 'assign', label: 'Work Assign Process', icon: Briefcase, component: ViewWorkAssignSection },
        { id: 'design', label: 'Design Details', icon: Printer, component: ViewDesignPrintSection }
      ];
    case 'printing':
      return [
        { id: 'printing', label: 'Order & Printing Status', icon: Package, component: ViewOrderPrintingSection }
      ];
    case 'packaging':
      return [
        { id: 'logistics', label: 'Packaging & Logistics', icon: Box, component: ViewPackagingLogisticsSection },
        { id: 'status', label: 'Packaging Status', icon: Box, component: ViewPackagingStatusSection }
      ];
    case 'delivery':
      return [
        { id: 'location', label: 'Delivery Location', icon: MapPin, component: ViewDeliveryLocationSection },
        { id: 'dispatch', label: 'Mode of Dispatch', icon: Truck, component: ViewDispatchModeSection },
        { id: 'details', label: 'Dispatch Details', icon: FileText, component: ViewDispatchDetailsSection }
      ];
    default: return [];
  }
});

const fetchOrder = async () => {
  if (!props.orderId) return;
  loading.value = true;
  try {
    const response = await axios.get(`/api/v1/orders/${props.orderId}`);
    if (response.data.success) {
      orderData.value = response.data.data;
    }
  } catch (error) {
    console.error("Error fetching order:", error);
  } finally {
    loading.value = false;
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) fetchOrder();
});
</script>
