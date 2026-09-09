<template>
  <DataTable :columns="columns" :items="processedOrders" :loading="loading" :from="from" manual-filters
    @filter-change="$emit('filter-change', $event)" @row-click="$emit('view', $event)" empty-text="No records found.">


    <template #cell-order_number="{ item: order }">
      <div class="flex flex-col">
        <span class="font-semibold text-slate-900 italic hover:text-primary transition-colors cursor-pointer"
          @click.stop="$emit('edit', order)">
          {{ order.order_number?.replace(/-(?:[A-Z]{3})\d{2}/, (m) => m.slice(0, 4)) }}
        </span>
        <div class="flex items-center gap-2 mt-0.5">
          <span v-if="order.delivery_date && order.status?.toLowerCase() !== 'delivered' && getStageStatus(order).toLowerCase() !== 'completed'" class="text-[10px]"
            :class="getCountdownColor(order.delivery_date)">
            {{ getCountdownText(order.delivery_date) }}
          </span>
          <span v-else class="text-[10px] text-slate-400 font-medium">
            -
          </span>
          <span v-if="order.priority" :class="['text-[10px] px-1.5 py-0.5 rounded-full font-bold border', getPriorityClass(order.priority)]">
            {{ getPriorityLabel(order.priority) }}
          </span>
        </div>
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
      <OrderCustomerCell :order="order" />
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

    <template #cell-completed_date_col="{ item: order }">
      <span class="text-slate-500 font-medium">{{ order.computed_completed_date && order.computed_completed_date !== 'N/A' ? formatDate(order.computed_completed_date) : 'N/A' }}</span>
    </template>

    <template #cell-assign_timings_col="{ item: order }">
      <span v-if="order.computed_assign_timings" 
        class="text-slate-600 text-xs font-bold flex items-center justify-center gap-1.5"
      >
        <Clock class="h-3.5 w-3.5 text-slate-400" />
        {{ order.computed_assign_timings }}
      </span>
      <span v-else class="text-slate-400 font-medium">N/A</span>
    </template>

    <template #cell-design_outputs_col="{ item: order }">
      <div class="flex items-center gap-1 overflow-x-hidden">
        <template v-for="output in [
          { key: 'In Draft', label: 'Draft' },
          { key: 'Gift', label: 'Gift' },
          { key: 'PDF', label: 'PDF' }
        ]" :key="output.key">
          <span 
            :class="[
              'px-1.5 py-0.5 rounded-md text-[9px] font-medium border transition-all whitespace-nowrap flex items-center gap-0.5',
              order.computed_design_outputs.includes(output.key)
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                : 'bg-rose-50 text-rose-700 border-rose-200'
            ]"
          >
            <component 
              :is="order.computed_design_outputs.includes(output.key) ? Check : X" 
              class="h-2.5 w-2.5" 
            />
            {{ output.label }}
          </span>
        </template>
      </div>
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

    <template #cell-sent_to_print_date_col="{ item: order }">
      <span class="text-slate-500 font-medium">{{ getSentToPrintDate(order) }}</span>
    </template>

    <template #cell-printer_name="{ item: order }">
      <div v-if="order.printing?.printing_status?.company_names?.length" class="flex items-center">
        <AppTooltip :content="order.printing.printing_status.company_names.join(', ')">
          <template #trigger>
            <span class="text-sm font-medium text-slate-700 cursor-help border-b border-dashed border-slate-300">
              {{ order.printing.printing_status.company_names.join(', ').length > 20 
                 ? order.printing.printing_status.company_names.join(', ').substring(0, 20) + '...' 
                 : order.printing.printing_status.company_names.join(', ') 
              }}
            </span>
          </template>
        </AppTooltip>
      </div>
      <span v-else class="text-sm font-medium text-slate-400">N/A</span>
    </template>

    <template #cell-start_time="{ item: order }">
      <span class="text-sm font-medium text-slate-700">{{ formatTimeTo12h(order.packaging?.packaging_logistics?.start_time) }}</span>
    </template>

    <template #cell-end_time="{ item: order }">
      <span class="text-sm font-medium text-slate-700">{{ formatTimeTo12h(order.packaging?.packaging_logistics?.end_time) }}</span>
    </template>



    <template #cell-card_type="{ item: order }">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
        {{ (order.client_information?.card_specs?.type || "N/A").replace(/_card| card/gi, '').replace('_', ' ') }}
      </span>
    </template>

    <template #cell-card_options="{ item: order }">
      <div v-if="order.client_information?.card_specs?.card_options" class="flex flex-wrap gap-1">
        <span v-for="option in (order.client_information.card_specs.card_options.split(','))" :key="option"
          class="px-1.5 py-0.5 rounded bg-primary/5 text-primary text-[9px] font-bold border border-primary/10 whitespace-nowrap">
          {{ option.trim() }}
        </span>
      </div>
      <span v-else class="text-slate-400 font-medium">N/A</span>
    </template>

    <template #cell-packed_qty="{ item: order }">
      <span class="font-medium text-slate-900">
        {{ order.packaging?.packaging_logistics?.qty_cards || 'N/A' }}
      </span>
    </template>
    
    <template #cell-gift_type="{ item: order }">
      <span v-if="order.dispatch_delivery?.dispatch_mode?.gift_type" 
        class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 capitalize">
        {{ order.dispatch_delivery.dispatch_mode.gift_type.replace('_', ' ') }}
      </span>
      <span v-else class="text-slate-400 font-medium">N/A</span>
    </template>

    <template #cell-delivery_address="{ item: order }">
      <span class="text-xs text-slate-600 line-clamp-2">
        {{ order.dispatch_delivery?.delivery_location?.place_name || order.client_information?.client_info?.address || "N/A" }}
      </span>
    </template>

    <template #cell-modes="{ item: order }">
      <span class="text-sm font-medium text-slate-700">
        {{ order.dispatch_delivery?.dispatch_mode?.modes || "N/A" }}
      </span>
    </template>
    
    <template #cell-actions="{ item: order }">
      <div class="flex justify-end gap-1.5 transition-opacity duration-200">
        <button @click.stop="$emit('view', order)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
          title="View Info">
          <Eye class="h-4 w-4" />
        </button>
        <button @click.stop="$emit('edit', order)"
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
import { Eye, Pencil, Check, X, Clock } from "lucide-vue-next";
import DataTable from "../ui/data-table/DataTable.vue";
import AppTooltip from "../ui/display/AppTooltip.vue";
import OrderCustomerCell from "../orders/cells/OrderCustomerCell.vue";
import { useSettings } from "../../composables/useSettings";
import { getPriorityLabel, getPriorityClass } from "../../constants/orderPriorities";

