<template>
  <DataTable :columns="columns" :items="processedOrders" :loading="loading" empty-text="No records found.">


    <template #cell-order_number="{ item: order }">
      <div class="flex flex-col">
        <span class="font-semibold text-slate-900 italic hover:text-primary transition-colors cursor-pointer"
          @click="$emit('edit', order)">
          {{ order.order_number }}
        </span>
        <span v-if="order.delivery_date && order.status?.toLowerCase() !== 'delivered' && getStageStatus(order).toLowerCase() !== 'completed'" class="text-[10px] mt-0.5"
          :class="getCountdownColor(order.delivery_date)">
          {{ getCountdownText(order.delivery_date) }}
        </span>
        <span v-else class="text-[10px] mt-0.5 text-slate-400 font-medium">
          -
        </span>
      </div>
    </template>

    <template #cell-printing_days="{ item: order }">
      <span v-if="getStageStatus(order).toLowerCase() !== 'completed'"
        :class="cn('inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200', getPrintingDaysColor(order))">
        {{ getDaysFromAssigned(order) }}
      </span>
      <span v-else class="text-slate-400 font-medium ml-4">-</span>
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

    <template #cell-order_placed_in="{ item: order }">
      <span class="text-sm font-medium text-slate-700">
        {{ order.client_information?.order_details?.order_placed_in || "N/A" }}
      </span>
    </template>

    <template #cell-assigned_date="{ item: order }">
      <span class="text-slate-500 font-medium">{{ getAssignedDate(order) }}</span>
    </template>

    <template #cell-delivery_date="{ item: order }">
      <span class="text-slate-500 font-medium">{{
        formatDate(order.delivery_date) }}</span>
    </template>

    <template #cell-status="{ item: order }">
      <span :class="cn(
        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
        statusStyles[getStageStatus(order).toLowerCase()] || 'bg-slate-500/10 text-slate-500 border-slate-500/20'
      )">
        {{ getStageStatus(order) }}
      </span>
    </template>

    <template #cell-created_at="{ item: order }">
      <div class="flex flex-col">
        <span class="text-[10px] text-slate-600 font-extrabold uppercase tracking-tight">{{ formatDate(order.created_at) }}</span>
        <span class="text-[9px] text-slate-400 font-medium">by {{ order.added_by?.name || "Admin" }}</span>
      </div>
    </template>

    <template #cell-modified_at="{ item: order }">
      <div class="flex flex-col">
        <span class="text-[10px] text-slate-600 font-extrabold uppercase tracking-tight">{{ formatDate(order[relationKey]?.updated_at || order.updated_at) }}</span>
        <span class="text-[9px] text-slate-400 font-medium">by {{ order[relationKey]?.modified_by?.name || order.modified_by?.name || "N/A" }}</span>
      </div>
    </template>

    <template #cell-process_status="{ item: order }">
      <div v-if="order.computed_process_status !== 'N/A'" :class="cn(
        'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border transition-colors duration-200',
        order.computed_process_status === 'Content Received'
          ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'
          : 'bg-rose-500/10 text-rose-600 border-rose-500/20'
      )">
        <span class="w-1.5 h-1.5 rounded-full mr-1.5"
          :class="order.computed_process_status === 'Content Received' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
        {{ order.computed_process_status }}
      </div>
      <span v-else class="text-slate-400">N/A</span>
    </template>

    <template #cell-completed_by="{ item: order }">
      <span class="text-sm font-medium text-slate-700">{{ order.computed_completed_by }}</span>
    </template>

    <template #cell-start_time="{ item: order }">
      <span class="text-sm font-medium text-slate-700">{{ formatTimeTo12h(order.packaging?.packaging_logistics?.start_time) }}</span>
    </template>

    <template #cell-end_time="{ item: order }">
      <span class="text-sm font-medium text-slate-700">{{ formatTimeTo12h(order.packaging?.packaging_logistics?.end_time) }}</span>
    </template>

    <template #cell-actions="{ item: order }">
      <div class="flex justify-end gap-1.5 transition-opacity duration-200">
        <button @click="$emit('view', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
          title="View Info">
          <Eye class="h-4 w-4" />
        </button>
        <button @click="$emit('edit', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
          title="Edit Order">
          <Pencil class="h-4 w-4" />
        </button>
      </div>
    </template>
  </DataTable>
