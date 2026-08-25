<template>
  <div class="min-h-screen bg-slate-100 p-8">
    <!-- Top Header -->
    <div class="flex justify-between items-center mb-14 max-w-7xl mx-auto border-b border-slate-200 pb-10">
      <!-- Title Section -->
      <div class="flex items-center gap-4">
        <div class="bg-primary/10 p-3 rounded-2xl">
          <Layers class="h-8 w-8 text-primary" />
        </div>
        <div>
          <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">
            Live Order Monitor
          </h1>
          <p class="text-slate-500 font-bold uppercase tracking-[0.15em] text-[9px] mt-0.5">
            Real-time Order Status & Tracking
          </p>
        </div>
      </div>

      <!-- Right Actions: Branch & Clock -->
      <div class="flex items-center gap-8">
        <!-- Branch Selector -->
        <div class="relative group cursor-pointer" @click="showBranchDropdown = !showBranchDropdown">
          <div class="text-right">
            <div class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Selected Branch</div>
            <div class="flex items-center gap-2 justify-end">
              <span class="text-lg font-black text-primary tracking-tight">{{ selectedBranch }}</span>
              <ChevronDown class="h-4 w-4 text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': showBranchDropdown }" />
            </div>
          </div>
          
          <!-- Dropdown -->
          <Transition name="fade-slide">
            <div v-if="showBranchDropdown" class="absolute top-full right-0 mt-4 w-56 bg-white border border-slate-200 rounded-2xl shadow-2xl z-50 py-2 backdrop-blur-xl bg-white/95">
              <div v-for="branch in branches" :key="branch" 
                   @click.stop="selectBranch(branch)"
                   class="px-6 py-3 text-left hover:bg-slate-50 transition-colors flex items-center justify-between group/item"
                   :class="{ 'bg-primary/5 text-primary': selectedBranch === branch }">
                <span class="text-xs font-bold uppercase tracking-wider" :class="selectedBranch === branch ? 'text-primary' : 'text-slate-600'">{{ branch }}</span>
                <div v-if="selectedBranch === branch" class="w-1.5 h-1.5 rounded-full bg-primary shadow-[0_0_8px_rgba(59,130,246,0.6)]"></div>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Divider -->
        <div class="h-10 w-px bg-slate-100"></div>

        <!-- Clock Section -->
        <div class="text-right">
          <div class="text-2xl font-black tracking-tight text-slate-900 tabular-nums leading-none">
            {{ currentTime }}
          </div>
          <div class="text-slate-400 font-bold text-[9px] uppercase tracking-widest mt-1">
            {{ currentDate }}
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content: Active Orders Pipeline -->
    <div class="max-w-7xl mx-auto overflow-hidden">
      <div class="px-8 py-0 flex justify-between items-center mb-6">
        <!-- Removed redundant heading -->
        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-full border border-slate-200 shadow-sm ml-auto">
          <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
          <span class="text-[9px] font-black tracking-widest text-slate-500 uppercase">System Synchronized</span>
        </div>
      </div>

      <div class="p-0 min-h-[500px]">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
          <TransitionGroup name="list">
            <div v-for="order in displayedOrders" :key="order.id" 
                 class="bg-white border-l-8 border-l-primary rounded-3xl p-6 shadow-2xl shadow-slate-300/60 hover:scale-[1.01] transition-all duration-300 group relative">
              
              <!-- Top Row: ID & Delivery Date -->
              <div class="flex items-center justify-between mb-5">
                <span class="text-xs font-black text-slate-500 bg-slate-50 px-4 py-1.5 rounded-lg tracking-widest uppercase border border-slate-100 shadow-sm">
                  ORDER ID: {{ order.order_number }}
                </span>
                <div v-if="order.delivery_date" class="flex items-center gap-1.5 bg-blue-50 text-blue-600 px-3 py-1 rounded-full border border-blue-100 shadow-sm">
                  <Calendar class="h-3.5 w-3.5" />
                  <span class="text-xs font-black tabular-nums">{{ formatDate(order.delivery_date) }}</span>
                </div>
              </div>

              <!-- Center: Customer Name -->
              <div class="py-1">
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-0.5">Customer Name</p>
                <h4 class="text-3xl font-black text-slate-900 tracking-tight uppercase truncate">
                  {{ order.customer_details?.name || 'Guest' }}
                </h4>
              </div>

              <!-- Bottom: Status Badge -->
              <div class="mt-5 pt-5 border-t border-slate-50 flex items-center justify-between">
                <div :class="['inline-flex items-center px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border shadow-sm', statusStyles[order.status?.toLowerCase()] || 'bg-slate-100 text-slate-400 border-slate-200']">
                  {{ order.status }}
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">In Progress</span>
                  <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_12px_rgba(16,185,129,0.5)]"></div>
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>
      </div>

      <!-- Page Indicator -->
      <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-10">
        <div v-for="p in totalPages" :key="p" 
             :class="['h-1.5 rounded-full transition-all duration-500', currentPage === p-1 ? 'w-8 bg-primary' : 'w-2 bg-slate-300']">
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { 
  ShoppingCart, 
  Activity, 
  TrendingUp, 
  Package,
  Layers,
  Calendar,
  DollarSign,
  MapPin,
  ChevronDown
} from "lucide-vue-next";

