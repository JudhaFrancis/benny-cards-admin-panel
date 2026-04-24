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
      :from="meta.from"
      @filter-change="handleFilterChange"
      @view-info="handleViewInfo"
      @edit="handleEdit"
      @delete="handleConfirmDelete"
    />

    <!-- Pagination Controls -->
    <div
      v-if="meta.total > 0"
      class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm"
    >
      <p class="text-[11px] text-gray-500 font-medium">
        Showing
        <span class="text-gray-700"
          >{{ meta.from || 0 }} to {{ (meta.from || 0) + filteredOrders.length - 1 }}</span
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
  CreditCard as CreditCardIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
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
const columnFilters = ref({});
const meta = ref({ total: 0, from: 1 });

const fetchOrders = async () => {
  loading.value = true;
  try {
    const params = {
      page: page.value,
      per_page: 10,
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
    toast.error("Failed to load orders");
  } finally {
    loading.value = false;
  }
};

const handleFilterChange = (filters) => {
  columnFilters.value = filters;
  page.value = 1;
  fetchOrders();
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
    const oid = selectedOrder.value.id;
    const payload = updatedOrderData || selectedOrder.value;

    const formData = new FormData();
    formData.append("_method", "PUT");

    // Helper to append nested objects/arrays to FormData
    const appendToFormData = (key, value) => {
      if (value === null || value === undefined) {
        formData.append(key, "");
      } else if (value instanceof File) {
        formData.append(key, value);
      } else if (Array.isArray(value)) {
        value.forEach((v, index) => appendToFormData(`${key}[${index}]`, v));
      } else if (typeof value === "object") {
        Object.keys(value).forEach((k) => appendToFormData(`${key}[${k}]`, value[k]));
      } else {
        formData.append(key, value);
      }
    };

    // Construct FormData from payload
    const fieldsToInclude = [
      "customer_name",
      "customer_email",
      "customer_phone",
      "customer_address_1",
      "customer_address_2",
      "discount",
      "extra_charges",
      "paid_amount",
      "remarks",
      "payment_method",
      "status",
      "order_date",
      "coupon_id"
    ];

    fieldsToInclude.forEach(field => {
      if (payload[field] !== undefined) formData.append(field, payload[field]);
    });

    // Special handling for customer_details object if it exists
    if (payload.customer_details) {
      Object.keys(payload.customer_details).forEach(key => {
        formData.append(`customer[${key}]`, payload.customer_details[key] || "");
      });
    }

    // append items
    if (payload.items && Array.isArray(payload.items)) {
      payload.items.forEach((item, index) => {
        formData.append(`items[${index}][id]`, item.id || "");
        formData.append(`items[${index}][product_id]`, item.product_id || "");
        formData.append(`items[${index}][product_name]`, item.product_name || "");
        formData.append(`items[${index}][quantity]`, item.quantity || 1);
        formData.append(`items[${index}][unit_price]`, item.unit_price || 0);
        
        if (item.product_image instanceof File) {
          formData.append(`items[${index}][product_image]`, item.product_image);
        } else {
          formData.append(`items[${index}][product_image]`, item.product_image || "");
        }
      });
    }

    const res = await axios.post(`/api/v1/orders/${oid}`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    if (res.data.success) {
      toast.success("Order updated successfully");
      isEditModalOpen.value = false;
      fetchOrders();
    }
  } catch (e) {
    console.error(e);
    toast.error(e.response?.data?.message || "Failed to save changes");
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
