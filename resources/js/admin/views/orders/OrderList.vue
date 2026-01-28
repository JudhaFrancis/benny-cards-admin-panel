<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Orders</h1>
        <p class="text-sm text-slate-500 mt-1">Manage and track your customer orders.</p>
      </div>
      <div class="flex items-center gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95">
          <PlusIcon class="h-4 w-4" />
          Create Order
        </button>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search Inner -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input 
              v-model="filters.search"
              type="text" 
              placeholder="Search orders, customers..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            >
          </div>

          <!-- Status Select -->
          <select 
            v-model="filters.status"
            class="bg-slate-50 border-slate-200 rounded-xl py-2 px-4 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-600"
          >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
          Showing {{ filteredOrders.length }} orders
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200">
      <div v-if="loading" class="p-20 flex flex-col items-center justify-center">
        <div class="h-10 w-10 border-4 border-primary/20 border-t-primary rounded-full animate-spin mb-4" />
        <p class="text-sm font-medium text-slate-400 animate-pulse">Fetching orders...</p>
      </div>
      
      <OrdersTable 
        v-else
        :orders="filteredOrders" 
        @view-info="handleView"
        @edit="handleEdit"
        @delete="handleDelete"
      />

      <!-- Pagination -->
      <div v-if="!loading && orders.length > 0" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
        <p class="text-xs text-slate-500 font-medium">
          Showing 1 to {{ filteredOrders.length }} of {{ filteredOrders.length }} results
        </p>
        <div class="flex items-center gap-2">
          <button class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-30 disabled:pointer-events-none transition-colors">
            <SearchIcon class="h-4 w-4 text-slate-600 rotate-180" /> <!-- Placeholder icon for prev -->
          </button>
          <button class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-30 disabled:pointer-events-none transition-colors">
            <SearchIcon class="h-4 w-4 text-slate-600" /> <!-- Placeholder icon for next -->
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { 
  Plus as PlusIcon, 
  Search as SearchIcon
} from 'lucide-vue-next';
import OrdersTable from '../../components/OrdersTable.vue';

const orders = ref([]);
const loading = ref(true);
const filters = reactive({
  search: '',
  status: ''
});

function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}

const fetchOrders = async () => {
  loading.value = true;
  // Simulate API delay
  setTimeout(() => {
    orders.value = [
      {
        id: '10254',
        customer: 'Johnathan Wick',
        orderDate: 'Oct 24, 2023',
        itemsCount: 3,
        orderStatus: 'delivered',
        amount: 1540.00,
        paidAmount: 1540.00,
        paymentStatus: 'paid'
      },
      {
        id: '10255',
        customer: 'Sara Connor',
        orderDate: 'Oct 25, 2023',
        itemsCount: 1,
        orderStatus: 'pending',
        amount: 120.50,
        paidAmount: 0.00,
        paymentStatus: 'unpaid'
      },
      {
        id: '10256',
        customer: 'Tony Stark',
        orderDate: 'Oct 26, 2023',
        itemsCount: 5,
        orderStatus: 'processing',
        amount: 12450.00,
        paidAmount: 5000.00,
        paymentStatus: 'partial'
      },
      {
        id: '10257',
        customer: 'Bruce Wayne',
        orderDate: 'Oct 27, 2023',
        itemsCount: 2,
        orderStatus: 'shipped',
        amount: 890.00,
        paidAmount: 890.00,
        paymentStatus: 'paid'
      },
      {
        id: '10258',
        customer: 'Peter Parker',
        orderDate: 'Oct 27, 2023',
        itemsCount: 4,
        orderStatus: 'cancelled',
        amount: 45.00,
        paidAmount: 0.00,
        paymentStatus: 'unpaid'
      }
    ];
    loading.value = false;
  }, 800);
};

const filteredOrders = computed(() => {
  return orders.value.filter(order => {
    const matchesSearch = order.customer.toLowerCase().includes(filters.search.toLowerCase()) || 
                         order.id.includes(filters.search);
    const matchesStatus = !filters.status || order.orderStatus === filters.status;
    return matchesSearch && matchesStatus;
  });
});

const handleView = (order) => {
  console.log('Viewing order:', order.id);
  alert(`Viewing Order #${order.id}`);
};

const handleEdit = (order) => {
  console.log('Editing order:', order.id);
  alert(`Editing Order #${order.id}`);
};

const handleDelete = (order) => {
  if(confirm(`Are you sure you want to delete order #${order.id}?`)) {
    orders.value = orders.value.filter(o => o.id !== order.id);
  }
};

onMounted(() => {
  fetchOrders();
});
</script>
