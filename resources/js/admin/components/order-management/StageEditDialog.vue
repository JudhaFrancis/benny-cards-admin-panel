<template>
  <InfoModal 
    :is-open="isOpen" 
    :title="`Edit Order #${orderData?.order_number || ''}`" 
    :icon="Pencil"
    max-width="xl"
    @close="$emit('close')"
  >
    <template #header-extra>
      <span class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2.5 py-1.5 rounded-lg border border-white/10 backdrop-blur-sm shadow-sm">
        <Calendar class="h-3.5 w-3.5" />
        {{ formatDate(orderData?.order_date) }}
      </span>
      <span class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2.5 py-1.5 rounded-lg border border-white/10 backdrop-blur-sm shadow-sm">
        <Box class="h-3.5 w-3.5" />
        {{ orderData?.tracking_number || "No Tracking ID" }}
      </span>
    </template>
    <div v-if="loading" class="flex items-center justify-center p-12">
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
    </div>

    <div v-else-if="orderData" class="space-y-6 px-1 pb-6">
      <div v-for="section in relevantSections" :key="section.id" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50 flex items-center gap-3 bg-slate-50/30">
          <div class="p-2 rounded-xl bg-primary/5 text-primary">
            <component :is="section.icon" class="h-5 w-5" />
          </div>
          <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ section.label }}</h3>
        </div>
        <div class="p-6">
          <component 
            :is="section.component" 
            :order="orderData" 
            :staff-options="staffOptions"
            :hide-audit="true"
            @update:order="(val) => (orderData = val)"
          />
        </div>
      </div>

      <!-- Compact Status & Audit Row -->
      <div v-if="orderData" class="flex flex-wrap items-center justify-between bg-slate-50/50 p-4 rounded-2xl border border-slate-100 mt-2 gap-4">
        <!-- Status Selector (Premium Dropdown) -->
        <Listbox v-model="stageStatus">
          <div class="relative">
            <ListboxButton class="flex items-center gap-2 px-3 py-1.5 bg-white rounded-xl border border-slate-200 shadow-sm hover:border-primary/30 transition-all active:scale-95 group">
              <Activity class="h-3.5 w-3.5 text-slate-400 group-hover:text-primary transition-colors" />
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100 pr-2 mr-1">Status</span>
              
              <div class="flex items-center gap-1.5">
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
                <ChevronDown class="h-3 w-3 text-slate-400 group-hover:text-slate-600 transition-transform duration-300" />
              </div>
            </ListboxButton>

            <transition
              leave-active-class="transition duration-100 ease-in"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <ListboxOptions class="absolute z-50 mt-2 w-48 overflow-auto rounded-2xl bg-white py-1.5 text-base shadow-xl ring-1 ring-slate-900/5 focus:outline-none sm:text-sm">
                <ListboxOption
                  v-for="option in statusOptions"
                  :key="option.value"
                  :value="option.value"
                  v-slot="{ active, selected }"
                  as="template"
                >
                  <li
                    :class="[
                      active ? 'bg-slate-50 text-slate-900' : 'text-slate-600',
                      'relative cursor-pointer select-none py-2.5 px-4 transition-colors flex items-center justify-between'
                    ]"
                  >
                    <span :class="[selected ? 'font-bold text-slate-900' : 'font-medium', 'block truncate']">
                      {{ option.label }}
                    </span>
                    <Check v-if="selected" class="h-3.5 w-3.5 text-primary" />
                  </li>
                </ListboxOption>
              </ListboxOptions>
            </transition>
          </div>
        </Listbox>

        <!-- Audit Info (Right Side) -->
        <div v-if="auditDetails" class="text-xs text-slate-400 flex items-center gap-2 justify-end">
          <Clock class="h-3.5 w-3.5" />
          <span>Last updated</span>
          <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600">
            {{ formatAuditDate(auditDetails.updated_at) }}
          </span>
          <span>by</span>
          <span class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2">
            {{ auditDetails.updated_by }}
          </span>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <button 
          @click="$emit('close')" 
          class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all font-medium"
        >
          Cancel
        </button>
        <button 
          @click="handleSave" 
          :disabled="isSaving"
          class="px-8 py-2.5 bg-slate-900 text-white rounded-xl font-bold shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95 text-sm flex items-center gap-2 disabled:opacity-70"
        >
          <Loader2 v-if="isSaving" class="h-4 w-4 animate-spin" />
          <Check v-else class="h-4 w-4" />
          {{ isSaving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </template>
  </InfoModal>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from "@headlessui/vue";
import { Loader2, Check, ClipboardList, User, CreditCard, Briefcase, Printer, Package, Box, MapPin, Truck, FileText, Activity, Calendar, Pencil, Hash, Clock, User as UserIcon, ChevronDown } from "lucide-vue-next";

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
import axios from "axios";
import { useToast } from "../../composables/useToast";
import InfoModal from "../ui/InfoModal.vue";

// Section Components
import OrderDetailsSection from "../orders/edit-tabs/tracking-edit-tabs/OrderDetailsSection.vue";
import ClientInfoSection from "../orders/edit-tabs/tracking-edit-tabs/ClientInfoSection.vue";
import CardSpecsSection from "../orders/edit-tabs/tracking-edit-tabs/CardSpecsSection.vue";
import WorkAssignSection from "../orders/edit-tabs/tracking-edit-tabs/WorkAssignSection.vue";
import DesignPrintSection from "../orders/edit-tabs/tracking-edit-tabs/DesignPrintSection.vue";
import OrderPrintingSection from "../orders/edit-tabs/tracking-edit-tabs/OrderPrintingSection.vue";
import PackagingLogisticsSection from "../orders/edit-tabs/tracking-edit-tabs/PackagingLogisticsSection.vue";
import PackagingStatusSection from "../orders/edit-tabs/tracking-edit-tabs/PackagingStatusSection.vue";
import DeliveryLocationSection from "../orders/edit-tabs/tracking-edit-tabs/DeliveryLocationSection.vue";
import DispatchModeSection from "../orders/edit-tabs/tracking-edit-tabs/DispatchModeSection.vue";
import DispatchDetailsSection from "../orders/edit-tabs/tracking-edit-tabs/DispatchDetailsSection.vue";

const props = defineProps({
  isOpen: Boolean,
  orderId: [Number, String],
  stage: String
});

const emit = defineEmits(["close", "success"]);

const toast = useToast();
const orderData = ref(null);
const loading = ref(false);
const isSaving = ref(false);
const staffOptions = ref([]);
const stageStatus = ref("Pending");

const statusOptions = [
  { label: 'Pending', value: 'Pending' },
  { label: 'Processing', value: 'Process' },
  { label: 'Completed', value: 'Completed' }
];

const stageTitle = computed(() => {
  switch (props.stage) {
    case 'client-information': return 'Client Information';
    case 'designing': return 'Designing';
    case 'printing': return 'Printing';
    case 'packaging': return 'Packaging';
    case 'delivery': return 'Dispatch & Delivery';
    default: return 'Edit Order Section';
  }
});

const relevantSections = computed(() => {
  switch (props.stage) {
    case 'client-information':
      return [
        { id: 'details', label: 'Order Details', icon: ClipboardList, component: OrderDetailsSection, key: 'job_details' },
        { id: 'client', label: 'Client Information', icon: User, component: ClientInfoSection, key: 'client_info' },
        { id: 'specs', label: 'Card Specifications', icon: CreditCard, component: CardSpecsSection, key: 'card_specs' }
      ];
    case 'designing':
      return [
        { id: 'assign', label: 'Work Assign Process', icon: Briefcase, component: WorkAssignSection, key: 'work_assign' },
        { id: 'design', label: 'Design Details', icon: Printer, component: DesignPrintSection, key: 'design_print' }
      ];
    case 'printing':
      return [
        { id: 'printing', label: 'Order & Printing Status', icon: Package, component: OrderPrintingSection, key: 'printing_status' }
      ];
    case 'packaging':
      return [
        { id: 'logistics', label: 'Packaging & Logistics', icon: Box, component: PackagingLogisticsSection, key: 'packaging_logistics' },
        { id: 'status', label: 'Packaging Status', icon: Box, component: PackagingStatusSection, key: 'packaging_status' }
      ];
    case 'delivery':
      return [
        { id: 'location', label: 'Delivery Location', icon: MapPin, component: DeliveryLocationSection, key: 'delivery_location' },
        { id: 'dispatch', label: 'Mode of Dispatch', icon: Truck, component: DispatchModeSection, key: 'dispatch_mode' },
        { id: 'details', label: 'Dispatch Details', icon: FileText, component: DispatchDetailsSection, key: 'dispatch_details' }
      ];
    default: return [];
  }
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
  // 1. Try modern stage-level audit
  const stageData = orderData.value?.[stageRelationKey.value];
  if (stageData?.audit_details) return stageData.audit_details;

  // 2. Fallback to legacy tracking-based audit for this stage
  const tracking = orderData.value?.tracking || {};
  
  // Map stages to all their possible tracking JSON sections
  const stageSectionMap = {
    'client-information': ['client_info', 'job_details', 'card_specs'],
    'designing': ['design_print', 'work_assign'],
    'printing': ['printing_status'],
    'packaging': ['packaging_status', 'packaging_logistics'],
    'delivery': ['dispatch_details', 'dispatch_mode', 'delivery_location']
  };

  const sections = stageSectionMap[props.stage] || [];
  let latestAudit = null;

  sections.forEach(key => {
    const audit = tracking[key]?._audit;
    if (audit && audit.updated_at) {
      if (!latestAudit || new Date(audit.updated_at) > new Date(latestAudit.updated_at)) {
        latestAudit = {
          updated_at: audit.updated_at,
          updated_by: audit.updated_by
        };
      }
    }
  });

  // 3. Global Fallback to Order Creation Info
  if (!latestAudit && orderData.value) {
    return {
      updated_at: orderData.value.created_at,
      updated_by: orderData.value.added_by?.name || "Admin"
    };
  }

  return latestAudit;
});

const fetchOrder = async () => {
  if (!props.orderId) return;
  loading.value = true;
  try {
    const response = await axios.get(`/api/v1/orders/${props.orderId}`);
    if (response.data.success) {
      orderData.value = response.data.data;
      if (!orderData.value.tracking) orderData.value.tracking = {};
      
      const stageData = orderData.value[stageRelationKey.value];
      stageStatus.value = stageData?.status || "Pending";

      relevantSections.value.forEach(s => {
        if (!orderData.value.tracking[s.key]) orderData.value.tracking[s.key] = {};
      });
    }
  } catch (error) {
    console.error("Error fetching order:", error);
    toast.error("Failed to load order data");
  } finally {
    loading.value = false;
  }
};

const fetchStaff = async () => {
  try {
    const response = await axios.get("/api/v1/users", { params: { per_page: 100 } });
    if (response.data.success) {
      const excludedRoles = ['user'];
      staffOptions.value = response.data.data.data
        .filter(u => u.role && !excludedRoles.includes(u.role.name.toLowerCase()))
        .map(u => ({ label: u.name, value: u.name }));
    }
  } catch (e) {
    console.error("Failed to fetch staff", e);
  }
};

const handleSave = async () => {
  isSaving.value = true;
  try {
    const payload = {
       status: stageStatus.value
    };
    relevantSections.value.forEach(s => {
      payload[s.key] = orderData.value.tracking[s.key];
    });

    if (props.stage === 'client-information') {
       await axios.put(`/api/v1/orders/${orderData.value.id}`, {
         order_date: orderData.value.order_date
       });
    }

    const response = await axios.put(`/api/v1/orders/${orderData.value.id}/stages/${props.stage}`, payload);
    if (response.data.success) {
      toast.success(`${stageTitle.value} updated successfully`);
      emit('success');
      emit('close');
    }
  } catch (error) {
    console.error("Error saving order:", error);
    toast.error("Failed to save changes.");
  } finally {
    isSaving.value = false;
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) fetchOrder();
});

onMounted(fetchStaff);
</script>
