<template>
  <DataTable :columns="columns" :items="recentOrders" :loading="loading" no-wrapper>
    <template #cell-order_number="{ item: order }">
      <span
        class="text-sm font-bold text-slate-900 leading-none group-hover:text-primary transition-colors cursor-pointer tracking-tight"
      >
        {{ order.order_number }}
      </span>
    </template>

    <template #cell-customer="{ item: order }">
      <div class="flex items-center gap-2">
        <div
          class="h-6 w-6 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500"
        >
          {{ (order.customer_details?.name || "C").substring(0, 1) }}
        </div>
        <span class="text-sm font-medium text-slate-700">{{
          order.customer_details?.name || "Guest"
        }}</span>
      </div>
    </template>

    <template #cell-order_date="{ item: order }">
      <span class="text-sm text-slate-500 font-medium">{{ formatDate(order.order_date) }}</span>
    </template>

    <template #cell-total_amount="{ item: order }">
      <span class="font-bold text-slate-900 text-sm">${{ Number(order.total_amount).toFixed(2) }}</span>
    </template>

    <template #cell-status="{ item: order }">
      <Badge variant="outline" :class="statusStyles[order.status?.toLowerCase()] || 'bg-slate-50 text-slate-600'">
        {{ order.status }}
      </Badge>
    </template>

    <template #cell-payment="{ item: order }">
      <Badge variant="outline" :class="paymentStyles[order.payment_status?.toLowerCase()] || 'bg-slate-50 text-slate-600'">
        {{ order.payment_status }}
      </Badge>
    </template>
  </DataTable>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Badge from "../ui/Badge.vue";
import DataTable from "../ui/DataTable.vue";

const loading = ref(true);
const recentOrders = ref([]);

const columns = [
  { key: "order_number", label: "Order", align: "left" },
  { key: "customer", label: "Customer", align: "left" },
  { key: "order_date", label: "Date", align: "left" },
  { key: "total_amount", label: "Amount", align: "left" },
  { key: "status", label: "Status", align: "left" },
  { key: "payment", label: "Payment", align: "right" },
];

const fetchRecentOrders = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/orders", { params: { limit: 5 } });
    if (response.data.success) {
      recentOrders.value = response.data.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch recent orders", error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchRecentOrders);

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
  });
};

const statusStyles = {
  completed:
    "bg-emerald-50 text-emerald-600 border-emerald-100 uppercase tracking-widest text-[9px]",
  pending:
    "bg-amber-50 text-amber-600 border-amber-100 uppercase tracking-widest text-[9px]",
  processing:
    "bg-blue-50 text-blue-600 border-blue-100 uppercase tracking-widest text-[9px]",
  cancelled:
    "bg-rose-50 text-rose-600 border-rose-100 uppercase tracking-widest text-[9px]",
};

const paymentStyles = {
  paid: "bg-emerald-50 text-emerald-600 border-emerald-100 uppercase tracking-widest text-[9px]",
  due: "bg-amber-50 text-amber-600 border-amber-100 uppercase tracking-widest text-[9px]",
  unpaid: "bg-rose-50 text-rose-600 border-rose-100 uppercase tracking-widest text-[9px]",
};
</script>