const { settings, fetchSettings } = useSettings();
fetchSettings();

const activeBranches = computed(() => {
    if (!settings.value.branches) {
        return [
            { label: "MTM", value: "MTM" },
            { label: "TVL", value: "TVL" },
            { label: "Chennai", value: "Chennai" },
            { label: "Online", value: "Online" },
            { label: "NGL", value: "NGL" },
            { label: "Not Assigned", value: "n/a" }
        ];
    }
    const options = settings.value.branches.filter(b => b.active).map(b => ({
        label: b.name,
        value: b.name
    }));
    options.push({ label: "Not Assigned", value: "n/a" });
    return options;
});

const props = defineProps({
  orders: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  stage: { type: String, required: true },
  from: { type: Number, default: 1 }
});

const emit = defineEmits(["view", "edit", "filter-change"]);

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
  // The backend already filters for orders that have this stage record

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
      computed_completed_date: order.designing?.work_assign?.completed_date || "N/A",
      computed_assign_timings: order.designing?.work_assign?.assign_timings || null,
      computed_design_outputs: (order.designing?.design_print?.design_outputs || "").split(",").filter(Boolean).map(val => {
        if (val === "Invitation in Draft") return "In Draft";
        if (val === "Gift Frame") return "Gift";
        if (val === "Buttersheet / Master") return "Master";
        return val;
      }),
      computed_printing_days_status: printingDaysStatus
    };
  });

  // Sort by assign timings if it's the designing stage
  if (props.stage === 'designing') {
    const timeValue = (t) => {
      const map = { '10am': 10, '12pm': 12, '2pm': 14, '4pm': 16, '6pm': 18 };
      return map[t] || 999;
    };
    return processed.sort((a, b) => {
      return timeValue(a.computed_assign_timings) - timeValue(b.computed_assign_timings);
    });
  }

  return processed;
});

