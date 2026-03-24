<template>
  <DataTable
    :columns="columns"
    :items="orders"
    :loading="loading"
    empty-text="No records found."
  >


    <template #cell-order_number="{ item: order }">
      <div class="flex flex-col">
        <span class="font-semibold text-slate-900 italic hover:text-primary transition-colors cursor-pointer" @click="$emit('view', order)">
          {{ order.order_number }}
        </span>
        <span 
          v-if="order.client_information?.job_details?.expected_delivery_date" 
          class="text-[10px] mt-0.5"
          :class="getCountdownColor(order.client_information.job_details.expected_delivery_date)"
        >
          {{ getCountdownText(order.client_information.job_details.expected_delivery_date) }}
        </span>
      </div>
    </template>

    <template #cell-printing_days="{ item: order }">
      <span :class="cn('inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200', getPrintingDaysColor(order))">
        {{ getDaysFromAssigned(order) }}
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
import { computed } from "vue";
import { Eye, Pencil } from "lucide-vue-next";
import DataTable from "../ui/DataTable.vue";

const props = defineProps({
  orders: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  stage: { type: String, required: true }
});

defineEmits(["view", "edit"]);

const columns = computed(() => {
  const base = [
    { key: "sn", label: "S.No", width: "60px", align: "center", class: "whitespace-nowrap" },
    { key: "order_number", label: "Order ID", align: "left", width: "160px", class: "whitespace-nowrap", filterKey: "order_number" },
    { key: "customer", label: "Customer", align: "left", width: "200px", filterKey: "customer_details.name" },
    { key: "assigned_name", label: "Assigned Name", align: "left", width: "180px", filter: false },
    { key: "assigned_date", label: "Assigned Date", align: "left", width: "140px", class: "whitespace-nowrap", filter: false },
  ];
  if (props.stage === 'printing') {
    base.push({ key: "printing_days", label: "Printing Days", align: "left", width: "120px", class: "whitespace-nowrap", filter: false });
  }
  base.push(
    { key: "status", label: "Status", align: "left", width: "120px", class: "whitespace-nowrap", filter: false },
    { key: "created_at", label: "Created", align: "left", width: "150px", type: "date", filterKey: "created_at" },
    { key: "modified_by", label: "Modified", align: "left", width: "150px", filter: false },
    { key: "actions", label: "Action", align: "right", width: "110px", class: "whitespace-nowrap" }
  );
  return base;
});

const getCountdownColor = (dateString) => {
  if (!dateString) return "text-slate-500";
  const deliveryDate = new Date(dateString);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  deliveryDate.setHours(0, 0, 0, 0);
  
  const diffTime = deliveryDate - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays <= 0) return "text-rose-500 font-medium";
  return "text-emerald-500 font-medium";
};

const getPrintingDaysColor = (order) => {
  const dateString = order.client_information?.job_details?.expected_delivery_date;
  if (!dateString) return "bg-slate-500/10 text-slate-500 border-slate-500/20";
  
  const deliveryDate = new Date(dateString);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  deliveryDate.setHours(0, 0, 0, 0);
  
  const diffTime = deliveryDate - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays <= 0) return "bg-rose-500/10 text-rose-500 border-rose-500/20";
  return "bg-emerald-500/10 text-emerald-500 border-emerald-500/20";
};

const getCountdownText = (dateString) => {
  if (!dateString) return "";
  const deliveryDate = new Date(dateString);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  deliveryDate.setHours(0, 0, 0, 0);
  
  const diffTime = deliveryDate - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  const absDays = Math.abs(diffDays);
  const dayStr = absDays === 1 ? 'Day' : 'Days';
  
  if (diffDays < 0) {
    return `${absDays} ${dayStr} Late`;
  } else if (diffDays === 0) {
    return "Due Today";
  } else {
    return `${absDays} ${dayStr} Left`;
  }
};

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
  switch (props.stage) {
    case 'client-information':
      return order.client_information?.job_details?.order_taken_by || "N/A";
    case 'designing':
      return order.designing?.work_assign?.assigned_to || "N/A";
    case 'printing':
      return order.printing?.printing_status?.assigned_to || order.designing?.work_assign?.assigned_to || "N/A";
    case 'packaging':
      return order.packaging?.packaging_logistics?.crafted_by || "N/A";
    case 'delivery':
      return order.dispatch_delivery?.dispatch_mode?.signature_name || "N/A";
    default:
      return "N/A";
  }
};

const getAssignedDateRaw = (order) => {
  let date = null;
  switch (props.stage) {
    case 'client-information':
      date = order.order_date;
      break;
    case 'designing':
      date = order.designing?.work_assign?.assigned_date;
      break;
    case 'printing':
      date = order.printing?.printing_status?.assigned_date;
      break;
    case 'packaging':
      date = order.packaging?.packaging_logistics?.date;
      break;
    case 'delivery':
      date = order.dispatch_delivery?.dispatch_mode?.date;
      break;
  }
  return date;
};

const getAssignedDate = (order) => {
  return formatDate(getAssignedDateRaw(order));
};

const getDaysFromAssigned = (order) => {
  const dateStr = getAssignedDateRaw(order);
  if (!dateStr) return "N/A";
  
  const assigned = new Date(dateStr);
  const today = new Date();
  assigned.setHours(0,0,0,0);
  today.setHours(0,0,0,0);
  
  const diffTime = today - assigned;
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays < 0) return "Day 1";
  return `Day ${diffDays + 1}`;
};

const getStageStatus = (order) => {
  if (order.status === 'cancelled') return 'Cancelled';
  
  const stageData = getStageData(order);
  if (stageData && stageData.status) {
    return stageData.status;
  }
  
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
  return stageData?.modified_by?.name || stageData?.added_by?.name || "Admin";
};

const getModifiedAt = (order) => {
  const stageData = getStageData(order);
  return formatDate(stageData?.updated_at || stageData?.created_at);
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
