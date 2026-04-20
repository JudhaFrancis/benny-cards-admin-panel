<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <PageHeader :title="title" :subtitle="subtitle" />

    <!-- Filters & Search (REMOVED) -->

    <OrderStageTable :orders="filteredOrders" :loading="loading" :stage="stage" :from="meta.from" @view="handleView" @edit="handleEdit" @filter-change="handleFilterChange" />

    <!-- Pagination Controls -->
    <div
      v-if="meta.total > 0"
      class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm"
    >
      <p class="text-[11px] text-gray-500 font-medium">
        Showing
        <span class="text-gray-700"
          >{{ (page - 1) * 20 + 1 }} to {{ Math.min(page * 20, meta.total) }}</span
        >
        of <span class="text-gray-700">{{ meta.total || 0 }}</span> results
      </p>
      <div class="flex items-center gap-2">
        <button
          @click="page--"
          :disabled="page <= 1"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <button
          @click="page++"
          :disabled="page >= meta.last_page"
          class="p-2 rounded-xl border border-gray-200 text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-50 transition-all active:scale-95"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Dialogs -->
    <StageViewDialog :is-open="isViewModalOpen" :order-id="selectedOrder?.id" :stage="stage"
      @close="isViewModalOpen = false" />

    <StageEditDialog :is-open="isEditModalOpen" :order-id="selectedOrder?.id" :stage="stage"
      @close="isEditModalOpen = false" @success="fetchOrders" />

    <OrderCreateDialog :is-open="isCreateModalOpen" @close="isCreateModalOpen = false" @success="fetchOrders" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import OrderStageTable from "../../components/order-management/OrderStageTable.vue";
import StageViewDialog from "../../components/order-management/StageViewDialog.vue";
import StageEditDialog from "../../components/order-management/StageEditDialog.vue";
import { useAuth } from "../../composables/useAuth";
import { usePermissions } from "../../composables/usePermissions";
import { Plus as PlusIcon, ChevronLeft as ChevronLeftIcon, ChevronRight as ChevronRightIcon } from "lucide-vue-next";
import axios from "axios";
import OrderCreateDialog from "../../components/orders/OrderCreateDialog.vue";

const { user } = useAuth();
const { getModulePermissions } = usePermissions();
const { canAdd: canCreate } = getModulePermissions("Order");

const props = defineProps({
  stage: { type: String, required: true },
  title: { type: String, required: true },
  subtitle: { type: String, required: true }
});

const orders = ref([]);
const loading = ref(true);
const page = ref(1);
const meta = ref({ total: 0, from: 1 });
const columnFilters = ref({});

// Modals State
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isCreateModalOpen = ref(false);
const selectedOrder = ref(null);

const fetchOrders = async () => {
  orders.value = [];
  loading.value = true;
  try {
    const params = {
      page: page.value,
      per_page: 20,
      stage: props.stage,
      ...columnFilters.value
    };

    const response = await axios.get("/api/v1/orders", { params });
    if (response.data.success) {
      orders.value = response.data.data.data;
      meta.value = {
        total: response.data.data.total,
        from: response.data.data.from || 1,
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
      };
    }
  } catch (error) {
    console.error("Error fetching orders:", error);
  } finally {
    loading.value = false;
  }
};

const handleFilterChange = (filters) => {
  columnFilters.value = filters;
  page.value = 1; // Reset to first page on filter change
  fetchOrders();
};

const filteredOrders = computed(() => {
  let items = orders.value;

  // Role based filtering: If not super-admin or admin, only show orders assigned to this user
  const userRole = user.value?.role?.name?.toLowerCase();
  const userName = user.value?.name;

  if (userRole && userRole !== 'super-admin') {
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
      return order.client_information?.order_details?.order_taken_by;
    case 'designing':
      return order.designing?.work_assign?.assigned_to || order.designing?.work_assign?.completed_by;
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
watch(() => props.stage, () => {
  page.value = 1;
  columnFilters.value = {}; // Reset filters when switching between stages
  fetchOrders();
});
watch(page, fetchOrders);
</script>
