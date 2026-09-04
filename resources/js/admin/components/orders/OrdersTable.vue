<template>
  <DataTable :columns="columns" :items="processedOrders" :from="from" manual-filters :loading="loading" @filter-change="$emit('filter-change', $event)" @row-click="$emit('view-info', $event)" empty-text="No orders found .">
    <!-- Custom Row Cells -->
    <template #cell-order_number="{ item: order }">
      <div class="flex flex-col">
        <span
          class="font-semibold text-slate-900 italic cursor-pointer hover:text-primary transition-colors duration-200"
          @click.stop="$emit('edit', order)"
        >
          {{ order.order_number?.split('-')[0] }}
        </span>
        <div class="flex items-center gap-2 mt-0.5">
          <span v-if="order.delivery_date && order.resolved_status?.toLowerCase() !== 'delivered'" class="text-[10px]"
            :class="getCountdownColor(order.delivery_date)">
            {{ getCountdownText(order.delivery_date) }}
          </span>
          <span v-else-if="order.resolved_status?.toLowerCase() === 'delivered'" class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
            Delivered
          </span>
          <span v-if="order.priority" :class="['text-[10px] px-1.5 py-0.5 rounded-full font-bold border', getPriorityClass(order.priority)]">
            {{ getPriorityLabel(order.priority) }}
          </span>
        </div>
      </div>
    </template>

    <template #cell-customer="{ item: order }">
      <OrderCustomerCell :order="order" />
    </template>

    <template #cell-dates="{ item: order }">
      <OrderDatesCell :order="order" />
    </template>

    <template #cell-orderSrc="{ item: order }">
      <OrderSourceCell :order="order" />
    </template>

    <template #cell-status="{ item: order }">
      <OrderStatusCell :order="order" :orderStatusStyles="orderStatusStyles" />
    </template>

    <template #cell-payment="{ item: order }">
      <span :class="cn(
        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors duration-200',
        (order.payment_status &&
          paymentStatusStyles[order.payment_status.toLowerCase()]) ||
        'bg-slate-100 text-slate-800 border-slate-200',
      )
        ">
        {{ capitalize(order.payment_status) }}
      </span>
    </template>

    <template #cell-created_at="{ item: order }">
      <OrderMetaCell :date="order.created_at" :by="order.added_by?.name || 'Admin'" />
    </template>

    <template #cell-modified_by="{ item: order }">
      <OrderMetaCell :date="order.computed_modified_at" :by="order.computed_modified_by" />
    </template>

    <template #cell-actions="{ item: order }">
      <div class="flex justify-end gap-1.5 transition-opacity duration-200">
        <!-- Main Actions (Always show view/edit, show others if in main 'Orders' view) -->
        <!-- <button v-if="canEdit && statusFilter === 'all'" @click.stop="sendWhatsApp(order)"
          :disabled="sendingWhatsapp === order.id"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 transition-all duration-200 disabled:opacity-50"
          title="Send WhatsApp">
          <Loader2 v-if="sendingWhatsapp === order.id" class="h-4 w-4 animate-spin" />
          <MessageCircle v-else class="h-4 w-4" />
        </button> -->

        <button v-if="canView" @click.stop="$emit('view-info', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
          title="View Info">
          <Eye class="h-4 w-4" />
        </button>

        <button v-if="canEdit" @click.stop="$emit('edit', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 transition-all duration-200"
          title="Edit Order">
          <Pencil class="h-4 w-4" />
        </button>

        <button v-if="canDelete && statusFilter === 'all'" @click.stop="$emit('delete', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
          title="Delete Order">
          <Trash2 class="h-4 w-4" />
        </button>
      </div>
    </template>
  </DataTable>
</template>

<script setup>
import { ref, computed } from "vue";
import { Eye, Pencil, Trash2, MessageCircle, Loader2 } from "lucide-vue-next";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";
import axios from "axios";
import DataTable from "../ui/data-table/DataTable.vue";
import { getPriorityLabel, getPriorityClass } from "../../constants/orderPriorities";

// New cell components
import OrderCustomerCell from "./cells/OrderCustomerCell.vue";
import OrderDatesCell from "./cells/OrderDatesCell.vue";
import OrderSourceCell from "./cells/OrderSourceCell.vue";
import OrderStatusCell from "./cells/OrderStatusCell.vue";
import OrderMetaCell from "./cells/OrderMetaCell.vue";

