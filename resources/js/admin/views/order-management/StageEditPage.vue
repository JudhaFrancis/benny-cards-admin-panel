<template>
  <div class="space-y-8 animate-in fade-in duration-500 max-w-5xl mx-auto pb-20">
    <div class="flex items-center justify-between">
      <button @click="$router.push(`/order-management/${stage}`)"
        class="group flex items-center gap-2.5 text-slate-400 hover:text-rose-500 transition-all duration-300">
        <div
          class="p-2 rounded-lg bg-white border border-slate-200 group-hover:border-rose-500/30 group-hover:bg-rose-50 shadow-sm transition-all duration-300">
          <ArrowLeft class="h-3.5 w-3.5 group-hover:-translate-x-0.5 transition-transform" />
        </div>
        <span class="font-bold text-[10px] uppercase tracking-[0.2em] transition-colors">Cancel Editing</span>
      </button>

      <div class="flex gap-3">
        <button @click="handleSave" :disabled="isSaving"
          class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95 text-sm flex items-center gap-2 disabled:opacity-70">
          <Loader2 v-if="isSaving" class="h-4 w-4 animate-spin" />
          <Check v-else class="h-4 w-4" />
          {{ isSaving ? 'SAVING...' : 'SAVE CHANGES' }}
        </button>
      </div>
    </div>

    <PageHeader :title="`Edit Order #${order?.order_number || ''}`">
      <template #subtitle>
        <div class="flex items-center gap-2">
          <span>{{ stageTitle }}</span>
          <span v-if="customerName" class="h-1 w-1 rounded-full bg-slate-300"></span>
          <div v-if="customerName" class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
            <User class="h-3 w-3" />
            {{ customerName }}
          </div>
        </div>
      </template>
    </PageHeader>

    <div v-if="loading" class="flex items-center justify-center p-20">
      <Loader2 class="h-10 w-10 animate-spin text-primary" />
    </div>

    <div v-else-if="order" class="space-y-6">
      <!-- Dynamic Editable Sections Based on Stage -->
      <div v-for="section in relevantSections" :key="section.id"
        class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-2xl bg-primary/5 text-primary">
              <component :is="section.icon" class="h-6 w-6" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ section.label }}</h3>
            </div>
          </div>
        </div>
        <div class="p-8">
          <component :is="section.component" :order="order" :staff-options="staffOptions"
            @update:order="(val) => (order = val)" />
        </div>
      </div>
    </div>

    <!-- Sticky Save Bar for Mobile/Easy Access -->
    <div class="fixed bottom-6 right-6 z-50 md:hidden">
      <button @click="handleSave" :disabled="isSaving"
        class="w-16 h-16 bg-slate-900 text-white rounded-full shadow-2xl flex items-center justify-center transition-all active:scale-90">
        <Check v-if="!isSaving" class="h-6 w-6" />
        <Loader2 v-else class="h-6 w-6 animate-spin" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { ArrowLeft, Check, Loader2, Pencil, ClipboardList, User, CreditCard, Briefcase, Printer, Package, Box, MapPin, Truck, FileText } from "lucide-vue-next";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import PageHeader from "../../components/ui/layout/PageHeader.vue";

// Section Components
import OrderDetailsSection from "../../components/orders/edit-tabs/tracking-edit-tabs/client-information/OrderDetailsSection.vue";
import ClientInfoSection from "../../components/orders/edit-tabs/tracking-edit-tabs/client-information/ClientInfoSection.vue";
import CardSpecsSection from "../../components/orders/edit-tabs/tracking-edit-tabs/client-information/CardSpecsSection.vue";
import WorkAssignSection from "../../components/orders/edit-tabs/tracking-edit-tabs/designing/WorkAssignSection.vue";
import DesignPrintSection from "../../components/orders/edit-tabs/tracking-edit-tabs/designing/DesignPrintSection.vue";
import OrderPrintingSection from "../../components/orders/edit-tabs/tracking-edit-tabs/printing/OrderPrintingSection.vue";
import PackagingLogisticsSection from "../../components/orders/edit-tabs/tracking-edit-tabs/packaging/PackagingLogisticsSection.vue";
import PackagingStatusSection from "../../components/orders/edit-tabs/tracking-edit-tabs/packaging/PackagingStatusSection.vue";
import DeliveryLocationSection from "../../components/orders/edit-tabs/tracking-edit-tabs/dispatch-delivery/DeliveryLocationSection.vue";
import DispatchModeSection from "../../components/orders/edit-tabs/tracking-edit-tabs/dispatch-delivery/DispatchModeSection.vue";
import DispatchDetailsSection from "../../components/orders/edit-tabs/tracking-edit-tabs/dispatch-delivery/DispatchDetailsSection.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const order = ref(null);
const loading = ref(true);
const isSaving = ref(false);
const staffOptions = ref([]);

