<template>
  <div class="space-y-8 animate-in fade-in duration-500 max-w-5xl mx-auto pb-20">
    <div class="flex items-center justify-between">
      <button @click="$router.push(`/order-management/${stage}`)" class="group flex items-center gap-2.5 text-slate-400 hover:text-rose-500 transition-all duration-300">
        <div class="p-2 rounded-lg bg-white border border-slate-200 group-hover:border-rose-500/30 group-hover:bg-rose-50 shadow-sm transition-all duration-300">
          <ArrowLeft class="h-3.5 w-3.5 group-hover:-translate-x-0.5 transition-transform" />
        </div>
        <span class="font-bold text-[10px] uppercase tracking-[0.2em] transition-colors">Cancel Editing</span>
      </button>

      <div class="flex gap-3">
        <button 
          @click="handleSave" 
          :disabled="isSaving"
          class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95 text-sm flex items-center gap-2 disabled:opacity-70"
        >
          <Loader2 v-if="isSaving" class="h-4 w-4 animate-spin" />
          <Check v-else class="h-4 w-4" />
          {{ isSaving ? 'SAVING...' : 'SAVE CHANGES' }}
        </button>
      </div>
    </div>

    <PageHeader :title="`Edit Order #${order?.order_number || ''}`" :subtitle="stageTitle" />

    <div v-if="loading" class="flex items-center justify-center p-20">
      <Loader2 class="h-10 w-10 animate-spin text-primary" />
    </div>

    <div v-else-if="order" class="space-y-6">
       <!-- Dynamic Editable Sections Based on Stage -->
       <div v-for="section in relevantSections" :key="section.id" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
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
            <component 
              :is="section.component" 
              :order="order" 
              :staff-options="staffOptions"
              @update:order="(val) => (order = val)"
            />
          </div>
       </div>
    </div>
    
    <!-- Sticky Save Bar for Mobile/Easy Access -->
    <div class="fixed bottom-6 right-6 z-50 md:hidden">
       <button 
          @click="handleSave" 
          :disabled="isSaving"
          class="w-16 h-16 bg-slate-900 text-white rounded-full shadow-2xl flex items-center justify-center transition-all active:scale-90"
        >
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
import PageHeader from "../../components/ui/PageHeader.vue";

// Section Components
import OrderDetailsSection from "../../components/orders/edit-tabs/tracking-edit-tabs/OrderDetailsSection.vue";
import ClientInfoSection from "../../components/orders/edit-tabs/tracking-edit-tabs/ClientInfoSection.vue";
import CardSpecsSection from "../../components/orders/edit-tabs/tracking-edit-tabs/CardSpecsSection.vue";
import WorkAssignSection from "../../components/orders/edit-tabs/tracking-edit-tabs/WorkAssignSection.vue";
import DesignPrintSection from "../../components/orders/edit-tabs/tracking-edit-tabs/DesignPrintSection.vue";
import OrderPrintingSection from "../../components/orders/edit-tabs/tracking-edit-tabs/OrderPrintingSection.vue";
import PackagingLogisticsSection from "../../components/orders/edit-tabs/tracking-edit-tabs/PackagingLogisticsSection.vue";
import PackagingStatusSection from "../../components/orders/edit-tabs/tracking-edit-tabs/PackagingStatusSection.vue";
import DeliveryLocationSection from "../../components/orders/edit-tabs/tracking-edit-tabs/DeliveryLocationSection.vue";
import DispatchModeSection from "../../components/orders/edit-tabs/tracking-edit-tabs/DispatchModeSection.vue";
import DispatchDetailsSection from "../../components/orders/edit-tabs/tracking-edit-tabs/DispatchDetailsSection.vue";

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

const relevantSections = computed(() => {
  switch (stage.value) {
    case 'client-information':
      return [
        { id: 'details', label: 'Order Details', icon: ClipboardList, component: OrderDetailsSection, key: 'job_details' },
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
      if (!order.value.tracking) order.value.tracking = {};
      
      // Ensure all required keys exist to avoid v-model errors
      relevantSections.value.forEach(s => {
        if (!order.value.tracking[s.key]) order.value.tracking[s.key] = {};
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
    // Construct payload with only relevant sections
    const payload = {};
    relevantSections.value.forEach(s => {
      payload[s.key] = order.value.tracking[s.key];
    });

    if (stage.value === 'client-information') {
       await axios.put(`/api/v1/orders/${order.value.id}`, {
         order_date: order.value.order_date
       });
    }

    const response = await axios.put(`/api/v1/orders/${order.value.id}/stages/${stage.value}`, payload);
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
