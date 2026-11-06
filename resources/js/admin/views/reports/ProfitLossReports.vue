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
            Profit & Loss Report
          </h1>
          <p class="text-sm text-slate-500 mt-1">Comprehensive financial breakdown of revenue and expenditures.</p>
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
        label="Revenue"
        :value="'₹' + stats.revenue"
        :icon="TrendingUpIcon"
        bg-class="bg-indigo-500 shadow-indigo-200"
      />
      <SummaryCard 
        label="Expenses"
        :value="'₹' + stats.expenses"
        :icon="TrendingDownIcon"
        bg-class="bg-rose-500 shadow-rose-200"
      />
      <SummaryCard 
        :label="stats.profit >= 0 ? 'Net Profit' : 'Net Loss'"
        :value="'₹' + Math.abs(stats.profit)"
        :icon="DollarSignIcon"
        :bg-class="stats.profit >= 0 ? 'bg-emerald-500 shadow-emerald-200' : 'bg-rose-600 shadow-rose-300'"
      />
    </div>

    <!-- Detailed Ledger/Analysis -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
      <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center gap-3">
        <div class="h-8 w-1 bg-primary rounded-full"></div>
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Revenue & Expense Analysis</h3>
      </div>
      <div class="p-6">
        <div class="space-y-8">
          <!-- Income Section -->
          <div class="space-y-4">
            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Operating Income</h4>
            <div class="space-y-1 bg-slate-50/50 rounded-2xl p-4 border border-slate-100">
              <div v-for="item in ledger.income" :key="item.label" class="flex items-center justify-between py-2 px-2">
                <span class="text-sm text-slate-600 font-medium">{{ item.label }}</span>
                <span class="text-sm font-bold text-slate-900">₹{{ item.amount }}</span>
              </div>
              <div class="flex items-center justify-between mt-2 pt-3 border-t border-slate-200 px-2 font-bold">
                <span class="text-sm text-slate-900 uppercase tracking-tight">Total Operating Income</span>
                <span class="text-sm text-indigo-600">₹{{ stats.revenue }}</span>
              </div>
            </div>
          </div>

          <!-- Expense Section -->
          <div class="space-y-4">
            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Operating Expenses</h4>
            <div class="space-y-1 bg-slate-50/50 rounded-2xl p-4 border border-slate-100">
              <div v-for="item in ledger.expenses" :key="item.label" class="flex items-center justify-between py-2 px-2">
                <span class="text-sm text-slate-600 font-medium">{{ item.label }}</span>
                <span class="text-sm font-bold text-slate-900">-₹{{ item.amount }}</span>
              </div>
              <div class="flex items-center justify-between mt-2 pt-3 border-t border-slate-200 px-2 font-bold">
                <span class="text-sm text-slate-900 uppercase tracking-tight">Total Operating Expenses</span>
                <span class="text-sm text-rose-600">₹{{ stats.expenses }}</span>
              </div>
            </div>
          </div>

          <!-- Net Section -->
          <div :class="cn('p-5 rounded-2xl flex items-center justify-between font-bold text-lg mt-6 shadow-sm border transition-colors', 
               stats.profit >= 0 ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-rose-50 border-rose-100 text-rose-700')">
            <span class="tracking-tight">Net Profit for the Period</span>
            <span class="text-2xl font-black">₹{{ stats.profit }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { 
  ArrowLeft as ArrowLeftIcon,
  TrendingUp as TrendingUpIcon,
  TrendingDown as TrendingDownIcon,
  DollarSign as DollarSignIcon
} from 'lucide-vue-next';
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import SummaryCard from "../../components/reports/SummaryCard.vue";

const loading = ref(false);
const filters = ref({
  filter_type: "day",
  filter_option: "today",
  from_date: new Date().toISOString().split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
});

const stats = ref({
  revenue: 0,
  expenses: 0,
  profit: 0
});

const ledger = ref({
  income: [],
  expenses: []
});

const fetchReport = async () => {
  loading.value = true;
  try {
    const params = {
      startDate: filters.value.from_date,
      endDate: filters.value.to_date,
      ...filters.value
    };
    const response = await axios.get('/api/v1/reports/profit-loss', { params });
    if (response.data.success) {
      stats.value = response.data.data.stats;
      ledger.value = response.data.data.ledger;
    }
  } catch (e) {
    console.error('Failed to fetch profit-loss report', e);
  } finally {
    loading.value = false;
  }
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
