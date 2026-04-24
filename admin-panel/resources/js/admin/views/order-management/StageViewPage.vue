<template>
  <div class="space-y-8 animate-in fade-in duration-500 max-w-5xl mx-auto">
    <div class="flex items-center">
      <button @click="$router.push(`/order-management/${stage}`)" class="group flex items-center gap-2.5 text-slate-400 hover:text-primary transition-all duration-300">
        <div class="p-2 rounded-lg bg-white border border-slate-200 group-hover:border-primary/30 group-hover:bg-primary/5 shadow-sm transition-all duration-300">
          <ArrowLeft class="h-3.5 w-3.5 group-hover:-translate-x-0.5 transition-transform" />
        </div>
        <span class="font-bold text-[10px] uppercase tracking-[0.2em] transition-colors">Back to List</span>
      </button>
    </div>

    <PageHeader :title="`Order #${order?.order_number || ''}`" :subtitle="stageTitle" />

    <div v-if="loading" class="flex items-center justify-center p-20">
      <Loader2 class="h-10 w-10 animate-spin text-primary" />
    </div>

    <div v-else-if="order" class="space-y-6">
       <!-- Dynamic Sections Based on Stage -->
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
            <component :is="section.component" :order="order" readonly />
          </div>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { ArrowLeft, Pencil, Loader2, ClipboardList, User, CreditCard, Briefcase, Printer, Package, Box, MapPin, Truck, FileText } from "lucide-vue-next";
import axios from "axios";
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
const order = ref(null);
const loading = ref(true);

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
        { id: 'details', label: 'Order Details', icon: ClipboardList, component: OrderDetailsSection },
        { id: 'client', label: 'Client Information', icon: User, component: ClientInfoSection },
        { id: 'specs', label: 'Card Specifications', icon: CreditCard, component: CardSpecsSection }
      ];
    case 'designing':
      return [
        { id: 'assign', label: 'Work Assign Process', icon: Briefcase, component: WorkAssignSection },
        { id: 'design', label: 'Design – Checked & Given to Print', icon: Printer, component: DesignPrintSection }
      ];
    case 'printing':
      return [
        { id: 'printing', label: 'Order & Printing Status', icon: Package, component: OrderPrintingSection }
      ];
    case 'packaging':
      return [
        { id: 'logistics', label: 'Packaging & Logistics', icon: Box, component: PackagingLogisticsSection },
        { id: 'status', label: 'Packaging Status', icon: Box, component: PackagingStatusSection }
      ];
    case 'delivery':
      return [
        { id: 'location', label: 'Delivery Location', icon: MapPin, component: DeliveryLocationSection },
        { id: 'dispatch', label: 'Mode of Dispatch', icon: Truck, component: DispatchModeSection },
        { id: 'details', label: 'Dispatch Details', icon: FileText, component: DispatchDetailsSection }
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
    }
  } catch (error) {
    console.error("Error fetching order:", error);
  } finally {
    loading.value = false;
  }
};

const handleEdit = () => {
  router.push(`/order-management/${stage.value}/${route.params.id}/edit`);
};

onMounted(fetchOrder);
</script>
