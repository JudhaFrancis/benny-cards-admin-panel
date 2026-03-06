<template>
  <div class="p-6 space-y-6 animate-in fade-in duration-500">
    <!-- Header -->
    <PageHeader
      title="Invoice Report"
      subtitle="Detailed overview of billing, invoicing, and payment statuses."
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
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">S/No.</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Invoice No.</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Order No.</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Amount</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(invoice, index) in invoices" :key="invoice.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ index + 1 }}</td>
              <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ invoice.invoice_number }}</td>
              <td class="px-6 py-4">
                <span class="text-sm font-medium text-slate-900">{{ invoice.customer_name }}</span>
              </td>
              <td class="px-6 py-4 text-sm font-semibold text-slate-900 italic">
                {{ invoice.order_number }}
              </td>
              <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">₹{{ invoice.total_amount }}</td>
              <td class="px-6 py-4 text-center">
                <span :class="cn('inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border', 
                      paymentStatusStyles[invoice.status?.toLowerCase()] || 'bg-slate-100 text-slate-800 border-slate-200')">
                  {{ invoice.status || 'Pending' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-500">{{ formatDate(invoice.created_at) }}</td>
            </tr>
            <tr v-if="invoices.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-slate-500">No invoices found for the selected period.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import PageHeader from "../../components/ui/PageHeader.vue";
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { 
  ChevronRight as ChevronRightIcon,
  Calendar as CalendarIcon,
  Filter as FilterIcon,
  Download as DownloadIcon,
  FileText as InvoiceIcon,
  CreditCard as PaidIcon,
  Clock as UnpaidIcon
} from 'lucide-vue-next';

const filters = ref({
  startDate: '',
  endDate: ''
});

const invoices = ref([]);
const stats = ref([
  { label: 'Total Invoiced', value: '₹0', icon: InvoiceIcon, bgClass: 'bg-indigo-50', iconClass: 'text-indigo-600' },
  { label: 'Total Paid', value: '₹0', icon: PaidIcon, bgClass: 'bg-emerald-50', iconClass: 'text-emerald-600' },
  { label: 'Outstanding', value: '₹0', icon: UnpaidIcon, bgClass: 'bg-rose-50', iconClass: 'text-rose-600' }
]);

const fetchReport = async () => {
  try {
    const response = await axios.get('/api/v1/reports/invoices', { params: filters.value });
    if (response.data.success) {
      invoices.value = response.data.data.invoices;
      stats.value[0].value = '₹' + response.data.data.stats.total;
      stats.value[1].value = '₹' + response.data.data.stats.paid;
      stats.value[2].value = '₹' + response.data.data.stats.outstanding;
    }
  } catch (e) {
    console.error('Failed to fetch invoice report', e);
  }
};

const handleFilter = () => {
  fetchReport();
};

const handleExport = () => {
  const url = `/admin/reports/invoices/export?startDate=${filters.value.startDate}&endDate=${filters.value.endDate}`;
  window.open(url, '_blank');
};

const paymentStatusStyles = {
  unpaid: "bg-slate-500/10 text-slate-500 border-slate-500/20",
  due: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  partial: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  paid: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  pending: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  completed: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
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
