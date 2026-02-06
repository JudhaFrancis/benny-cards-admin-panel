<template>
  <DataTable
    :columns="columns"
    :items="orders"
    :loading="loading"
    empty-text="No orders found matching your criteria."
  >
    <!-- Custom Row Cells -->
    <template #cell-order_number="{ item: order }">
      <span class="font-semibold text-slate-900 italic">{{
        order.order_number
      }}</span>
    </template>

    <template #cell-customer="{ item: order }">
      <div class="flex flex-col">
        <span class="text-sm font-medium text-slate-900">{{
          order.customer_details?.name || "N/A"
        }}</span>
      </div>
    </template>

    <template #cell-orderDate="{ item: order }">
      <span class="text-slate-500">{{
        new Date(order.order_date).toLocaleDateString()
      }}</span>
    </template>

    <template #cell-items="{ item: order }">
      <span class="font-medium text-slate-900">{{ order.items_count }}</span>
    </template>

    <template #cell-status="{ item: order }">
      <span
        :class="
          cn(
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
            (order.status && orderStatusStyles[order.status.toLowerCase()]) ||
              'bg-slate-100 text-slate-800 border-slate-200',
          )
        "
      >
        {{ capitalize(order.status) }}
      </span>
    </template>

    <template #cell-amount="{ item: order }">
      <span class="font-bold text-slate-900"
        >${{ Number(order.total_amount).toFixed(2) }}</span
      >
    </template>

    <template #cell-paid="{ item: order }">
      <span class="font-medium text-slate-600"
        >${{ Number(order.paid_amount).toFixed(2) }}</span
      >
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

    <template #cell-actions="{ item: order }">
      <div class="flex justify-end gap-1.5 transition-opacity duration-200">
        <button
          v-if="canView"
          @click="$emit('view-info', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
          title="View Info"
        >
          <Eye class="h-4 w-4" />
        </button>
        <button
          v-if="canEdit"
          @click="$emit('edit', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
          title="Edit Order"
        >
          <Pencil class="h-4 w-4" />
        </button>
        <button
          v-if="canDelete"
          @click="$emit('delete', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
          title="Delete Order"
        >
          <Trash2 class="h-4 w-4" />
        </button>
      </div>
    </template>
  </DataTable>
</template>

<script setup>
import { Eye, Pencil, Trash2 } from "lucide-vue-next";
import { usePermissions } from "../../composables/usePermissions";
import DataTable from "../ui/DataTable.vue";

const props = defineProps({
  orders: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["view-info", "edit", "delete"]);

const { getModulePermissions } = usePermissions();
const { canView, canEdit, canDelete } = getModulePermissions("Order");

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "order_number", label: "Order ID", align: "left" },
  { key: "customer", label: "Customer", align: "left" },
  { key: "orderDate", label: "Order Date", align: "left" },
  { key: "items", label: "Items", align: "center" },
  { key: "status", label: "Order Status", align: "left" },
  { key: "amount", label: "Amount", align: "right" },
  { key: "paid", label: "Paid", align: "right" },
  { key: "payment", label: "Payment", align: "left" },
  { key: "actions", label: "Actions", align: "right" },
];

const orderStatusStyles = {
  pending: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  processing: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  shipped: "bg-primary/10 text-primary border-primary/20",
  delivered: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  cancelled: "bg-rose-500/10 text-rose-500 border-rose-500/20",
};

const paymentStatusStyles = {
  paid: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  due: "bg-amber-500/10 text-amber-500 border-amber-500/20",
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

<style scoped></style>