const columns = computed(() => {
  const stageDataKey = relationKey.value;

  const cols = [
    { key: "sn", label: "S.No", width: "60px", align: "center", class: "whitespace-nowrap" },
    { key: "order_number", label: "Order ID", align: "left", width: "160px", class: "whitespace-nowrap", filterKey: "order_number" },
    { key: "customer", label: "Customer", align: "left", width: "200px", filterKey: "customer_details.name", tooltip: "Customer Name\nProduct Name\nTotal Quantity" },
  ];

  if (props.stage !== 'printing') {
    cols.push({
      key: "assigned_name",
      label: props.stage === 'client-information' ? "Order Taken By" : "Assigned Name",
      align: "left",
      width: "180px",
      filterKey: "computed_assigned_name"
    });
  }

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
      options: activeBranches.value
    });

    cols.push({
      key: "card_type",
      label: "Card Type",
      align: "left",
      width: "140px",
      class: "whitespace-nowrap",
      type: "select",
      placeholder: "All Types",
      filterKey: "client_information.card_specs.type",
      options: [
        { label: "Customize", value: "customize" },
        { label: "Semi – Customize", value: "semi_customize" },
        { label: "Ready Made", value: "ready_made" },
        { label: "Digital Local", value: "digital_local" },
        { label: "Not Assigned", value: "n/a" }
      ]
    });
    cols.push({
      key: "card_options",
      label: "Add On",
      align: "left",
      width: "180px",
      type: "text",
      filterKey: "client_information.card_specs.card_options"
    });
  }

  const dateLabel = props.stage === 'client-information' ? "Order Date" : 
                    (props.stage === 'printing' ? "Confirmed Date" : "Assigned Date");

  cols.push({
    key: "assigned_date",
    label: dateLabel,
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "date",
    filterKey: props.stage === 'client-information' ? "order_date" : null
  });

  if (props.stage === 'designing') {
    cols.push({
      key: "completed_date_col",
      label: "Completed Date",
      align: "left",
      width: "140px",
      class: "whitespace-nowrap",
      type: "date",
      filterKey: "designing.work_assign.completed_date"
    });
    cols.push({
      key: "assign_timings_col",
      label: "Assign Timings",
      align: "center",
      width: "120px",
      class: "whitespace-nowrap",
      type: "text",
      filterKey: "designing_work_assign_assign_timings"
    });
  }

  if (props.stage === 'printing') {
    cols.push({
      key: "sent_to_print_date_col",
      label: "Sent to Print Date",
      align: "left",
      width: "140px",
      class: "whitespace-nowrap",
      type: "date",
      filterKey: "computed_sent_to_print_date"
    });
    cols.push({
      key: "printer_name",
      label: "Printer Name",
      align: "left",
      width: "160px",
      class: "whitespace-nowrap",
      type: "text",
      filterKey: "printing_printing_status_company_name"
    });
  }

  if (props.stage === 'packaging') {
    // Start Time and End Time removed by user request
    cols.push({
      key: "card_type",
      label: "Card Type",
      align: "left",
      width: "140px",
      class: "whitespace-nowrap",
      type: "select",
      placeholder: "All Types",
      filterKey: "client_information_card_specs_type",
      options: [
        { label: "Customize", value: "customize" },
        { label: "Semi – Customize", value: "semi_customize" },
        { label: "Ready Made", value: "ready_made" },
        { label: "Digital Local", value: "digital_local" },
        { label: "Not Assigned", value: "n/a" }
      ]
    });

    cols.push({
      key: "card_options",
      label: "Add On",
      align: "left",
      width: "160px",
      class: "whitespace-normal min-w-[150px]",
      type: "text",
      filterKey: "client_information_card_specs_card_options"
    });
    cols.push({
      key: "packed_qty",
      label: "Packed Qty",
      align: "left",
      width: "100px",
      class: "whitespace-nowrap",
      type: "text",
      filterKey: "packaging_packaging_logistics_qty_cards"
    });
  }

  if (props.stage === 'delivery') {
    cols.push({
      key: "gift_type",
      label: "Gift Option",
      align: "left",
      width: "140px",
      class: "whitespace-nowrap",
      type: "select",
      placeholder: "All Options",
      filterKey: "dispatch_delivery_dispatch_mode_gift_type",
      options: [
        { label: "With Gift", value: "with_gift" },
        { label: "Without Gift", value: "without_gift" },
        { label: "Not Assigned", value: "n/a" }
      ]
    });
    cols.push({
      key: "delivery_address",
      label: "Delivery Address",
      align: "left",
      width: "200px",
      class: "whitespace-normal min-w-[180px]",
      type: "text",
      filterKey: "dispatch_delivery_delivery_location_place_name"
    });
    cols.push({
      key: "modes",
      label: "Mode of Dispatch",
      align: "left",
      width: "160px",
      class: "whitespace-nowrap",
      type: "select",
      placeholder: "All Modes",
      filterKey: "dispatch_delivery_dispatch_mode_modes",
      options: [
        { label: "Shop Pickup", value: "Shop Pickup" },
        { label: "Direct to Client", value: "Direct to Client" },
        { label: "Bus", value: "Bus" },
        { label: "Transport", value: "Transport" },
        { label: "Courier", value: "Courier" },
        { label: "Not Assigned", value: "n/a" }
      ]
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
        { label: "Content Not Received", value: "Content Not Received" },
        { label: "Not Assigned", value: "n/a" }
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

    cols.push({
      key: "design_outputs_col",
      label: "Design Output",
      align: "left",
      width: "180px",
      type: "text",
      filterKey: "designing_design_print_design_outputs"
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
    multiple: true,
    defaultFilter: ["Pending", "Process"],
    hideAllOption: true,
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
    label: props.stage === 'printing' ? "Received Date" : "Delivery Date",
    align: "left",
    width: "140px",
    class: "whitespace-nowrap",
    type: "date",
    filterKey: "delivery_date"
  });

  // Add Audit
  if (props.stage !== 'client-information' && props.stage !== 'designing') {
    if (props.stage !== 'printing' && props.stage !== 'packaging' && props.stage !== 'delivery') {
      cols.push({
        key: "created_at",
        label: "Created",
        align: "left",
        width: "120px",
        class: "whitespace-nowrap",
        type: "date",
        filterKey: "created_at"
      });
    }
  }

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
      const log = order.packaging?.packaging_logistics;
      let names = [];
      if (Array.isArray(log?.assigned_by_multiple)) names.push(...log.assigned_by_multiple);
      if (Array.isArray(log?.crafted_by_multiple)) names.push(...log.crafted_by_multiple);
      if (log?.crafted_by && !names.includes(log.crafted_by)) names.push(log.crafted_by);
      return names.length > 0 ? [...new Set(names)].join(", ") : "N/A";
    case 'delivery':
      return order.dispatch_delivery?.dispatch_mode?.packed_by || "N/A";
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
      date = order.printing?.printing_status?.confirmed_date || order.printing?.printing_status?.assigned_date;
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

const getSentToPrintDate = (order) => {
  if (props.stage !== 'printing') return "N/A";
  const ps = order.printing?.printing_status;
  const types = order.client_information?.card_specs?.type?.split(',') || [];
  let sentDate = null;
  for (const t of types) {
    if (ps?.[t.trim() + '_sent_to_print_date']) {
      sentDate = ps[t.trim() + '_sent_to_print_date'];
      break;
    }
  }
  return formatDate(sentDate);
};

const getAssignedDate = (order) => {
  return formatDate(getAssignedDateRaw(order));
};

const getDaysFromAssigned = (order) => {
  let dateStr = getAssignedDateRaw(order);
  
  // Specific override for printing stage calculation:
  if (props.stage === 'printing') {
    const ps = order.printing?.printing_status;
    const types = order.client_information?.card_specs?.type?.split(',') || [];
    let sentDate = null;
    let receivedDate = null;

    for (const t of types) {
      const typeId = t.trim();
      if (ps?.[typeId + '_sent_to_print_date'] && !sentDate) {
        sentDate = ps[typeId + '_sent_to_print_date'];
      }
      if (ps?.[typeId + '_delivery_date'] && !receivedDate) {
        receivedDate = ps[typeId + '_delivery_date'];
      }
    }

    if (!sentDate) return "N/A";

    const start = new Date(sentDate);
    const end = receivedDate ? new Date(receivedDate) : new Date();
    
    start.setHours(0, 0, 0, 0);
    end.setHours(0, 0, 0, 0);

    const diffTime = end - start;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    return `Day ${Math.max(0, diffDays) + 1}`;
  }

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
