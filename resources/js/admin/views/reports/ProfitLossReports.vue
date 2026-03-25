<template>
  <div class="p-6 space-y-6 animate-in fade-in duration-500">
    <!-- Header -->
    <PageHeader
      title="Profit & Loss Report"
      subtitle="Comprehensive financial breakdown of revenue and expenditures."
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

    <!-- Main Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
            <TrendingUpIcon class="h-6 w-6" />
          </div>
          <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">Revenue</span>
        </div>
        <div>
          <p class="text-sm text-slate-500 font-medium">Total Income</p>
          <p class="text-2xl font-bold text-slate-900">₹{{ stats.revenue }}</p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
            <TrendingDownIcon class="h-6 w-6" />
          </div>
          <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded-lg">Expenses</span>
        </div>
        <div>
          <p class="text-sm text-slate-500 font-medium">Total Expenses</p>
          <p class="text-2xl font-bold text-slate-900">₹{{ stats.expenses }}</p>
        </div>
      </div>

      <div :class="cn('p-6 rounded-2xl border shadow-sm space-y-4', stats.profit >= 0 ? 'bg-emerald-50/30 border-emerald-100' : 'bg-rose-50/30 border-rose-100')">
        <div class="flex items-center justify-between">
          <div :class="cn('w-12 h-12 rounded-xl flex items-center justify-center', stats.profit >= 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600')">
            <DollarSignIcon class="h-6 w-6" />
          </div>
          <span :class="cn('text-xs font-bold px-2 py-1 rounded-lg', stats.profit >= 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600')">Net Profit</span>
        </div>
        <div>
          <p class="text-sm text-slate-500 font-medium">Net Profit / Loss</p>
          <p :class="cn('text-2xl font-bold', stats.profit >= 0 ? 'text-emerald-600' : 'text-rose-600')">₹{{ stats.profit }}</p>
        </div>
      </div>
    </div>

    <!-- Detailed Ledger/Analysis -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
      <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
        <h3 class="font-bold text-slate-900">Revenue & Expense Analysis</h3>
      </div>
      <div class="p-6">
        <div class="space-y-6">
          <!-- Income Section -->
          <div class="space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Operating Income</h4>
            <div class="space-y-2">
              <div v-for="item in ledger.income" :key="item.label" class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-600 font-medium">{{ item.label }}</span>
                <span class="text-sm font-bold text-slate-900">₹{{ item.amount }}</span>
              </div>
              <div class="flex items-center justify-between pt-2 border-t border-slate-100 font-bold">
                <span class="text-sm text-slate-900">Total Operating Income</span>
                <span class="text-sm text-blue-600">₹{{ stats.revenue }}</span>
              </div>
            </div>
          </div>

          <!-- Expense Section -->
          <div class="space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Operating Expenses</h4>
            <div class="space-y-2">
              <div v-for="item in ledger.expenses" :key="item.label" class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-600 font-medium">{{ item.label }}</span>
                <span class="text-sm font-bold text-slate-900">-₹{{ item.amount }}</span>
              </div>
              <div class="flex items-center justify-between pt-2 border-t border-slate-100 font-bold">
                <span class="text-sm text-slate-900">Total Operating Expenses</span>
                <span class="text-sm text-rose-600">₹{{ stats.expenses }}</span>
              </div>
            </div>
          </div>

          <!-- Net Section -->
          <div :class="cn('p-4 rounded-xl flex items-center justify-between font-bold text-lg mt-4', stats.profit >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700')">
            <span>Net Profit for the Period</span>
            <span>₹{{ stats.profit }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { 
  ChevronRight as ChevronRightIcon,
  Calendar as CalendarIcon,
  Filter as FilterIcon,
  Download as DownloadIcon,
  TrendingUp as TrendingUpIcon,
  TrendingDown as TrendingDownIcon,
  DollarSign as DollarSignIcon
} from 'lucide-vue-next';

const filters = ref({
  startDate: '',
  endDate: ''
});

const stats = ref({
  revenue: 0,
  expenses: 0,
  profit: 0
});

const ledger = ref({
  income: [
    { label: 'Card Sales', amount: 0 },
    { label: 'Printing Services', amount: 0 },
    { label: 'Custom Design Fees', amount: 0 }
  ],
  expenses: [
    { label: 'Material Cost (Raw Cards)', amount: 0 },
    { label: 'Ink & Printing Consumables', amount: 0 },
    { label: 'Packaging Materials', amount: 0 },
    { label: 'Shipping & Delivery', amount: 0 }
  ]
});

const fetchReport = async () => {
  try {
    const response = await axios.get('/api/v1/reports/profit-loss', { params: filters.value });
    if (response.data.success) {
      stats.value = response.data.data.stats;
      ledger.value = response.data.data.ledger;
    }
  } catch (e) {
    console.error('Failed to fetch profit-loss report', e);
  }
};

const handleFilter = () => {
  fetchReport();
};

const handleExport = () => {
  window.open(`/api/v1/admin/reports/profit-loss/export?start_date=${filters.value.startDate}&end_date=${filters.value.endDate}`, '_blank');
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