</template>

<script setup>
import { computed } from "vue";
import { Eye, Pencil } from "lucide-vue-next";
import DataTable from "../ui/data-table/DataTable.vue";

const props = defineProps({
  orders: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  stage: { type: String, required: true }
});

defineEmits(["view", "edit"]);

const relationKey = computed(() => {
  switch (props.stage) {
    case 'client-information': return 'client_information';
    case 'designing': return 'designing';
    case 'printing': return 'printing';
    case 'packaging': return 'packaging';
    case 'delivery': return 'dispatch_delivery';
    default: return props.stage.replace('-', '_');
  }
});

const processedOrders = computed(() => {
  let filtered = props.orders;

  // Filter based on stage record existence
  filtered = filtered.filter(order => !!getStageData(order));

  return filtered.map(order => {
    const assignedDate = getAssignedDateRaw(order);
    let printingDaysStatus = "N/A";
    if (assignedDate) {
      const assigned = new Date(assignedDate);
      const today = new Date();
      assigned.setHours(0, 0, 0, 0);
      today.setHours(0, 0, 0, 0);
      const diffTime = today - assigned;
      const daysSinceAssigned = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
      printingDaysStatus = daysSinceAssigned <= 7 ? "On Time" : "Delayed";
    }

    return {
      ...order,
      computed_stage_status: getStageStatus(order),
      computed_assigned_name: getAssignedName(order),
      computed_process_status: getProcessStatus(order),
      computed_completed_by: order.designing?.work_assign?.completed_by || "N/A",
      computed_printing_days_status: printingDaysStatus
    };
  });
});