const props = defineProps({
  orders: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  statusFilter: {
    type: String,
    default: "all",
  },
  from: {
    type: Number,
    default: 1,
  },
});

const getStageData = (order) => {
  // Logic to find the most recently updated stage
  const stages = [
    { key: 'dispatch_delivery', data: order.dispatch_delivery },
    { key: 'packaging', data: order.packaging },
    { key: 'printing', data: order.printing },
    { key: 'designing', data: order.designing },
    { key: 'client_information', data: order.client_information }
  ];
  
  return stages
    .filter(s => !!s.data && (s.data.updated_at || s.data.created_at))
    .sort((a, b) => {
      const dateA = new Date(a.data.updated_at || a.data.created_at);
      const dateB = new Date(b.data.updated_at || b.data.created_at);
      return dateB - dateA;
    })[0]?.data || null;
};

const getAssignedName = (order) => {
  switch (props.statusFilter) {
    case 'client_info':
      return order.client_information?.order_details?.order_taken_by || "Not Assigned";
    case 'designing':
      return order.designing?.work_assign?.assigned_to || "Not Assigned";
    case 'printing':
      return order.printing?.printing_status?.assigned_to || order.designing?.work_assign?.assigned_to || "Not Assigned";
    case 'packaging':
      const log = order.packaging?.packaging_logistics;
      let names = [];
      if (Array.isArray(log?.assigned_by_multiple)) names.push(...log.assigned_by_multiple);
      if (Array.isArray(log?.crafted_by_multiple)) names.push(...log.crafted_by_multiple);
      if (log?.crafted_by && !names.includes(log.crafted_by)) names.push(log.crafted_by);
      return names.length > 0 ? [...new Set(names)].join(", ") : "Not Assigned";
    case 'delivered':
      return order.dispatch_delivery?.dispatch_mode?.packed_by || "Not Assigned";
    default:
      return order.designing?.work_assign?.assigned_to || "Not Assigned";
  }
};

const getAssignedDate = (order) => {
  switch (props.statusFilter) {
    case 'client_info':
      return order.order_date;
    case 'designing':
      return order.designing?.work_assign?.assigned_date;
    case 'printing':
      return order.printing?.printing_status?.confirmed_date || order.printing?.printing_status?.assigned_date;
    case 'packaging':
      return order.packaging?.packaging_logistics?.date;
    case 'delivered':
      return order.dispatch_delivery?.dispatch_mode?.date;
    default:
      return order.designing?.work_assign?.assigned_date;
  }
};

const processedOrders = computed(() => {
  return props.orders.map(order => {
    const latestStage = getStageData(order);
    return {
      ...order,
      computed_modified_at: latestStage?.updated_at || latestStage?.created_at || order.updated_at || order.created_at,
      computed_modified_by: latestStage?.modified_by?.name || latestStage?.added_by?.name || order.added_by?.name || "Admin"
    };
  });
});

const emit = defineEmits(["view-info", "edit", "delete", "update-status"]);

const handleStatusChange = (order, newStatus) => {
  order.status = newStatus; // Optimistic update
  emit('update-status', { orderId: order.id, status: newStatus });
};

const { getModulePermissions } = usePermissions();
const { canView, canEdit, canDelete } = getModulePermissions("Order");
const toast = useToast();

const sendingWhatsapp = ref(null);

const sendWhatsApp = async (order) => {
  if (!order.customer_details?.phone) {
    toast.error("Customer phone number not found");
    return;
  }

  sendingWhatsapp.value = order.id;
  try {
    const response = await axios.post("/api/v1/whatsapp/send", {
      phone: order.customer_details.phone,
      event: "NEW_ORDER",
      data: {
        customerName: order.customer_details.name,
        order_id: order.order_number,
      },
    });

    if (response.data.success) {
      toast.success("WhatsApp message sent successfully");
    }
  } catch (error) {
    console.error("WhatsApp Error:", error);
    toast.error(error.response?.data?.message || "Failed to send WhatsApp message");
  } finally {
    sendingWhatsapp.value = null;
  }
};