const currentTime = ref("");
const currentDate = ref("");
const recentOrders = ref([]);
const currentPage = ref(0);
import { useSettings } from "../../composables/useSettings";

const itemsPerPage = 4;

const { settings, fetchSettings } = useSettings();
fetchSettings();

const branches = computed(() => {
    let list = ["All Branches"];
    if (settings.value.branches) {
        list.push(...settings.value.branches.filter(b => b.active).map(b => b.name));
    } else {
        list.push("NGL", "MTM", "TVL", "Chennai", "Online");
    }
    return list;
});
const selectedBranch = ref("All Branches");
const showBranchDropdown = ref(false);

const selectBranch = (branch) => {
  selectedBranch.value = branch;
  showBranchDropdown.value = false;
  currentPage.value = 0; // Reset pagination
  fetchMonitorData();
};

const realStats = ref({
  total_orders: 0,
  completed_orders: 0,
  total_revenue: 0,
  pending_orders: 0
});

const displayedOrders = computed(() => {
  if (!recentOrders.value.length) return [];
  const start = currentPage.value * itemsPerPage;
  return recentOrders.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => Math.ceil(recentOrders.value.length / itemsPerPage));

const nextSet = () => {
  if (totalPages.value <= 1) return;
  currentPage.value = (currentPage.value + 1) % totalPages.value;
};

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
};

const monitorStats = ref([
  { label: "Daily Volume", value: "0", status: "Tracking Active", iconColor: "text-blue-500", icon: ShoppingCart },
  { label: "Today Sales", value: "₹0", status: "Ledger Update", iconColor: "text-emerald-500", icon: DollarSign },
  { label: "Fulfilled", value: "0", status: "Delivery Ready", iconColor: "text-indigo-500", icon: Package },
  { label: "Queue", value: "0", status: "Action Required", iconColor: "text-amber-500", icon: Layers },
]);

const fetchMonitorData = async () => {
  try {
    const [ordersRes, statsRes] = await Promise.all([
      axios.get("/api/v1/orders", { 
        params: { 
          limit: 12,
          branch: selectedBranch.value
        } 
      }),
      axios.get("/api/v1/dashboard/stats", {
        params: {
          branch: selectedBranch.value
        }
      })
    ]);

    if (ordersRes.data.success) {
      recentOrders.value = ordersRes.data.data.data;
    }
    
    if (statsRes.data.success) {
      const data = statsRes.data.data;
      realStats.value = data;
      
      monitorStats.value[0].value = data.total_orders;
      monitorStats.value[1].value = `₹${Number(data.total_revenue).toLocaleString()}`;
      monitorStats.value[2].value = data.completed_orders;
      monitorStats.value[3].value = data.total_orders - data.completed_orders;
    }
  } catch (error) {
    console.error("Monitor Data Sync Failed", error);
  }
};

let clockInterval;
let dataInterval;
let paginationInterval;

onMounted(() => {
  updateClock();
  fetchMonitorData();
  clockInterval = setInterval(updateClock, 1000);
  dataInterval = setInterval(fetchMonitorData, 30000); // Sync every 30s
  paginationInterval = setInterval(nextSet, 8000); // Rotate every 8s
});

onUnmounted(() => {
  clearInterval(clockInterval);
  clearInterval(dataInterval);
  clearInterval(paginationInterval);
});

const statusStyles = {
  completed: "bg-emerald-50 text-emerald-600 border-emerald-100",
  active: "bg-blue-50 text-blue-600 border-blue-100",
  pending: "bg-amber-50 text-amber-600 border-amber-100",
  cancelled: "bg-rose-50 text-rose-600 border-rose-100",
};

const paymentStyles = {
  paid: "bg-emerald-50 text-emerald-600 border-emerald-100",
  partial: "bg-amber-50 text-amber-600 border-amber-100",
  unpaid: "bg-rose-50 text-rose-600 border-rose-100",
};

const formatDate = (dateString) => {
  if (!dateString) return "No Date";
  const date = new Date(dateString);
  return date.toLocaleDateString('en-GB', { 
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
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

/* Transition for auto-pagination */
.list-enter-active,
.list-leave-active {
  transition: all 0.6s ease;
}
.list-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.list-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
.list-leave-active {
  position: absolute;
}

/* Branch Dropdown Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
