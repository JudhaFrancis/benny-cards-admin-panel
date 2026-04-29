<template>
  <div class="p-6 space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <button @click="$router.push('/reports')"
          class="group/back h-10 w-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95 shadow-sm"
          title="Back to Reports">
          <ArrowLeftIcon class="h-5 w-5 text-slate-500 group-hover/back:text-slate-900 transition-colors" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Orders Report
          </h1>
          <p class="text-sm text-slate-500 mt-1">Complete summary and analytics for all business orders.</p>
        </div>
      </div>
    </div>

    <!-- Advanced Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <AdvancedDateFilter v-model="filters" @change="fetchReport" />
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <SummaryCard 
        v-for="(stat, index) in stats" 
        :key="stat.label"
        :label="stat.label"
        :value="stat.value"
        :icon="stat.icon"
        :active="activeStatIndex === index"
        :active-border-class="stat.activeBorder"
        :active-icon-bg-class="stat.activeIconBg"
        :active-icon-color-class="stat.activeIconColor"
        :active-label-color-class="stat.activeLabel"
        @click="handleStatClick(index)"
      />
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-8 w-1 bg-primary rounded-full"></div>
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
            Order Records <span class="text-slate-400 ml-1">({{ orders.length }})</span>
          </h3>
        </div>

        <div class="flex items-center gap-4 flex-1 justify-end">
          <!-- Search Input -->
          <div class="relative w-full md:w-64 group">
            <SearchIcon
              class="absolute left-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input v-model="searchQuery" type="text" placeholder="Search orders..."
              class="w-full pl-10 pr-10 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm group-hover:border-slate-300" />
            <button v-if="searchQuery" @click="searchQuery = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition-colors">
              <XIcon class="h-3.5 w-3.5" />
            </button>
          </div>

          <button @click="handleExport" :disabled="loading || orders.length === 0"
            class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 shadow-sm hover:opacity-90 transition-all active:scale-95 disabled:opacity-50 whitespace-nowrap">
            <DownloadIcon class="h-3.5 w-3.5" />
            Export
          </button>
        </div>
      </div>

      <DataTable
        :columns="columns"
        :items="orders"
        :loading="loading"
        :from="meta.from"
        manual-filters
        @row-click="handleRowClick"
        @filter-change="handleFilterChange"
        :empty-text="searchQuery ? 'No orders match your search.' : 'No matching results found for the selected period.'"
      >
        <template #cell-customer_name="{ item: order }">
          <span class="text-sm font-medium text-slate-900">{{ order.customer_name }}</span>
        </template>

        <template #cell-order_number="{ item: order }">
          <span class="text-sm font-semibold text-slate-900 italic">
            {{ order.order_number }}
          </span>
        </template>

        <template #cell-status="{ item: order }">
          <span :class="cn('inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-colors duration-200', 
                orderStatusStyles[order.status?.toLowerCase()] || 'bg-slate-100 text-slate-800 border-slate-200')">
            {{ order.status || 'New Order' }}
          </span>
        </template>

        <template #cell-total_amount="{ item: order }">
          <span class="text-sm font-bold text-slate-900">₹{{ order.total_amount }}</span>
        </template>

        <template #cell-created_at="{ item: order }">
          <div class="flex items-center gap-1.5 text-xs text-slate-600 font-bold">
            <CalendarIcon class="h-3 w-3 text-slate-400" />
            {{ formatDate(order.created_at) }}
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Pagination Controls (Matching Main Style) -->
    <div
      v-if="meta.total > 0"
      class="bg-white rounded-2xl border border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm animate-in fade-in slide-in-from-bottom-2 duration-500"
    >
      <p class="text-[11px] text-slate-500 font-medium">
        Showing
        <span class="text-slate-700"
          >{{ meta.from || 0 }} to {{ Math.min(meta.from + orders.length - 1, meta.total) }}</span
        >
        of <span class="text-slate-700">{{ meta.total || 0 }}</span> results
      </p>
      <div class="flex items-center gap-2">
        <button
          @click="page--"
          :disabled="page <= 1"
          class="p-2 rounded-xl border border-slate-200 text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all active:scale-95 shadow-sm"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
        <button
          @click="page++"
          :disabled="page >= meta.last_page"
          class="p-2 rounded-xl border border-slate-200 text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all active:scale-95 shadow-sm"
        >
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>

    <!-- Order Info Dialog -->
    <OrderInfoDialog
      :is-open="isInfoModalOpen"
      :order="selectedOrder"
      @close="isInfoModalOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { 
  ArrowLeft as ArrowLeftIcon,
  Calendar as CalendarIcon,
  Download as DownloadIcon,
  Search as SearchIcon,
  X as XIcon,
   ShoppingBag as OrdersIcon,
   CheckCircle2 as SuccessIcon,
   Clock as PendingIcon,
   ChevronLeft as ChevronLeftIcon,
   ChevronRight as ChevronRightIcon
 } from 'lucide-vue-next';
import DataTable from "../../components/ui/data-table/DataTable.vue";
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import SummaryCard from "../../components/reports/SummaryCard.vue";
import OrderInfoDialog from "../../components/orders/OrderInfoDialog.vue";

