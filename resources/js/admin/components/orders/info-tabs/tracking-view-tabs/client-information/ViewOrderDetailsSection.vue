<template>
  <div class="space-y-8">
    <!-- Order Information Group -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
        <ClipboardListIcon class="h-5 w-5 text-primary" />
        Order Information
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Order No</label>
          <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-600 font-mono text-sm">
            #{{ order.order_number }}
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Order Date</label>
          <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900">
            {{ formatDate(order.order_date) || "N/A" }}
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Priority</label>
          <div :class="['p-3 rounded-xl border text-sm font-bold', getPriorityClass(order.priority || 'P4')]">
            {{ getPriorityLabel(order.priority || 'P4') }}
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Order Taken By</label>
          <div
            class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900 flex items-center gap-2">
            <UserIcon class="h-4 w-4 text-slate-400" />
            {{ jobDetails.order_taken_by || "N/A" }}
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Delivery Date</label>
          <div
            class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900 flex items-center gap-2">
            <CalendarIcon class="h-4 w-4 text-slate-400" />
            {{ formatDate(order.delivery_date) || "N/A" }}
          </div>
        </div>
      </div>
    </div>

    <div class="border-t border-slate-100"></div>

    <!-- Source & Reference Group -->
    <div class="space-y-6">
      <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
        <CompassIcon class="h-5 w-5 text-primary" />
        Source & Reference
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Order Placed In</label>
          <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900">
            {{ jobDetails.order_placed_in || "N/A" }}
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Reference</label>
          <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900">
            {{ jobDetails.reference || "N/A" }}
          </div>
        </div>
      </div>
      <div v-if="jobDetails.remarks" class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Other Reference / Remarks</label>
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm text-slate-700 leading-relaxed italic">
          "{{ jobDetails.remarks }}"
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div v-if="order.client_information && !hideAudit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2">
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          order.client_information.modified_by?.name || order.client_information.added_by?.name || "System"
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(order.client_information.updated_at || order.client_information.created_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  ClipboardList as ClipboardListIcon,
  User as UserIcon,
  Compass as CompassIcon,
  Clock as ClockIcon,
  Calendar as CalendarIcon,
} from "lucide-vue-next";
import { getPriorityLabel, getPriorityClass } from "../../../../../constants/orderPriorities";

const props = defineProps({
  order: { type: Object, required: true },
  hideAudit: { type: Boolean, default: false },
});

const jobDetails = computed(() => props.order.client_information?.order_details || {});

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  const d = `${day}-${month}-${year}`;
  const t = date.toLocaleTimeString("en-GB", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  }).toUpperCase();
  return `${d} at ${t}`;
};
</script>
