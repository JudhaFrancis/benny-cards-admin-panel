<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <PageHeader :title="title" :subtitle="subtitle" />

    <!-- Filters & Search (REMOVED) -->

    <OrderStageTable
      :orders="filteredOrders"
      :loading="loading"
      :stage="stage"
      @view="handleView"
      @edit="handleEdit"
    />

    <!-- Dialogs -->
    <StageViewDialog
      :is-open="isViewModalOpen"
      :order-id="selectedOrder?.id"
      :stage="stage"
      @close="isViewModalOpen = false"
    />

    <StageEditDialog
      :is-open="isEditModalOpen"
      :order-id="selectedOrder?.id"
      :stage="stage"
      @close="isEditModalOpen = false"
      @success="fetchOrders"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import OrderStageTable from "../../components/order-management/OrderStageTable.vue";
import StageViewDialog from "../../components/order-management/StageViewDialog.vue";
import StageEditDialog from "../../components/order-management/StageEditDialog.vue";
import { useAuth } from "../../composables/useAuth";

const { user } = useAuth();

const props = defineProps({
  stage: { type: String, required: true },
  title: { type: String, required: true },
  subtitle: { type: String, required: true }
});

const orders = ref([]);
const loading = ref(true);

// Modals State
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedOrder = ref(null);

const fetchOrders = async () => {
  loading.value = true;
  try {
    const params = {
      per_page: 100, 
      stage: props.stage,
    };
    
    const response = await axios.get("/api/v1/orders", { params });
    if (response.data.success) {
      orders.value = response.data.data.data.map((order, index) => ({
        ...order,
        sn: index + (response.data.data.from || 1),
      }));
    }
  } catch (error) {
    console.error("Error fetching orders:", error);
  } finally {
    loading.value = false;
  }
};

const filteredOrders = computed(() => {
  let items = orders.value;

  // Role based filtering: If not super-admin or admin, only show orders assigned to this user
  const userRole = user.value?.role?.name?.toLowerCase();
  const userName = user.value?.name;

  if (userRole && userRole !== 'super-admin' && userRole !== 'admin') {
    items = items.filter((o) => {
      const assignedName = getAssignedNameHelper(o, props.stage);
      return assignedName === userName;
    });
  }

  return items;
});

// Helper to get assigned name for filtering
const getAssignedNameHelper = (order, stage) => {
  switch (stage) {
    case 'client-information':
      return order.client_information?.job_details?.order_taken_by;
    case 'designing':
      return order.designing?.work_assign?.assigned_to;
    case 'printing':
      return order.printing?.printing_status?.assigned_to || order.designing?.work_assign?.assigned_to;
    case 'packaging':
      return order.packaging?.packaging_logistics?.crafted_by;
    case 'delivery':
      return order.dispatch_delivery?.dispatch_mode?.signature_name;
    default:
      return null;
  }
};

// Helper for stage status to match table component logic
const getStageStatus = (order, stage) => {
  if (order.status === 'cancelled') return 'Cancelled';
  
  const stageRelationMap = {
    'client-information': 'client_information',
    'designing': 'designing',
    'printing': 'printing',
    'packaging': 'packaging',
    'delivery': 'dispatch_delivery'
  };
  
  const relation = stageRelationMap[stage];
  return order[relation]?.status || 'Pending';
};

const handleView = (order) => {
  selectedOrder.value = order;
  isViewModalOpen.value = true;
};

const handleEdit = (order) => {
  selectedOrder.value = order;
  isEditModalOpen.value = true;
};

onMounted(fetchOrders);

// Refetch if stage or filter changes (if we want backend filter)
watch(() => props.stage, fetchOrders);
</script>
