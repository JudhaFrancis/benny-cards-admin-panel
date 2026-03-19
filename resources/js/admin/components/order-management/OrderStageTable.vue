<template>
  <DataTable
    :columns="columns"
    :items="orders"
    :loading="loading"
    empty-text="No records found."
  >


    <template #cell-order_number="{ item: order }">
      <span class="font-semibold text-slate-900 italic hover:text-primary transition-colors cursor-pointer" @click="$emit('view', order)">
        {{ order.order_number }}
      </span>
    </template>

    <template #cell-customer="{ item: order }">
      <div class="flex flex-col">
        <span class="text-sm font-medium text-slate-900">{{
          order.customer_details?.name || "N/A"
        }}</span>
      </div>
    </template>

    <template #cell-assigned_name="{ item: order }">
      <div class="flex flex-col">
        <span class="text-sm font-semibold text-slate-900">{{ getAssignedName(order) }}</span>
      </div>
    </template>

    <template #cell-assigned_date="{ item: order }">
      <span class="text-slate-500 font-medium">{{ getAssignedDate(order) }}</span>
    </template>

    <template #cell-status="{ item: order }">
      <span
        :class="cn(
          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
          statusStyles[getStageStatus(order).toLowerCase()] || 'bg-slate-500/10 text-slate-500 border-slate-500/20'
        )"
      >
        {{ getStageStatus(order) }}
      </span>
    </template>

    <template #cell-created_at="{ item: order }">
      <div class="flex flex-col">
        <span class="text-xs text-slate-600">{{ formatDate(order.created_at) }}</span>
        <span class="text-[10px] text-slate-400">by {{ order.added_by?.name || "Admin" }}</span>
      </div>
    </template>

    <template #cell-modified_by="{ item: order }">
      <div class="flex flex-col">
        <span class="text-xs text-slate-600">{{ getModifiedAt(order) }}</span>
        <span class="text-[10px] text-slate-400">by {{ getModifiedBy(order) }}</span>
      </div>
    </template>

    <template #cell-actions="{ item: order }">
      <div class="flex justify-end gap-1.5 transition-opacity duration-200">
        <button
          @click="$emit('view', order)"
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
      </div>
    </template>
  </DataTable>
</template>

<script setup>
import { Eye, Pencil } from "lucide-vue-next";
import DataTable from "../ui/DataTable.vue";

const props = defineProps({
  orders: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  stage: { type: String, required: true }
});

defineEmits(["view", "edit"]);

const columns = [
  { key: "sn", label: "S.No", width: "60px", align: "center", class: "whitespace-nowrap" },
  { key: "order_number", label: "Order ID", align: "left", width: "160px", class: "whitespace-nowrap" },
  { key: "customer", label: "Customer", align: "left", width: "200px" },
  { key: "assigned_name", label: "Assigned Name", align: "left", width: "180px" },
  { key: "assigned_date", label: "Assigned Date", align: "left", width: "140px", class: "whitespace-nowrap" },
  { key: "status", label: "Status", align: "left", width: "120px", class: "whitespace-nowrap" },
  { key: "created_at", label: "Created", align: "left", width: "150px" },
  { key: "modified_by", label: "Modified", align: "left", width: "150px" },
  { key: "actions", label: "Action", align: "right", width: "110px", class: "whitespace-nowrap" }
];

const statusStyles = {
  pending: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  process: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  completed: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  cancelled: "bg-rose-500/10 text-rose-500 border-rose-500/20"
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric"
  });
};

const getAssignedName = (order) => {
  const tracking = order.tracking || {};
  switch (props.stage) {
    case 'client-information':
      return tracking.job_details?.order_taken_by || "N/A";
    case 'designing':
      return tracking.work_assign?.assigned_to || "N/A";
    case 'printing':
      return tracking.printing_status?.assigned_to || tracking.work_assign?.assigned_to || "N/A"; // Printing often shares same assignee or isn't specifically named
    case 'packaging':
      return tracking.packaging_logistics?.crafted_by || "N/A";
    case 'delivery':
      return tracking.dispatch_mode?.signature_name || "N/A";
    default:
      return "N/A";
  }
};

const getAssignedDate = (order) => {
  const tracking = order.tracking || {};
  let date = null;
  switch (props.stage) {
    case 'client-information':
      date = order.order_date;
      break;
    case 'designing':
      date = tracking.work_assign?.assigned_date;
      break;
    case 'printing':
      date = tracking.printing_status?.assigned_date;
      break;
    case 'packaging':
      date = tracking.packaging_logistics?.date;
      break;
    case 'delivery':
      date = tracking.dispatch_mode?.date;
      break;
  }
  return formatDate(date);
};

const getStageStatus = (order) => {
  if (order.status === 'cancelled') return 'Cancelled';
  
  const stageData = getStageData(order);
  if (stageData && stageData.status) {
    return stageData.status;
  }
  
  // Fallback to heuristic if DB status is not set
  const tracking = order.tracking || {};
  let isStarted = false;
  let isDone = false;

  switch (props.stage) {
    case 'client-information':
      isStarted = !!tracking.job_details;
      isDone = !!tracking.card_specs && Object.keys(tracking.card_specs).length > 0;
      break;
    case 'designing':
      isStarted = !!tracking.work_assign;
      isDone = !!tracking.design_print && Object.keys(tracking.design_print).length > 0;
      break;
    case 'printing':
      isStarted = !!tracking.printing_status;
      isDone = tracking.printing_status?.readymade_sent_to_print || tracking.printing_status?.customize_sent_to_print_date;
      break;
    case 'packaging':
      isStarted = !!tracking.packaging_logistics;
      isDone = !!tracking.packaging_status && Object.keys(tracking.packaging_status).length > 0;
      break;
    case 'delivery':
      isStarted = !!tracking.delivery_location;
      isDone = !!tracking.dispatch_details && Object.keys(tracking.dispatch_details).length > 0;
      break;
  }

  if (isDone) return 'Completed';
  if (isStarted) return 'Process';
  return 'Pending';
};

const getStageData = (order) => {
  switch (props.stage) {
    case 'client-information': return order.client_information;
    case 'designing': return order.designing;
    case 'printing': return order.printing;
    case 'packaging': return order.packaging;
    case 'delivery': return order.dispatch_delivery;
    default: return null;
  }
};

const getModifiedBy = (order) => {
  const stageData = getStageData(order);
  if (stageData?.audit_details?.updated_by) {
    return stageData.audit_details.updated_by;
  }
  
  // Fallback to tracking audit
  const tracking = order.tracking || {};
  let audit = null;
  switch (props.stage) {
    case 'client-information': audit = tracking.client_info?._audit; break;
    case 'designing': audit = tracking.design_print?._audit; break;
    case 'printing': audit = tracking.printing_status?._audit; break;
    case 'packaging': audit = tracking.packaging_status?._audit; break;
    case 'delivery': audit = tracking.dispatch_details?._audit; break;
  }
  return audit?.updated_by || "Admin";
};

const getModifiedAt = (order) => {
  const stageData = getStageData(order);
  if (stageData?.audit_details?.updated_at) {
    return formatDate(stageData.audit_details.updated_at);
  }

  const tracking = order.tracking || {};
  let audit = null;
  switch (props.stage) {
    case 'client-information': audit = tracking.client_info?._audit; break;
    case 'designing': audit = tracking.design_print?._audit; break;
    case 'printing': audit = tracking.printing_status?._audit; break;
    case 'packaging': audit = tracking.packaging_status?._audit; break;
    case 'delivery': audit = tracking.dispatch_details?._audit; break;
  }
  return formatDate(audit?.updated_at);
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