const columns = computed(() => {
  if (props.statusFilter === "all") {
    return [
      { key: "sn", label: "S.No", width: "60px", align: "center", class: "whitespace-nowrap" },
      { key: "order_number", label: "Order ID", align: "left", width: "160px", class: "whitespace-nowrap" },
      { key: "customer", label: "Customer", align: "left", width: "200px", filterKey: "customer_details.name", tooltip: "Customer Name\nProduct Name\nTotal Quantity" },
      { key: "dates", label: "Dates", align: "left", width: "150px", class: "whitespace-nowrap", type: "date", filterKey: "order_date", tooltip: "Order Date (OD)\nDesign Finalized (DF)\nDelivery Date (DD)" },
      { key: "orderSrc", label: "Order Src", align: "left", width: "150px", class: "whitespace-nowrap", filterKey: "client_information.order_details.order_taken_by", tooltip: "Order Taken By\nOrder Placed In" },
      { key: "status", label: "Status", align: "left", width: "150px", class: "whitespace-nowrap", filterKey: "resolved_status", type: "select", options: Object.keys(orderStatusStyles).map(s => ({ label: s.charAt(0).toUpperCase() + s.slice(1), value: s })), tooltip: "Current Status\nAssigned Staff Name" },
      { key: "payment", label: "Payment Status", align: "left", width: "140px", class: "whitespace-nowrap", filterKey: "payment_status", type: "select", options: [ { label: "Paid", value: "paid" }, { label: "Unpaid", value: "unpaid" }, { label: "Partial", value: "partial" } ] },
      { key: "created_at", label: "Created", align: "left", width: "130px", type: "date", filterKey: "created_at", tooltip: "Created Date\nCreated By" },
      { key: "modified_by", label: "Modified", align: "left", width: "130px", type: "date", filterKey: "computed_modified_at", tooltip: "Modified Date\nModified By" },
      { key: "actions", label: "Actions", align: "right", width: "130px", class: "whitespace-nowrap" },
    ];
  }

  // Workflow Columns for Tracking Tabs
  
  let assignedNameKey = "designing.work_assign.assigned_to";
  let assignedDateKey = "designing.work_assign.assigned_date";
  
  if (props.statusFilter === 'client_info') {
    assignedNameKey = "client_information.order_details.order_taken_by";
    assignedDateKey = "order_date";
  } else if (props.statusFilter === 'printing') {
    assignedNameKey = "printing.printing_status.assigned_to";
    assignedDateKey = "printing.printing_status.assigned_date";
  } else if (props.statusFilter === 'packaging') {
    assignedNameKey = "packaging.packaging_logistics.crafted_by";
    assignedDateKey = "packaging.packaging_logistics.date";
  } else if (props.statusFilter === 'delivered') {
    assignedNameKey = "dispatch_delivery.dispatch_mode.packed_by";
    assignedDateKey = "dispatch_delivery.dispatch_mode.date";
  }

  return [
    { key: "sn", label: "S.No", width: "60px", align: "center", class: "whitespace-nowrap" },
    { key: "order_number", label: "Order ID", align: "left", width: "160px", class: "whitespace-nowrap" },
    { key: "assigned_name", label: "Assigned Name", align: "left", width: "200px", filterKey: assignedNameKey },
    { key: "assigned_date", label: "Assigned Date", align: "left", width: "140px", class: "whitespace-nowrap", type: "date", filterKey: assignedDateKey },
    { key: "status", label: "Status", align: "left", width: "120px", class: "whitespace-nowrap" },
    { key: "created_at", label: "Created At", align: "left", width: "150px", type: "date" },
    { key: "modified_by", label: "Modified By", align: "left", width: "150px" },
    { key: "actions", label: "Action", align: "right", width: "110px", class: "whitespace-nowrap" },
  ];
});

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

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

const orderStatusStyles = {
  "new order": "bg-slate-500/10 text-slate-500 border-slate-500/20",
  confirmed: "bg-blue-500/10 text-blue-500 border-blue-500/20",
  "designing in progress": "bg-indigo-500/10 text-indigo-500 border-indigo-500/20",
  designed: "bg-indigo-500/20 text-indigo-600 border-indigo-500/30",
  "printing in progress": "bg-sky-500/10 text-sky-500 border-sky-500/20",
  printed: "bg-sky-500/20 text-sky-600 border-sky-500/30",
  "packing in progress": "bg-cyan-500/10 text-cyan-500 border-cyan-500/20",
  packed: "bg-cyan-500/20 text-cyan-600 border-cyan-500/30",
  "out for delivery": "bg-orange-500/10 text-orange-500 border-orange-500/20",
  delivered: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
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

<style scoped></style>
