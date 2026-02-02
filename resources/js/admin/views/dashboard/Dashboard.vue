<template>
  <div class="space-y-10 max-w-7xl mx-auto pb-12">
    <!-- Page Header -->
    <PageHeader title="Executive Dashboard">
      <template #subtitle>
        Welcome back,
        <span class="font-bold text-primary">{{ user?.name || "Admin" }}</span
        >! Here's your overview.
      </template>
      <template #actions>
        <div
          class="flex items-center gap-2 px-4 py-2 bg-slate-50 border border-slate-100 rounded-2xl"
        >
          <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
          <span
            class="text-[10px] font-bold text-slate-500 uppercase tracking-widest"
            >Systems Active</span
          >
        </div>
      </template>
    </PageHeader>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatsCard
        v-for="(stat, index) in stats"
        :key="stat.title"
        v-bind="stat"
        :delay="index * 100"
      />
    </div>

    <!-- Main Insights Grid -->
    <div
      class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-in fade-in duration-1000 delay-300"
    >
      <!-- Orders Flow -->
      <Card class="lg:col-span-2 overflow-hidden flex flex-col">
        <CardHeader
          class="flex flex-row items-center justify-between border-b border-slate-50/50"
        >
          <div>
            <CardTitle>Orders Overview</CardTitle>
            <p
              class="text-[10px] font-medium text-slate-400 mt-0.5 uppercase tracking-wider"
            >
              Net distribution over 7 months
            </p>
          </div>
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5">
              <span
                class="h-1.5 w-1.5 rounded-full bg-primary/20 border border-primary/30"
              ></span>
              <span class="text-[9px] font-bold text-slate-400 uppercase"
                >Projection</span
              >
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-1.5 w-1.5 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--primary),0.5)]"
              ></span>
              <span class="text-[9px] font-bold text-slate-900 uppercase"
                >Actual</span
              >
            </div>
          </div>
        </CardHeader>
        <CardContent class="flex-1 bg-gradient-to-b from-white to-slate-50/30">
          <OrdersChart />
        </CardContent>
      </Card>

      <!-- Payments Distribution -->
      <Card class="overflow-hidden flex flex-col">
        <CardHeader class="border-b border-slate-50/50">
          <CardTitle>Payments Status</CardTitle>
          <p
            class="text-[10px] font-medium text-slate-400 mt-0.5 uppercase tracking-wider"
          >
            Current billing cycles
          </p>
        </CardHeader>
        <CardContent class="flex-1 flex flex-col justify-center">
          <PaymentsChart />
        </CardContent>
      </Card>
    </div>

    <!-- Recent Activity Section -->
    <div class="space-y-4 animate-in fade-in duration-1000 delay-500">
      <div class="flex items-center justify-between px-2">
        <h2
          class="text-sm font-bold text-slate-900 uppercase tracking-[0.15em]"
        >
          Recent Activity
        </h2>
        <router-link
          to="/orders"
          class="text-[10px] font-bold text-primary hover:text-primary/80 transition-colors tracking-widest uppercase flex items-center gap-1"
        >
          Full Report ➔
        </router-link>
      </div>

      <Card class="overflow-hidden">
        <RecentOrdersTable />
        <div
          class="p-4 bg-slate-50/50 border-t border-slate-50 flex items-center justify-center"
        >
          <div
            class="flex items-center gap-2 px-3 py-1 bg-white border border-slate-100 rounded-full shadow-sm"
          >
            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
            <span
              class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"
              >End of Recent Transaction Log</span
            >
            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
          </div>
        </div>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ShoppingCart, CheckCircle, Clock, DollarSign } from "lucide-vue-next";
import PageHeader from "../../components/ui/PageHeader.vue";
import Card from "../../components/ui/Card.vue";
import CardHeader from "../../components/ui/CardHeader.vue";
import CardTitle from "../../components/ui/CardTitle.vue";
import CardContent from "../../components/ui/CardContent.vue";
import StatsCard from "../../components/dashboard/StatsCard.vue";
import OrdersChart from "../../components/dashboard/OrdersChart.vue";
import PaymentsChart from "../../components/dashboard/PaymentsChart.vue";
import RecentOrdersTable from "../../components/dashboard/RecentOrdersTable.vue";
import { useAuth } from "../../composables/useAuth";

const { user } = useAuth();

const stats = [
  {
    title: "Total Orders",
    value: "1,234",
    change: "+12.5%",
    changeType: "positive",
    icon: ShoppingCart,
  },
  {
    title: "Completed Orders",
    value: "1,089",
    change: "+8.2%",
    changeType: "positive",
    icon: CheckCircle,
  },
  {
    title: "Pending Payments",
    value: "45",
    change: "-3.1%",
    changeType: "negative",
    icon: Clock,
  },
  {
    title: "Total Revenue",
    value: "$89,420",
    change: "+15.3%",
    changeType: "positive",
    icon: DollarSign,
  },
];
</script>

<style scoped>
.animate-in {
  animation-fill-mode: forwards;
}
</style>
