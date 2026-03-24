<template>
  <div class="p-6 space-y-6 animate-in fade-in duration-500">
    <!-- Header -->
    <PageHeader
      title="Orders Report"
      subtitle="Complete summary and analytics for all business orders."
    />

    <!-- Filters & Actions -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-wrap items-center justify-between gap-4 shadow-sm">
      <div class="flex flex-wrap items-center gap-4">
        <!-- From Date -->
        <div class="relative group">
          <CalendarIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none" />
          <input type="date" v-model="filters.startDate" 
                 class="pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer w-[160px]" />
        </div>

        <!-- To Date -->
        <div class="relative group">
          <CalendarIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none" />
          <input type="date" v-model="filters.endDate" 
                 class="pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all cursor-pointer w-[160px]" />
        </div>

        <button @click="handleFilter" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all font-semibold shadow-lg shadow-primary/20 active:scale-95">
          <FilterIcon class="h-4 w-4" />
          Filter
        </button>
      </div>

      <button @click="handleExport" class="flex items-center gap-2 px-6 py-2 border border-primary text-primary rounded-xl hover:bg-primary/5 transition-all font-medium">
        <DownloadIcon class="h-4 w-4" />
        Export
      </button>
    </div>

    <!-- Stats summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="stat in stats" :key="stat.label" class="bg-white p-6 rounded-2xl border border-slate-200 flex items-center gap-5 shadow-sm">
        <div :class="cn('w-14 h-14 rounded-2xl flex items-center justify-center', stat.bgClass)">
          <component :is="stat.icon" :class="cn('h-7 w-7', stat.iconClass)" />
        </div>
        <div>
          <p class="text-sm text-slate-500 font-medium">{{ stat.label }}</p>
          <p class="text-2xl font-bold text-slate-900">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- Table -->
    <DataTable
      :columns="columns"
      :items="orders"
      empty-text="No matching results found for the selected period."
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
        <span :class="cn('inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border', 
              orderStatusStyles[order.status?.toLowerCase()] || 'bg-slate-100 text-slate-800 border-slate-200')">
          {{ order.status || 'New' }}
        </span>
      </template>

      <template #cell-total_amount="{ item: order }">
        <span class="text-sm font-bold text-slate-900">₹{{ order.total_amount }}</span>
      </template>

      <template #cell-created_at="{ item: order }">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
          {{ formatDate(order.created_at) }}
        </span>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { 
  ChevronRight as ChevronRightIcon,
  Calendar as CalendarIcon,
  Filter as FilterIcon,
  Download as DownloadIcon,
  ShoppingBag as OrdersIcon,
  CheckCircle2 as SuccessIcon,
  Clock as PendingIcon
} from 'lucide-vue-next';

const filters = ref({
  startDate: '',
  endDate: ''
});

const orders = ref([]);
const stats = ref([
  { label: 'Total Orders', value: '0', icon: OrdersIcon, bgClass: 'bg-blue-50', iconClass: 'text-blue-600' },
  { label: 'Completed', value: '0', icon: SuccessIcon, bgClass: 'bg-emerald-50', iconClass: 'text-emerald-600' },
  { label: 'Pending', value: '0', icon: PendingIcon, bgClass: 'bg-amber-50', iconClass: 'text-amber-600' }
]);

const columns = computed(() => [
  { key: 'sn', label: 'S/No.', align: 'center', width: '60px' },
  { key: 'customer_name', label: 'Customer Name', align: 'left' },
  { key: 'order_number', label: 'Order ID', align: 'left' },
  { key: 'status', label: 'Status', align: 'left' },
  { key: 'total_amount', label: 'Amount', align: 'right' },
  { key: 'created_at', label: 'Date', align: 'left', type: 'date' },
]);

const fetchReport = async () => {
  try {
    const response = await axios.get('/api/v1/reports/orders', { params: filters.value });
    if (response.data.success) {
      orders.value = response.data.data.orders;
      stats.value[0].value = response.data.data.stats.total;
      stats.value[1].value = response.data.data.stats.completed;
      stats.value[2].value = response.data.data.stats.pending;
    }
  } catch (e) {
    console.error('Failed to fetch orders report', e);
  }
};

const handleFilter = () => {
  fetchReport();
};

const handleExport = async () => {
  try {
    const response = await axios.get('/api/v1/reports/orders/export', { 
      params: filters.value,
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
  new: "bg-slate-500/10 text-slate-500 border-slate-500/20",
  confirmed: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  "content not received": "bg-rose-500/10 text-rose-500 border-rose-500/20",
  "designing process": "bg-indigo-500/10 text-indigo-500 border-indigo-500/20",
  "printing process": "bg-sky-500/10 text-sky-500 border-sky-500/20",
  "packaging process": "bg-cyan-500/10 text-cyan-500 border-cyan-500/20",
  dispatched: "bg-primary/10 text-primary border-primary/20",
  "payment pending": "bg-orange-500/10 text-orange-500 border-orange-500/20",
  completed: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  cancelled: "bg-rose-500/10 text-rose-500 border-rose-500/20",
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString();
};

function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}

onMounted(() => {
  fetchReport();
});
</script>

<style scoped>
/* Hide the native calendar icon to avoid double icons, we use our own Lucide icon */
input[type="date"]::-webkit-calendar-picker-indicator {
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  cursor: pointer;
  opacity: 0;
}

/* Ensure the date text is clearly visible */
input[type="date"] {
  color-scheme: light;
}
</style>