const stage = computed(() => route.params.stage);

const stageTitle = computed(() => {
  switch (stage.value) {
    case 'client-information': return 'Client Information';
    case 'designing': return 'Designing';
    case 'printing': return 'Printing';
    case 'packaging': return 'Packaging';
    case 'delivery': return 'Dispatch & Delivery';
    default: return 'Order Section';
  }
});

const customerName = computed(() => {
  return order.value?.client_information?.client_info?.name || order.value?.customer_details?.name || '';
});

const relevantSections = computed(() => {
  switch (stage.value) {
    case 'client-information':
      return [
        { id: 'details', label: 'Order Details', icon: ClipboardList, component: OrderDetailsSection, key: 'order_details' },
        { id: 'client', label: 'Client Information', icon: User, component: ClientInfoSection, key: 'client_info' },
        { id: 'specs', label: 'Card Specifications', icon: CreditCard, component: CardSpecsSection, key: 'card_specs' }
      ];
    case 'designing':
      return [
        { id: 'assign', label: 'Work Assign Process', icon: Briefcase, component: WorkAssignSection, key: 'work_assign' },
        { id: 'design', label: 'Design – Checked & Given to Print', icon: Printer, component: DesignPrintSection, key: 'design_print' }
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

const fetchOrder = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/v1/orders/${route.params.id}`);
    if (response.data.success) {
      order.value = response.data.data;

      const stageRelationMap = {
        'client-information': 'client_information',
        'designing': 'designing',
        'printing': 'printing',
        'packaging': 'packaging',
        'delivery': 'dispatch_delivery'
      };

      const relation = stageRelationMap[stage.value];
      if (relation) {
        if (!order.value[relation]) order.value[relation] = {};
        relevantSections.value.forEach(s => {
          if (!order.value[relation][s.key]) order.value[relation][s.key] = {};
        });
      }
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
    const response = await axios.get("/api/v1/users", { params: { per_page: 1000 } });
    if (response.data.success) {
      staffOptions.value = response.data.data.data
        .filter(u => u.role?.name?.toLowerCase() !== 'user')
        .map(u => ({ label: u.name, value: u.name }));
    }
  } catch (e) {
    console.error("Failed to fetch staff", e);
  }
};

const handleSave = async () => {
  isSaving.value = true;
  try {
    const stageRelationMap = {
      'client-information': 'client_information',
      'designing': 'designing',
      'printing': 'printing',
      'packaging': 'packaging',
      'delivery': 'dispatch_delivery'
    };
    const relation = stageRelationMap[stage.value];

    const formData = new FormData();
    formData.append('_method', 'PUT');
    
    // Add all section data as JSON strings
    relevantSections.value.forEach(s => {
      const content = order.value[relation]?.[s.key] || {};
      formData.append(s.key, JSON.stringify(content));

      // Special case: PackagingStatusSection actually edits dispatch_mode (packed_by and gift_type)
      if (s.key === 'packaging_status' && relation === 'packaging') {
        const dispatchMode = order.value.dispatch_delivery?.dispatch_mode || {};
        formData.append('dispatch_mode', JSON.stringify(dispatchMode));
      }
    });

    // Handle sticker image if in designing or packaging stage
    const stageData = order.value[relation] || {};
    if ((stage.value === 'designing' || stage.value === 'packaging') && stageData.design_print_file) {
      formData.append('sticker_image', stageData.design_print_file);
    }

    if (stage.value === 'client-information') {
      const orderDetails = order.value[relation]?.order_details || {};
      console.log("Saving client info. Order priority is:", order.value.priority);
      await axios.put(`/api/v1/orders/${order.value.id}`, {
        order_date: order.value.order_date,
        delivery_date: orderDetails.expected_delivery_date,
        priority: order.value.priority
      });
    }

    const response = await axios.post(`/api/v1/orders/${order.value.id}/stages/${stage.value}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.success) {
      toast.success(`${stageTitle.value} updated successfully`);
      router.push(`/order-management/${stage.value}`);
    }
  } catch (error) {
    console.error("Error saving order:", error);
    toast.error("Failed to save changes. Please check required fields.");
  } finally {
    isSaving.value = false;
  }
};

onMounted(() => {
  fetchOrder();
  fetchStaff();
});
</script>
