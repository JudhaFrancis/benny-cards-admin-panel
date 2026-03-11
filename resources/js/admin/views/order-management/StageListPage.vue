<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <PageHeader :title="title" :subtitle="subtitle" />

    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm relative z-30">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search Inner -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search Order ID..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
          </div>

          <!-- Advanced Filters Dropdown -->
          <FilterDropdown
            :isActive="activeFiltersCount > 0"
            @reset="resetFilters"
          >
            <FilterSectionHelper label="Overall Order Status">
              <ContextDropdown
                v-model="orderStatusFilter"
                :options="orderStatusOptions"
                :icon="ActivityIcon"
              />
            </FilterSectionHelper>

            <FilterSectionHelper label="Payment Status">
              <ContextDropdown
                v-model="paymentFilter"
                :options="paymentOptions"
                :icon="CreditCardIcon"
              />
            </FilterSectionHelper>

            <FilterSectionHelper label="Stage Status" last>
              <ContextDropdown
                v-model="stageStatusFilter"
                :options="stageStatusOptions"
                :icon="ClipboardListIcon"
              />
            </FilterSectionHelper>
          </FilterDropdown>

          <button
            v-if="activeFiltersCount > 0"
            @click="resetFilters"
            class="text-xs font-semibold text-primary hover:text-primary-dark transition-colors px-2"
          >
            Clear Filters
          </button>
        </div>

        <div class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
          {{ filteredOrders.length }} Items
        </div>
      </div>
    </div>

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
import { 
  Search as SearchIcon, 
  Activity as ActivityIcon, 
  CreditCard as CreditCardIcon, 
  ClipboardList as ClipboardListIcon 
} from "lucide-vue-next";
import axios from "axios";
import PageHeader from "../../components/ui/PageHeader.vue";
import OrderStageTable from "../../components/order-management/OrderStageTable.vue";
import FilterDropdown from "../../components/ui/FilterDropdown.vue";
import FilterSectionHelper from "../../components/ui/FilterSection.vue";
import ContextDropdown from "../../components/ui/ContextDropdown.vue";
import StageViewDialog from "../../components/order-management/StageViewDialog.vue";
import StageEditDialog from "../../components/order-management/StageEditDialog.vue";

const props = defineProps({
  stage: { type: String, required: true },
  title: { type: String, required: true },
  subtitle: { type: String, required: true }
});

const orders = ref([]);
const loading = ref(true);
const searchQuery = ref("");

// Modals State
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedOrder = ref(null);

// Filters state
const orderStatusFilter = ref("all");
const paymentFilter = ref("all");
const stageStatusFilter = ref("all");

const orderStatusOptions = [
  { label: "All Statuses", value: "all" },
  { label: "Pending", value: "pending" },
  { label: "Processing", value: "processing" },
  { label: "Shipped", value: "shipped" },
  { label: "Delivered", value: "delivered" },
  { label: "Cancelled", value: "cancelled" },
];

const paymentOptions = [
  { label: "All Payments", value: "all" },
  { label: "Paid", value: "paid" },
  { label: "Partial", value: "partial" },
  { label: "Unpaid", value: "unpaid" },
];

const stageStatusOptions = [
  { label: "All Progress", value: "all" },
  { label: "Pending", value: "pending" },
  { label: "Process", value: "process" },
  { label: "Completed", value: "completed" },
  { label: "Cancelled", value: "cancelled" },
];

const activeFiltersCount = computed(() => {
  let count = 0;
  if (orderStatusFilter.value !== "all") count++;
  if (paymentFilter.value !== "all") count++;
  if (stageStatusFilter.value !== "all") count++;
  return count;
});

const resetFilters = () => {
  orderStatusFilter.value = "all";
  paymentFilter.value = "all";
  stageStatusFilter.value = "all";
  searchQuery.value = "";
};

const fetchOrders = async () => {
  loading.value = true;
  try {
    const params = {
      per_page: 100, // Load enough for frontend filtering
    };
    
    // We can filter basic order status on backend if supported
    if (orderStatusFilter.value !== 'all') params.status = orderStatusFilter.value;
    
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

  // Frontend filtering for complex stage logic
  items = items.filter(o => {
    // Search
    const searchMatch = !searchQuery.value || o.order_number?.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    // Payment Status
    const paymentMatch = paymentFilter.value === 'all' || o.payment_status === paymentFilter.value;

    // Stage Status - Reusing logic from Table Component if possible, but here we can check completion
    let stageMatch = true;
    if (stageStatusFilter.value !== 'all') {
       const status = getStageStatus(o, props.stage).toLowerCase();
       stageMatch = status === stageStatusFilter.value;
    }

    return searchMatch && paymentMatch && stageMatch;
  });

  return items;
});

// Helper for stage status to match table component logic
const getStageStatus = (order, stage) => {
  if (order.status === 'cancelled') return 'Cancelled';
  const tracking = order.tracking || {};
  let isStarted = false;
  let isDone = false;

  switch (stage) {
    case 'client-information':
      isStarted = !!tracking.job_details;
      isDone = !!tracking.card_specs && Object.keys(tracking.card_specs).length > 0;
      break;
    case 'designing':
      isStarted = !!tracking.work_assign;
      isDone = !!tracking.design_print && Object.keys(tracking.design_print).length > 0;
      break;
    case 'printing':
      isStarted = !!tracking.printing_status;
      isDone = !!(tracking.printing_status?.readymade_sent_to_print || tracking.printing_status?.customize_sent_to_print_date);
      break;
    case 'packaging':
      isStarted = !!tracking.packaging_logistics;
      isDone = !!tracking.packaging_status && Object.keys(tracking.packaging_status).length > 0;
      break;
    case 'delivery':
      isStarted = !!tracking.delivery_location;
      isDone = !!tracking.dispatch_details && Object.keys(tracking.dispatch_details).length > 0;
      break;
  }
  if (isDone) return 'Completed';
  if (isStarted) return 'Process';
  return 'Pending';
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
watch([() => props.stage, orderStatusFilter], fetchOrders);
</script>
