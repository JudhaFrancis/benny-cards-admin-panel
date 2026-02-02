<template>
  <DataTable :columns="columns" :items="recentOrders" no-wrapper>
    <template #cell-id="{ item: order }">
      <span
        class="text-sm font-bold text-slate-900 leading-none group-hover:text-primary transition-colors cursor-pointer tracking-tight"
      >
        {{ order.id }}
      </span>
    </template>

    <template #cell-customer="{ item: order }">
      <div class="flex items-center gap-2">
        <div
          class="h-6 w-6 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500"
        >
          {{ order.customer.substring(0, 1) }}
        </div>
        <span class="text-sm font-medium text-slate-700">{{
          order.customer
        }}</span>
      </div>
    </template>

    <template #cell-date="{ item: order }">
      <span class="text-sm text-slate-500 font-medium">{{ order.date }}</span>
    </template>

    <template #cell-amount="{ item: order }">
      <span class="font-bold text-slate-900 text-sm">{{ order.amount }}</span>
    </template>

    <template #cell-status="{ item: order }">
      <Badge variant="outline" :class="statusStyles[order.status]">
        {{ order.status }}
      </Badge>
    </template>
  </DataTable>
</template>

<script setup>
import Badge from "../ui/Badge.vue";
import DataTable from "../ui/DataTable.vue";

const columns = [
  { key: "id", label: "Order ID", align: "left" },
  { key: "customer", label: "Customer", align: "left" },
  { key: "date", label: "Date", align: "left" },
  { key: "amount", label: "Amount", align: "left" },
  { key: "status", label: "Status", align: "right" },
];

const recentOrders = [
  {
    id: "ORD-2024-001",
    customer: "John Doe",
    date: "2024-01-15",
    amount: "$125.00",
    status: "completed",
  },
  {
    id: "ORD-2024-002",
    customer: "Jane Smith",
    date: "2024-01-14",
    amount: "$89.50",
    status: "pending",
  },
  {
    id: "ORD-2024-003",
    customer: "Mike Johnson",
    date: "2024-01-14",
    amount: "$234.00",
    status: "processing",
  },
  {
    id: "ORD-2024-004",
    customer: "Sarah Williams",
    date: "2024-01-13",
    amount: "$67.25",
    status: "completed",
  },
  {
    id: "ORD-2024-005",
    customer: "Robert Brown",
    date: "2024-01-13",
    amount: "$156.00",
    status: "cancelled",
  },
];

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
</script>
