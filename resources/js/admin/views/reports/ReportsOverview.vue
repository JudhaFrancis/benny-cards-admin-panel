<template>
  <div class="p-6 space-y-8 animate-in fade-in duration-500">
    <!-- Header -->
    <PageHeader
      title="Reports Overview"
      subtitle="Access all business reports and analytics in one place"
    />

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="report in reports" :key="report.title" 
           class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-primary/30 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300">
        <div class="p-6 flex flex-col items-center text-center space-y-4">
          <!-- Icon Container -->
          <div :class="cn('w-16 h-16 rounded-2xl flex items-center justify-center transition-colors duration-300', report.bgClass)">
            <component :is="report.icon" :class="cn('h-8 w-8', report.iconClass)" />
          </div>

          <!-- Content -->
          <div class="space-y-1">
            <h3 class="text-lg font-bold text-slate-900 group-hover:text-primary transition-colors">{{ report.title }}</h3>
            <p class="text-sm text-slate-500 leading-relaxed">{{ report.description }}</p>
          </div>

          <!-- Action -->
          <router-link :to="report.url" class="w-full">
            <button :class="cn('w-full flex items-center justify-center gap-2 py-3 rounded-xl transition-all duration-300 font-semibold', report.buttonClass)">
              View Report
              <ChevronRightIcon class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
            </button>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import PageHeader from "../../components/ui/layout/PageHeader.vue";
import { 
  ChevronRight as ChevronRightIcon,
  ShoppingBag as OrdersIcon,
  FileText as InvoiceIcon,
  TrendingUp as ProfitIcon
} from 'lucide-vue-next';

const reports = [
  {
    title: 'Orders Report',
    description: 'Complete summary of all orders, statuses, and customer details',
    url: '/reports/orders',
    icon: OrdersIcon,
    bgClass: 'bg-blue-50 group-hover:bg-blue-100',
    iconClass: 'text-blue-600',
    buttonClass: 'bg-white border border-slate-200 text-slate-600 group-hover:bg-primary group-hover:text-white group-hover:border-primary shadow-sm hover:shadow-lg hover:shadow-primary/20'
  },
  {
    title: 'Invoice Report',
    description: 'Track and filter all generated invoices and payment history',
    url: '/reports/invoices',
    icon: InvoiceIcon,
    bgClass: 'bg-purple-50 group-hover:bg-purple-100',
    iconClass: 'text-purple-600',
    buttonClass: 'bg-white border border-slate-200 text-slate-600 group-hover:bg-primary group-hover:text-white group-hover:border-primary shadow-sm hover:shadow-lg hover:shadow-primary/20'
  },
  {
    title: 'Profit & Loss',
    description: 'Detailed analysis of business revenue and expenses',
    url: '/reports/profit-loss',
    icon: ProfitIcon,
    bgClass: 'bg-emerald-50 group-hover:bg-emerald-100',
    iconClass: 'text-emerald-600',
    buttonClass: 'bg-white border border-slate-200 text-slate-600 group-hover:bg-primary group-hover:text-white group-hover:border-primary shadow-sm hover:shadow-lg hover:shadow-primary/20'
  }
];

function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}
</script>
