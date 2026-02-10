<template>
  <div class="min-h-screen bg-[#0F172A] text-white p-8 font-sans overflow-hidden">
    <!-- Top Header -->
    <div class="flex justify-between items-center mb-10">
      <div>
        <h1 class="text-4xl font-black tracking-tighter text-white flex items-center gap-3">
          <span class="w-3 h-10 bg-primary rounded-full animate-pulse"></span>
          LIVE OPERATIONS MONITOR
        </h1>
        <p class="text-slate-400 font-bold uppercase tracking-[0.3em] text-xs mt-2 pl-6">
          System Core • Real-time Data Stream
        </p>
      </div>
      <div class="text-right">
        <div class="text-5xl font-mono font-bold tracking-tight text-primary tabular-nums">
          {{ currentTime }}
        </div>
        <div class="text-slate-500 font-bold text-sm uppercase tracking-widest mt-1">
          {{ currentDate }}
        </div>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-4 gap-6 mb-10">
      <div v-for="stat in monitorStats" :key="stat.label" 
           class="bg-slate-900/50 border border-slate-800 p-6 rounded-[2rem] backdrop-blur-xl relative overflow-hidden group hover:border-primary/50 transition-all duration-500">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity">
          <component :is="stat.icon" class="w-24 h-24" />
        </div>
        <p class="text-slate-500 text-xs font-black uppercase tracking-widest mb-1">{{ stat.label }}</p>
        <div class="text-4xl font-bold tracking-tight mb-2 tabular-nums">{{ stat.value }}</div>
        <div class="flex items-center gap-2">
            <span :class="['h-2 w-2 rounded-full', stat.colorClass]"></span>
            <span class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">{{ stat.status }}</span>
        </div>
      </div>
    </div>

    <!-- Main Content: Live Orders Table -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-[3rem] overflow-hidden backdrop-blur-md relative">
      <div class="p-8 border-b border-slate-800 flex justify-between items-center bg-slate-900/80">
        <h2 class="text-xl font-bold tracking-tight flex items-center gap-3">
          <Activity class="h-5 w-5 text-primary" />
          ACTIVE ORDER PIPELINE
        </h2>
        <div class="flex items-center gap-2 bg-slate-800/50 px-4 py-2 rounded-full border border-slate-700">
          <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></div>
          <span class="text-[10px] font-bold tracking-widest text-slate-300 uppercase">Live Feed Active</span>
        </div>
      </div>

      <div class="p-0 overflow-x-auto min-h-[500px]">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="text-slate-500 text-[10px] uppercase tracking-[0.2em] font-black italic">
              <th class="px-8 py-6 border-b border-slate-800">Order ID</th>
              <th class="px-8 py-6 border-b border-slate-800">Customer</th>
              <th class="px-8 py-6 border-b border-slate-800">Status</th>
              <th class="px-8 py-6 border-b border-slate-800">Payment</th>
              <th class="px-8 py-6 border-b border-slate-800 text-right">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/50">
            <tr v-for="order in recentOrders" :key="order.id" 
                class="hover:bg-white/5 transition-colors group">
              <td class="px-8 py-6">
                <div class="font-mono font-bold text-primary text-lg tracking-tight group-hover:scale-105 transition-transform origin-left">
                  {{ order.order_number }}
                </div>
              </td>
              <td class="px-8 py-6">
                <div class="flex flex-col">
                  <span class="font-bold text-slate-200">{{ order.customer_details?.name || 'Guest' }}</span>
                  <span class="text-[10px] text-slate-500 font-bold tracking-wider">{{ order.customer_details?.city_1 || 'General' }}</span>
                </div>
              </td>
              <td class="px-8 py-6">
                <div :class="['inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border', statusStyles[order.status?.toLowerCase()] || 'bg-slate-500/10 text-slate-400 border-slate-500/20']">
                  {{ order.status }}
                </div>
              </td>
              <td class="px-8 py-6">
                <div :class="['inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border', paymentStyles[order.payment_status?.toLowerCase()] || 'bg-slate-500/10 text-slate-400 border-slate-500/20']">
                  {{ order.payment_status }}
                </div>
              </td>
              <td class="px-8 py-6 text-right">
                <div class="text-2xl font-black tracking-tighter text-white tabular-nums">
                  ${{ Number(order.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer Overlay -->
      <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-[#0F172A] to-transparent pointer-events-none"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { 
  ShoppingCart, 
  DollarSign, 
  Activity, 
  TrendingUp, 
  Package,
  Layers
} from "lucide-vue-next";

const currentTime = ref("");
const currentDate = ref("");
const recentOrders = ref([]);
const realStats = ref({
  total_orders: 0,
  completed_orders: 0,
  total_revenue: 0,
  pending_orders: 0
});

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

const monitorStats = ref([
  { label: "Daily Volume", value: "0", status: "Tracking Active", colorClass: "bg-emerald-500", icon: ShoppingCart },
  { label: "Today Sales", value: "$0.00", status: "Ledger Update", colorClass: "bg-primary", icon: DollarSign },
  { label: "Fulfilled", value: "0", status: "Delivery Ready", colorClass: "bg-blue-500", icon: Package },
  { label: "Queue", value: "0", status: "Action Required", colorClass: "bg-amber-500", icon: Layers },
]);

const fetchMonitorData = async () => {
  try {
    const [ordersRes, statsRes] = await Promise.all([
      axios.get("/api/v1/orders", { params: { limit: 12 } }),
      axios.get("/api/v1/dashboard/stats")
    ]);

    if (ordersRes.data.success) {
      recentOrders.value = ordersRes.data.data.data;
    }
    
    if (statsRes.data.success) {
      const data = statsRes.data.data;
      realStats.value = data;
      
      monitorStats.value[0].value = data.total_orders;
      monitorStats.value[1].value = `$${Number(data.total_revenue).toLocaleString()}`;
      monitorStats.value[2].value = data.completed_orders;
      monitorStats.value[3].value = data.total_orders - data.completed_orders;
    }
  } catch (error) {
    console.error("Monitor Data Sync Failed", error);
  }
};

let clockInterval;
let dataInterval;

onMounted(() => {
  updateClock();
  fetchMonitorData();
  clockInterval = setInterval(updateClock, 1000);
  dataInterval = setInterval(fetchMonitorData, 30000); // Sync every 30s
});

onUnmounted(() => {
  clearInterval(clockInterval);
  clearInterval(dataInterval);
});

const statusStyles = {
  completed: "bg-emerald-500/10 text-emerald-400 border-emerald-500/20",
  active: "bg-blue-500/10 text-blue-400 border-blue-500/20",
  pending: "bg-amber-500/10 text-amber-400 border-amber-500/20",
  cancelled: "bg-rose-500/10 text-rose-400 border-rose-500/20",
};

const paymentStyles = {
  paid: "bg-emerald-500/10 text-emerald-400 border-emerald-500/20",
  due: "bg-amber-500/10 text-amber-400 border-amber-500/20",
  unpaid: "bg-rose-500/10 text-rose-400 border-rose-500/20",
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@700&display=swap');

.font-sans {
  font-family: 'Space Grotesk', sans-serif;
}

.font-mono {
  font-family: 'JetBrains Mono', monospace;
}

::-webkit-scrollbar {
  display: none;
}

table tr:last-child td {
    border-bottom: none;
}
</style>
