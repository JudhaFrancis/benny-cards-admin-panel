<template>
  <div class="overflow-x-auto custom-scrollbar">
    <table class="w-full border-separate border-spacing-0">
      <thead>
        <tr class="border-b border-slate-200">
          <th class="px-4 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Order ID</th>
          <th class="px-4 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Customer</th>
          <th class="px-4 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Order Date</th>
          <th class="px-4 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Items</th>
          <th class="px-4 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Order Status</th>
          <th class="px-4 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Amount</th>
          <th class="px-4 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Paid</th>
          <th class="px-4 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Payment</th>
          <th class="px-4 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr 
          v-for="order in orders" 
          :key="order.id"
          class="group hover:bg-slate-50/50 transition-colors duration-200"
        >
          <td class="px-4 py-4 text-sm font-semibold text-slate-900 italic">
            #{{ order.id }}
          </td>
          <td class="px-4 py-4">
            <div class="flex flex-col">
              <span class="text-sm font-medium text-slate-900">{{ order.customer }}</span>
            </div>
          </td>
          <td class="px-4 py-4 text-sm text-slate-500">
            {{ order.orderDate }}
          </td>
          <td class="px-4 py-4 text-center text-sm font-medium text-slate-900">
            {{ order.itemsCount }}
          </td>
          <td class="px-4 py-4">
            <span
              :class="cn(
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
                orderStatusStyles[order.orderStatus.toLowerCase()] || 'bg-slate-100 text-slate-800 border-slate-200'
              )"
            >
              {{ capitalize(order.orderStatus) }}
            </span>
          </td>
          <td class="px-4 py-4 text-right text-sm font-bold text-slate-900">
            ${{ order.amount.toFixed(2) }}
          </td>
          <td class="px-4 py-4 text-right text-sm font-medium text-slate-600">
            ${{ order.paidAmount.toFixed(2) }}
          </td>
          <td class="px-4 py-4">
            <span
              :class="cn(
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
                paymentStatusStyles[order.paymentStatus.toLowerCase()] || 'bg-slate-100 text-slate-800 border-slate-200'
              )"
            >
              {{ capitalize(order.paymentStatus) }}
            </span>
          </td>
          <td class="px-4 py-4 text-right">
            <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <button
                @click="$emit('view-info', order)"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
                title="View Info"
              >
                <Eye class="h-4 w-4" />
              </button>
              <button
                @click="$emit('edit', order)"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
                title="Edit Order"
              >
                <Pencil class="h-4 w-4" />
              </button>
              <button
                @click="$emit('delete', order)"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
                title="Delete Order"
              >
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="orders.length === 0" class="text-center py-20 animate-in fade-in duration-500">
      <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 mb-4">
        <ShoppingCartIcon class="h-6 w-6 text-slate-400" />
      </div>
      <p class="text-sm font-medium text-slate-500 dark:text-slate-400 italic">
        No orders found matching your criteria.
      </p>
    </div>
  </div>
</template>

<script setup>
import { 
  Eye, 
  Pencil, 
  Trash2,
  ShoppingCart as ShoppingCartIcon
} from 'lucide-vue-next';

const props = defineProps({
  orders: {
    type: Array,
    required: true
  }
});

defineEmits(['view-info', 'edit', 'delete']);

const orderStatusStyles = {
  pending: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  processing: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  shipped: "bg-primary/10 text-primary border-primary/20",
  delivered: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  cancelled: "bg-rose-500/10 text-rose-500 border-rose-500/20",
};

const paymentStatusStyles = {
  paid: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  partial: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  unpaid: "bg-rose-500/10 text-rose-500 border-rose-500/20",
};

function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}

const capitalize = (str) => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
}
</style>