const columns = computed(() => {
  const stageDataKey = relationKey.value;

  const cols = [
    { key: "sn", label: "S.No", width: "60px", align: "center", class: "whitespace-nowrap" },
    { key: "order_number", label: "Order ID", align: "left", width: "160px", class: "whitespace-nowrap", filterKey: "order_number" },
    { key: "customer", label: "Customer Name", align: "left", width: "200px", filterKey: "customer_details.name" },
    {
      key: "assigned_name",
      label: props.stage === 'client-information' ? "Order Taken By" : "Assigned Name",
      align: "left",
      width: "180px",
      filterKey: "computed_assigned_name"
    },
  ];

  if (props.stage === 'client-information') {
    cols.push({
      key: "order_placed_in",
      label: "Order Placed In",
      align: "left",
      width: "150px",
      class: "whitespace-nowrap",
      type: "select",
      placeholder: "All Places",
      filterKey: "client_information.order_details.order_placed_in",
      options: [
        { label: "MTM", value: "MTM" },
        { label: "TVL", value: "TVL" },
        { label: "Chennai", value: "Chennai" },
        { label: "Online", value: "Online" },
        { label: "NGL", value: "NGL" }
      ]
    });
  }

  cols.push({
    key: "assigned_date",
    label: props.stage === 'client-information' ? "Order Date" : "Assigned Date",
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "date",
    filterKey: props.stage === 'client-information' ? "order_date" : null
  });

  if (props.stage === 'packaging') {
    cols.push({
      key: "start_time",
      label: "Start Time",
      align: "left",
      width: "120px",
      class: "whitespace-nowrap"
    });
    cols.push({
      key: "end_time",
      label: "End Time",
      align: "left",
      width: "120px",
      class: "whitespace-nowrap"
    });
  }

  if (props.stage === 'designing') {
    cols.push({
      key: "process_status",
      label: "Process Status",
      align: "left",
      width: "180px",
      class: "whitespace-nowrap",
      type: "select",
      filterKey: "computed_process_status",
      options: [
        { label: "Content Received", value: "Content Received" },
        { label: "Content Not Received", value: "Content Not Received" }
      ]
    });

    cols.push({
      key: "completed_by",
      label: "Completed By",
      align: "left",
      width: "160px",
      class: "whitespace-nowrap",
      type: "text",
      filterKey: "computed_completed_by"
    });
  }

  if (props.stage === 'printing') {
    cols.push({
      key: "printing_days",
      label: "Printing Days",
      align: "left",
      width: "120px",
      class: "whitespace-nowrap",
      type: "select",
      placeholder: "All Days",
      filterKey: "computed_printing_days_status",
      options: [
        { label: "On Time", value: "On Time" },
        { label: "Delayed", value: "Delayed" }
      ]
    });
  }

  // Add Status
  cols.push({
    key: "status",
    label: "Status",
    align: "left",
    width: "120px",
    class: "whitespace-nowrap",
    type: "select",
    filterKey: "computed_stage_status",
    options: [
      { label: "Pending", value: "Pending" },
      { label: "Processing", value: "Process" },
      { label: "Completed", value: "Completed" },
      { label: "Cancelled", value: "Cancelled" }
    ]
  });

  // Add Delivery Date (Global)
  cols.push({
    key: "delivery_date",
    label: "Delivery Date",
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "date",
    filterKey: "delivery_date"
  });

  // Add Audit
  cols.push({
    key: "created_at",
    label: "Created",
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "date",
    filterKey: "created_at"
  });

  cols.push({
    key: "modified_at",
    label: "Modified",
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "date",
    filterKey: `${stageDataKey}.updated_at`
  });

  // Common final columns
  cols.push(
    { key: "actions", label: "Action", align: "right", width: "110px", class: "whitespace-nowrap" }
  );

  return cols;
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
  // Check age of assignment first
  const dateStr = getAssignedDateRaw(order);
  if (dateStr) {
      const assigned = new Date(dateStr);
      const today = new Date();
      assigned.setHours(0, 0, 0, 0);
      today.setHours(0, 0, 0, 0);
      const diffTime = today - assigned;
      const daysSinceAssigned = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
      
      // If within 7 days of assignment, show Green
      if (daysSinceAssigned <= 7) {
          return "bg-emerald-500/10 text-emerald-500 border-emerald-500/20";
      }
  }

  // Fallback to proximity to delivery if after 7 days
  const deliveryDateString = order.delivery_date;
  if (!deliveryDateString) return "bg-slate-500/10 text-slate-500 border-slate-500/20";

  const deliveryDate = new Date(deliveryDateString);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  deliveryDate.setHours(0, 0, 0, 0);

  const diffTime = deliveryDate - today;
  const deliveryDiffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (deliveryDiffDays <= 0) return "bg-rose-500/10 text-rose-500 border-rose-500/20";
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
  const d = new Date(date);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}-${month}-${year}`;
};

const formatTimeTo12h = (timeStr) => {
  if (!timeStr || typeof timeStr !== 'string') return "N/A";
  const [h, m] = timeStr.split(":");
  if (!h || !m) return timeStr;
  const hour = parseInt(h);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const hour12 = hour % 12 || 12;
  return `${String(hour12).padStart(2, '0')}:${m} ${ampm}`;
};

const getAssignedName = (order) => {
  switch (props.stage) {
    case 'client-information':
      return order.client_information?.order_details?.order_taken_by || "N/A";
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
  assigned.setHours(0, 0, 0, 0);
  today.setHours(0, 0, 0, 0);

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

const getProcessStatus = (order) => {
  if (props.stage !== 'designing' || !order.designing?.work_assign) return "N/A";
  const wa = order.designing.work_assign;
  if (wa.content_received) return "Content Received";
  if (wa.content_not_received) return "Content Not Received";
  return "N/A";
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
