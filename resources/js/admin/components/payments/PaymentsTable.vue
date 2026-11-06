<template>
  <DataTable
    :columns="columns"
    :items="payments"
    :loading="loading"
    @row-click="$emit('view', $event)"
    empty-text="No payments found matching your criteria."
  >
    <!-- Custom Row Cells -->
    <template #cell-sn="{ item: payment }">
      <span class="text-slate-500 font-medium">{{ payment.sn }}</span>
    </template>

    <template #cell-payment_number="{ item: payment }">
      <span class="font-semibold text-slate-900">{{
        payment.payment_number
      }}</span>
    </template>

    <template #cell-order="{ item: payment }">
      <span v-if="payment.order" class="text-sm text-slate-600 font-medium"
        >{{ payment.order.order_number?.replace(/-(?:[A-Z]{3})\d{2}/, (m) => m.slice(0, 4)) }}</span
      >
      <span v-else class="text-slate-400 italic font-medium">None</span>
    </template>

    <template #cell-amount="{ item: payment }">
      <span class="font-bold text-slate-900"
        >₹{{
          Number(payment.amount).toLocaleString("en-IN", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          })
        }}</span
      >
    </template>

    <template #cell-method="{ item: payment }">
      <div class="flex flex-wrap gap-1 max-w-[200px]">
        <span 
          v-for="(method, idx) in [...new Set((payment.payment_details || []).map(d => d.method))]" 
          :key="idx"
          class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200 capitalize"
        >
          {{ method.replace('_', ' ') }}
        </span>
      </div>
    </template>

    <template #cell-status="{ item: payment }">
      <span
        :class="
          cn(
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
            statusStyles[payment.payment_status] ||
              'bg-slate-100 text-slate-800 border-slate-200',
          )
        "
      >
        {{ capitalize(payment.payment_status) }}
      </span>
    </template>

    <template #cell-date="{ item: payment }">
      <span class="text-slate-500 font-medium">{{
        formatDate(payment.payment_date)
      }}</span>
    </template>

    <template #cell-actions="{ item: payment }">
      <div class="flex justify-end gap-1.5 transition-opacity duration-200">
        <button
          v-if="canView"
          @click.stop="$emit('view', payment)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
          title="View Info"
        >
          <Eye class="h-4 w-4" />
        </button>
        <button
          v-if="canEdit"
          @click.stop="$emit('edit', payment)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
          title="Edit Payment"
        >
          <Pencil class="h-4 w-4" />
        </button>
        <button
          v-if="canDelete"
          @click.stop="$emit('delete', payment)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
          title="Delete Payment"
        >
          <Trash2 class="h-4 w-4" />
        </button>
      </div>
    </template>
  </DataTable>
</template>

<script setup>
import { Eye, Pencil, Trash2 } from "lucide-vue-next";
import DataTable from "../ui/data-table/DataTable.vue";

const props = defineProps({
  payments: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  canView: {
    type: Boolean,
    default: false,
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
  canDelete: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["view", "edit", "delete"]);

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "payment_number", label: "Payment", align: "left", filterKey: "payment_number" },
  { key: "order", label: "Order", align: "left", filterKey: "order.order_number" },
  { key: "amount", label: "Amount", align: "right" },
  { key: "method", label: "Method", align: "left", filterKey: "payment_method" },
  { key: "status", label: "Status", align: "left", filterKey: "payment_status" },
  { key: "date", label: "Date", align: "left", type: "date", filterKey: "payment_date" },
  { key: "actions", label: "Actions", align: "right" },
];

const statusStyles = {
  completed: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  pending: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  failed: "bg-rose-500/10 text-rose-500 border-rose-500/20",
  refunded: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  cancelled: "bg-slate-500/10 text-slate-500 border-slate-500/20",
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const capitalize = (str) => {
  if (!str) return "";
  return str.charAt(0).toUpperCase() + str.slice(1);
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};
</script>
