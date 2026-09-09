<template>
  <DataTable :columns="columns" :items="recentOrders" :loading="loading" no-wrapper>
    <template #cell-order_number="{ item: order }">
      <span class="font-semibold text-slate-900 italic">
        {{ order.order_number?.replace(/-(?:[A-Z]{3})\d{2}/, (m) => m.slice(0, 4)) }}
      </span>
    </template>

    <template #cell-customer="{ item: order }">
      <div class="flex flex-col">
        <span class="text-sm font-medium text-slate-900">{{
          order.customer_details?.name || "N/A"
        }}</span>
      </div>
    </template>

    <template #cell-order_date="{ item: order }">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
        {{ formatDate(order.order_date) }}
      </span>
    </template>

    <template #cell-total_amount="{ item: order }">
      <span class="font-bold text-slate-900">₹{{ Number(order.total_amount).toFixed(2) }}</span>
    </template>

    <template #cell-status="{ item: order }">
      <span
        :class="
          cn(
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
            (order.tracking_status_label &&
              orderStatusStyles[order.tracking_status_label.toLowerCase()]) ||
              'bg-slate-100 text-slate-800 border-slate-200',
          )
        "
      >
        {{ order.tracking_status_label || order.status || "New" }}
      </span>
    </template>

    <template #cell-payment="{ item: order }">
      <span
        :class="
          cn(
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
            (order.payment_status &&
              paymentStatusStyles[order.payment_status.toLowerCase()]) ||
              'bg-slate-100 text-slate-800 border-slate-200',
          )
        "
      >
        {{ capitalize(order.payment_status) }}
      </span>
    </template>
  </DataTable>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import DataTable from "../ui/data-table/DataTable.vue";

const loading = ref(true);
const recentOrders = ref([]);

const columns = [
  { key: "sn", label: "S.No", align: "left", width: "60px" },
  { key: "order_number", label: "Order", align: "left" },
  { key: "customer", label: "Customer", align: "left", filterKey: "customer_details.name" },
  { key: "order_date", label: "Date", align: "left", type: "date" },
  { key: "total_amount", label: "Amount", align: "left" },
  { key: "status", label: "Status", align: "left", filterKey: "tracking_status_label" },
  { key: "payment", label: "Payment", align: "right", filterKey: "payment_status" },
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

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

const orderStatusStyles = {
  new: "bg-slate-500/10 text-slate-500 border-slate-500/20",
  confirmed: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  "content not received": "bg-rose-500/10 text-rose-500 border-rose-500/20",
  "designing process": "bg-indigo-500/10 text-indigo-500 border-indigo-500/20",
  "printing process": "bg-sky-500/10 text-sky-500 border-sky-500/20",
  "packaging process": "bg-cyan-500/10 text-cyan-500 border-cyan-500/20",
  dispatched: "bg-primary/10 text-primary border-primary/20",
  "payment pending": "bg-orange-500/10 text-orange-500 border-orange-500/20",
  completed: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  cancelled: "bg-rose-500/10 text-rose-500 border-rose-500/20",
};

const paymentStatusStyles = {
  paid: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  partial: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  unpaid: "bg-rose-500/10 text-rose-500 border-rose-500/20",
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const capitalize = (str) => {
  if (!str) return "";
  return str.charAt(0).toUpperCase() + str.slice(1);
};
</script>
