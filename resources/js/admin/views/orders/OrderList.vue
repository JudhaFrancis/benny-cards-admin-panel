<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header Section -->
    <PageHeader :title="pageTitle" subtitle="Manage and track customer purchases">
      <template #actions>
        <button
          v-if="canCreate"
          @click="isCreateModalOpen = true"
          class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Create Order
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search (REMOVED) -->

    <!-- Table Section -->
    <OrdersTable
      :status-filter="statusFilter"
      :orders="filteredOrders"
      :loading="loading"
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

    <OrderCreateDialog
      :is-open="isCreateModalOpen"
      @close="isCreateModalOpen = false"
      @success="fetchOrders"
    />

    <OrderEditDialog
      :open="isEditModalOpen"
      :order="selectedOrder"
      :is-saving="isSaving"
      @open-change="isEditModalOpen = $event"
      @save="handleOrderSaved"
      @refresh="fetchOrders"
    />

    <ConfirmationModal
      :is-open="isDeleteModalOpen"
      title="Delete Order"
      :message="`Are you sure you want to delete order #${selectedOrder?.order_number || selectedOrder?.id}? This action cannot be undone.`"
      confirm-text="Delete Order"
      variant="danger"
      @close="isDeleteModalOpen = false"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import {
  Search as SearchIcon,
  Plus as PlusIcon,
  Activity as ActivityIcon,
  CreditCard as CreditCardIcon,
} from "lucide-vue-next";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";
import OrdersTable from "../../components/orders/OrdersTable.vue";
import OrderInfoDialog from "../../components/orders/OrderInfoDialog.vue";
import OrderCreateDialog from "../../components/orders/OrderCreateDialog.vue";
import OrderEditDialog from "../../components/orders/OrderEditDialog.vue";
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import ConfirmationModal from "../../components/ui/modals/ConfirmationModal.vue";

const { getModulePermissions } = usePermissions();
const { canAdd: canCreate, canEdit, canDelete } = getModulePermissions("Order");
const toast = useToast();
const router = useRouter();
const route = useRoute();

const orders = ref([]);
const loading = ref(true);
const isSaving = ref(false);
const page = ref(1);
const statusFilter = ref("all");
const meta = ref({ total: 0 });

const fetchOrders = async () => {
  loading.value = true;
  try {
    const params = {
      page: page.value,
      per_page: 100,
    };

    const response = await axios.get("/api/v1/orders", { params });
    if (response.data.success) {
      orders.value = response.data.data.data.map((order, index) => ({
        ...order,
        sn: index + (response.data.data.from || 1),
      }));
      meta.value = {
        total: response.data.data.total,
        from: response.data.data.from,
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
      };
    }
  } catch (error) {
    console.error("Error fetching orders:", error);
    toast.error("Failed to load orders");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchOrders();
});

const isInfoModalOpen = ref(false);
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedOrder = ref(null);

const filteredOrders = computed(() => orders.value);

// Watch for search/filter changes
watch(page, fetchOrders);

const handleViewInfo = (order) => {
  selectedOrder.value = order;
  isInfoModalOpen.value = true;
};

const handleEdit = (order) => {
  selectedOrder.value = order;
  isEditModalOpen.value = true;
};

const handleOrderSaved = async (updatedOrderData) => {
  // If the dialog emits the full updated order, we might use it to optimistically update
  // But reliable way is to re-fetch or patch the local list
  // Assuming updatedOrderData might be partial or full.

  // Optimistic update if we have full data
  if (updatedOrderData && updatedOrderData.id) {
    const index = orders.value.findIndex((o) => o.id === updatedOrderData.id);
    if (index !== -1) {
      orders.value[index] = { ...orders.value[index], ...updatedOrderData };
    }
  }

  // Also verify backend persistence (save logic is inside the dialog components, but top level might need final save if dialog emits 'save' with data but didn't push to API itself)
  // Wait, in OrderEditDialog structure I made, it emits 'save'.
  // Let's check OrderEditDialog again. It emits 'save' with data. It DOES NOT call API itself.

  // So we MUST save changes here!

  try {
    isSaving.value = true;
    const oid = selectedOrder.value.id; // or updatedOrderData.id
    const payload = updatedOrderData || selectedOrder.value;

    const res = await axios.put(`/api/v1/orders/${oid}`, payload);
    if (res.data.success) {
      toast.success("Order updated successfully");
      isEditModalOpen.value = false;
      fetchOrders(); // Refresh to be sure
    }
  } catch (e) {
    console.error(e);
    toast.error("Failed to save changes");
  } finally {
    isSaving.value = false;
  }
};

const handleConfirmDelete = (order) => {
  selectedOrder.value = order;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!selectedOrder.value) return;

  try {
    await axios.delete(`/api/v1/orders/${selectedOrder.value.id}`);
    toast.success("Order deleted successfully");
    orders.value = orders.value.filter((o) => o.id !== selectedOrder.value.id);
    isDeleteModalOpen.value = false;
  } catch (error) {
    console.error("Error deleting order:", error);
    toast.error("Failed to delete order");
  }
};
const pageTitle = computed(() => {
  if (statusFilter.value === "all") return "Orders";
  const options = {
    client_info: "Client Information",
    designing: "Designing Process",
    printing: "Printing Process",
    packaging: "Packaging & Logistics",
    delivered: "Delivery & Dispatch",
  };
  return options[statusFilter.value] || "Orders";
});
</script>