const loading = ref(false);
const searchQuery = ref("");
const orders = ref([]);
const page = ref(1);
const meta = ref({ total: 0, from: 1, last_page: 1 });
const columnFilters = ref({});
const filters = ref({
  filter_type: "day",
  filter_option: "today",
  from_date: new Date().toISOString().split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
});

const isInfoModalOpen = ref(false);
const selectedOrder = ref(null);

const handleRowClick = (order) => {
  selectedOrder.value = order;
  isInfoModalOpen.value = true;
};

const stats = ref([
  { 
    label: 'Total Orders', 
    value: '0', 
    icon: OrdersIcon, 
    status: 'all',
    activeBorder: 'border-indigo-500',
    activeIconBg: 'bg-indigo-50',
    activeIconColor: 'text-indigo-600',
    activeLabel: 'text-indigo-600'
  },
  { 
    label: 'Completed', 
    value: '0', 
    icon: SuccessIcon, 
    status: 'completed',
    activeBorder: 'border-emerald-500',
    activeIconBg: 'bg-emerald-50',
    activeIconColor: 'text-emerald-600',
    activeLabel: 'text-emerald-600'
  },
  { 
    label: 'Pending', 
    value: '0', 
    icon: PendingIcon, 
    status: 'pending',
    activeBorder: 'border-amber-500',
    activeIconBg: 'bg-amber-50',
    activeIconColor: 'text-amber-600',
    activeLabel: 'text-amber-600'
  }
]);

const activeStatIndex = ref(0);

const handleStatClick = (index) => {
  activeStatIndex.value = index;
  page.value = 1;
  fetchReport();
};

const handleFilterChange = (newFilters) => {
  columnFilters.value = newFilters;
  page.value = 1;
  fetchReport();
};

const handleSearch = () => {
  page.value = 1;
  fetchReport();
};

const columns = computed(() => [
  { key: 'sn', label: 'S/No.', align: 'center', width: '60px' },
  { key: 'order_number', label: 'Order ID', align: 'left' },
  { key: 'customer_name', label: 'Customer Name', align: 'left' },
  { 
    key: 'status', 
    label: 'Status', 
    align: 'left',
    type: 'select',
    options: [
      { label: 'Pending', value: 'pending' },
      { label: 'Confirmed', value: 'confirmed' },
      { label: 'Dispatched', value: 'dispatched' },
      { label: 'Completed', value: 'completed' },
      { label: 'Cancelled', value: 'cancelled' },
    ]
  },
  { key: 'total_amount', label: 'Amount', align: 'right' },
  { key: 'created_at', label: 'Date', align: 'left', type: 'date' },
]);

const filteredOrders = computed(() => {
  return orders.value;
});

const fetchReport = async () => {
  loading.value = true;
  try {
    const activeStatus = stats.value[activeStatIndex.value].status;
    const params = {
      startDate: filters.value.from_date,
      endDate: filters.value.to_date,
      page: page.value,
      per_page: 20,
      search: searchQuery.value,
      status: activeStatus !== 'all' ? activeStatus : undefined,
      ...columnFilters.value,
    };
    const response = await axios.get('/api/v1/reports/orders', { params });
    if (response.data.success) {
      orders.value = response.data.data.orders;
      meta.value = response.data.data.meta || { total: orders.value.length, from: 1, last_page: 1 };
      
      const s = response.data.data.stats;
      stats.value[0].value = s.total || '0';
      stats.value[1].value = s.completed || '0';
      stats.value[2].value = s.pending || '0';
    }
  } catch (e) {
    console.error('Failed to fetch orders report', e);
  } finally {
    loading.value = false;
  }
};

watch(page, fetchReport);
watch(searchQuery, handleSearch);

const handleExport = async () => {
  try {
    const params = {
      startDate: filters.value.from_date,
      endDate: filters.value.to_date
    };
    const response = await axios.get('/api/v1/reports/orders/export', { 
      params,
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `orders_report_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (e) {
    console.error('Failed to export orders report', e);
  }
};

const orderStatusStyles = {
  "new order": "bg-slate-500/10 text-slate-500 border-slate-500/20",
  confirmed: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  "designing in progress": "bg-indigo-500/10 text-indigo-500 border-indigo-500/20",
  designed: "bg-indigo-500/20 text-indigo-600 border-indigo-500/30",
  "printing in progress": "bg-sky-500/10 text-sky-500 border-sky-500/20",
  printed: "bg-sky-500/20 text-sky-600 border-sky-500/30",
  "packing in progress": "bg-cyan-500/10 text-cyan-500 border-cyan-500/20",
  packed: "bg-cyan-500/20 text-cyan-600 border-cyan-500/30",
  "out for delivery": "bg-orange-500/10 text-orange-500 border-orange-500/20",
  delivered: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  cancelled: "bg-rose-500/10 text-rose-500 border-rose-500/20",
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

const cn = (...classes) => classes.filter(Boolean).join(' ');

onMounted(() => {
  fetchReport();
});
</script>

<style scoped>
.animate-in {
  animation-fill-mode: forwards;
}
</style>
