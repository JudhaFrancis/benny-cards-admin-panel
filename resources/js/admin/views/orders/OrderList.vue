<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header Section -->
    <PageHeader title="Orders" subtitle="Manage and track customer purchases">
      <template #actions>
        <button
          v-if="canCreate"
          class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Create Order
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search -->
    <div
      class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100 relative z-30"
    >
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search Inner -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by order ID, customer name..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            />
          </div>

          <!-- Advanced Filters Dropdown -->
          <FilterDropdown
            :isActive="activeFiltersCount > 0"
            @reset="resetFilters"
          >
            <FilterSectionHelper label="Order Status">
              <ContextDropdown
                v-model="statusFilter"
                :options="statusOptions"
                :icon="ActivityIcon"
              />
            </FilterSectionHelper>

            <FilterSectionHelper label="Payment Status" last>
              <ContextDropdown
                v-model="paymentFilter"
                :options="paymentOptions"
                :icon="CreditCardIcon"
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

        <div
          class="text-xs font-semibold text-slate-400 uppercase tracking-widest"
        >
          {{ filteredOrders.length }} Results
        </div>
      </div>
    </div>

    <!-- Table Section -->
    <OrdersTable
      :orders="filteredOrders"
      @view-info="handleViewInfo"
      @edit="handleEdit"
      @delete="handleConfirmDelete"
    />

    <!-- Dialogs -->
    <OrderInfoDialog
      :is-open="isInfoModalOpen"
      :order="selectedOrder"
      @close="isInfoModalOpen = false"
    />

    <ConfirmationModal
      :is-open="isDeleteModalOpen"
      title="Delete Order"
      :message="`Are you sure you want to delete order #${selectedOrder?.id}? This action cannot be undone.`"
      confirm-text="Delete Order"
      variant="danger"
      @close="isDeleteModalOpen = false"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import {
  Search as SearchIcon,
  Plus as PlusIcon,
  Activity as ActivityIcon,
  CreditCard as CreditCardIcon,
} from "lucide-vue-next";
import { useRouter } from "vue-router";
import { mockOrders } from "../../data/mockOrders";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";
import OrdersTable from "../../components/orders/OrdersTable.vue";
import OrderInfoDialog from "../../components/orders/OrderInfoDialog.vue";
import PageHeader from "../../components/ui/PageHeader.vue";
import FilterDropdown from "../../components/ui/FilterDropdown.vue";
import FilterSectionHelper from "../../components/ui/FilterSection.vue";
import ContextDropdown from "../../components/ui/ContextDropdown.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";

const { canAdd: canCreate } = usePermissions();
const toast = useToast();
const router = useRouter();

const orders = ref([...mockOrders]);
const searchQuery = ref("");
const statusFilter = ref("all");
const paymentFilter = ref("all");

const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedOrder = ref(null);

const statusOptions = [
  { label: "All Statuses", value: "all" },
  {
    label: "Pending",
    value: "pending",
    description: "Order received but not yet processed.",
    badge: "New",
    badgeClass: "bg-amber-100 text-amber-700",
  },
  {
    label: "Processing",
    value: "processing",
    description: "Order is being prepared.",
    badge: "Prep",
    badgeClass: "bg-blue-100 text-blue-700",
  },
  {
    label: "Shipped",
    value: "shipped",
    description: "Order has left the warehouse.",
    badge: "Way",
    badgeClass: "bg-blue-100 text-blue-700",
  },
  {
    label: "Delivered",
    value: "delivered",
    description: "Customer has received the order.",
    badge: "Done",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Cancelled",
    value: "cancelled",
    description: "Order was terminated.",
    badge: "Void",
    badgeClass: "bg-rose-100 text-rose-700",
  },
];

const paymentOptions = [
  { label: "All Payments", value: "all" },
  {
    label: "Paid",
    value: "paid",
    description: "Full amount received.",
    badge: "Full",
    badgeClass: "bg-emerald-100 text-emerald-700",
  },
  {
    label: "Partial",
    value: "partial",
    description: "Installment or deposit paid.",
    badge: "Part",
    badgeClass: "bg-amber-100 text-amber-700",
  },
  {
    label: "Unpaid",
    value: "unpaid",
    description: "No payment received yet.",
    badge: "None",
    badgeClass: "bg-rose-100 text-rose-700",
  },
];

const activeFiltersCount = computed(() => {
  let count = 0;
  if (statusFilter.value !== "all") count++;
  if (paymentFilter.value !== "all") count++;
  return count;
});

const resetFilters = () => {
  statusFilter.value = "all";
  paymentFilter.value = "all";
};

const filteredOrders = computed(() => {
  return orders.value.filter((order) => {
    const matchesSearch =
      order.id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      order.customer.toLowerCase().includes(searchQuery.value.toLowerCase());

    const matchesStatus =
      statusFilter.value === "all" ||
      order.orderStatus.toLowerCase() === statusFilter.value.toLowerCase();

    const matchesPayment =
      paymentFilter.value === "all" ||
      order.paymentStatus.toLowerCase() === paymentFilter.value.toLowerCase();

    return matchesSearch && matchesStatus && matchesPayment;
  });
});

const handleViewInfo = (order) => {
  selectedOrder.value = order;
  isInfoModalOpen.value = true;
};

const handleEdit = (order) => {
  router.push(`/orders/${order.id}/edit`);
};

const handleConfirmDelete = (order) => {
  selectedOrder.value = order;
  isDeleteModalOpen.value = true;
};

const handleDelete = () => {
  orders.value = orders.value.filter((o) => o.id !== selectedOrder.value.id);
  toast.success("Order deleted successfully");
  isDeleteModalOpen.value = false;
};
</script>
