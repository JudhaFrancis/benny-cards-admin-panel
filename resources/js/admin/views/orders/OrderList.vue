<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Order Management</h1>
      <div class="flex gap-4">
        <!-- Search -->
        <input
          v-model="filters.search"
          @input="fetchOrders"
          type="text"
          placeholder="Search Order # or Customer..."
          class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <!-- Status Filter -->
        <select
          v-model="filters.status"
          @change="fetchOrders"
          class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div
      class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100"
    >
      <table class="w-full text-left border-collapse">
        <thead
          class="bg-gray-50 text-gray-600 text-sm font-semibold uppercase tracking-wider"
        >
          <tr>
            <th class="px-6 py-4">Order #</th>
            <th class="px-6 py-4">Customer</th>
            <th class="px-6 py-4">Date</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4">Total</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading" class="animate-pulse">
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
              Loading orders...
            </td>
          </tr>
          <tr
            v-else
            v-for="order in orders"
            :key="order.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-6 py-4 font-medium text-gray-900">
              {{ order.order_number }}
            </td>
            <td class="px-6 py-4">
              <div class="text-sm font-medium text-gray-900">
                {{ order.customer?.name || "N/A" }}
              </div>
              <div class="text-xs text-gray-500">
                {{ order.customer?.email }}
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">
              {{ formatDate(order.placed_at) }}
            </td>
            <td class="px-6 py-4">
              <span
                :class="statusClasses(order.status)"
                class="px-3 py-1 text-xs font-semibold rounded-full"
              >
                {{ order.status }}
              </span>
            </td>
            <td class="px-6 py-4 font-medium text-gray-900">
              ${{ order.total_amount }}
            </td>
            <td class="px-6 py-4 text-right">
              <button
                class="text-blue-600 hover:text-blue-800 font-medium text-sm"
              >
                View Details
              </button>
            </td>
          </tr>
          <tr v-if="!loading && orders.length === 0">
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
              No orders found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination (Simple Example) -->
    <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
      <button
        :disabled="pagination.current_page === 1"
        @click="changePage(pagination.current_page - 1)"
        class="disabled:opacity-50 hover:text-blue-600"
      >
        &larr; Previous
      </button>
      <span
        >Page {{ pagination.current_page }} of {{ pagination.last_page }}</span
      >
      <button
        :disabled="pagination.current_page === pagination.last_page"
        @click="changePage(pagination.current_page + 1)"
        class="disabled:opacity-50 hover:text-blue-600"
      >
        Next &rarr;
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from "vue";
// import axios from 'axios'; // Assumed axios is configured

const orders = ref([]);
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1 });
const filters = reactive({
  search: "",
  status: "",
});

const fetchOrders = async (page = 1) => {
  loading.value = true;
  try {
    // Mock API call structure
    // const response = await axios.get('/api/admin/v1/orders', { params: { ...filters, page } });
    // orders.value = response.data.data.data;
    // pagination.value = response.data.data; // Paginator meta

    // MOCK DATA FOR DEMO
    setTimeout(() => {
      orders.value = [
        {
          id: 1,
          order_number: "ORD-001",
          customer: { name: "John Doe", email: "john@example.com" },
          placed_at: "2023-10-27 10:00:00",
          status: "completed",
          total_amount: "120.50",
        },
        {
          id: 2,
          order_number: "ORD-002",
          customer: { name: "Jane Smith", email: "jane@example.com" },
          placed_at: "2023-10-28 14:30:00",
          status: "pending",
          total_amount: "45.00",
        },
      ];
      pagination.value = { current_page: page, last_page: 5 };
      loading.value = false;
    }, 500);
  } catch (error) {
    console.error("Failed to fetch orders", error);
    loading.value = false;
  }
};

const changePage = (page) => {
  fetchOrders(page);
};

const formatDate = (dateString) => {
  return (
    new Date(dateString).toLocaleDateString() +
    " " +
    new Date(dateString).toLocaleTimeString()
  );
};

const statusClasses = (status) => {
  const map = {
    pending: "bg-yellow-100 text-yellow-800",
    processing: "bg-blue-100 text-blue-800",
    completed: "bg-green-100 text-green-800",
    cancelled: "bg-red-100 text-red-800",
    refunded: "bg-gray-100 text-gray-800",
  };
  return map[status] || "bg-gray-100 text-gray-800";
};

onMounted(() => {
  fetchOrders();
});
</script>
