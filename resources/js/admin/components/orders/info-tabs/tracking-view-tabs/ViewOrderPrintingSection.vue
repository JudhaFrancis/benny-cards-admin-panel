<template>
  <div class="space-y-8">
    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
        >Assigned Date</label
      >
      <div
        class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
      >
        <CalendarIcon class="h-4 w-4 text-slate-400" />
        <span class="text-sm font-medium text-slate-900">{{
          formatDate(printingStatus.assigned_date) || "N/A"
        }}</span>
      </div>
    </div>

    <!-- Readymade Card Status -->
    <div
      class="p-6 bg-slate-50/50 rounded-2xl border border-slate-100 space-y-6"
    >
      <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
        <BoxIcon class="h-4 w-4 text-primary" /> Readymade Card Status
      </h4>
      <div class="flex flex-wrap gap-4">
        <div
          class="flex-1 min-w-[120px] flex items-center gap-3 p-3 rounded-xl border"
          :class="
            printingStatus.readymade_ordered
              ? 'bg-white border-primary border shadow-sm'
              : 'bg-slate-100/50 border-slate-100 opacity-50'
          "
        >
          <div
            class="p-1.5 rounded-lg"
            :class="
              printingStatus.readymade_ordered
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <ShoppingCartIcon class="h-3.5 w-3.5" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              printingStatus.readymade_ordered
                ? 'text-slate-900'
                : 'text-slate-400'
            "
            >Ordered</span
          >
        </div>
        <div
          class="flex-1 min-w-[120px] flex items-center gap-3 p-3 rounded-xl border"
          :class="
            printingStatus.readymade_sub_received
              ? 'bg-white border-primary border shadow-sm'
              : 'bg-slate-100/50 border-slate-100 opacity-50'
          "
        >
          <div
            class="p-1.5 rounded-lg"
            :class="
              printingStatus.readymade_sub_received
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <PackageIcon class="h-3.5 w-3.5" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              printingStatus.readymade_sub_received
                ? 'text-slate-900'
                : 'text-slate-400'
            "
            >Received</span
          >
        </div>
        <div
          class="flex-1 min-w-[120px] flex items-center gap-3 p-3 rounded-xl border"
          :class="
            printingStatus.readymade_sent_to_print
              ? 'bg-white border-primary border shadow-sm'
              : 'bg-slate-100/50 border-slate-100 opacity-50'
          "
        >
          <div
            class="p-1.5 rounded-lg"
            :class="
              printingStatus.readymade_sent_to_print
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <PrinterIcon class="h-3.5 w-3.5" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              printingStatus.readymade_sent_to_print
                ? 'text-slate-900'
                : 'text-slate-400'
            "
            >To Print</span
          >
        </div>
      </div>
      <div class="space-y-3" v-if="printingStatus.readymade_follow_up">
        <label
          class="text-[10px] font-black uppercase tracking-widest text-slate-400"
          >Follow Up History</label
        >
        <div class="flex flex-wrap gap-2">
          <span
            v-for="day in days"
            :key="day"
            class="px-2 py-1 rounded-md text-[10px] font-bold border"
            :class="
              readymadeFollowUpList.includes(day)
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-slate-50 text-slate-300 border-slate-100'
            "
          >
            {{ day }}
          </span>
        </div>
      </div>
    </div>

    <!-- Customize Card Status -->
    <div
      class="p-6 bg-slate-50/50 rounded-2xl border border-slate-100 space-y-6"
    >
      <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
        <BrushIcon class="h-4 w-4 text-primary" /> Customize Card Status
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-slate-400"
            >Sent to Print Date</label
          >
          <div
            class="p-3 rounded-xl bg-white border border-slate-100 text-sm font-medium text-slate-900"
          >
            {{ formatDate(printingStatus.customize_sent_to_print_date) || "—" }}
          </div>
        </div>
        <div class="space-y-2">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-slate-400"
            >Delivery Date</label
          >
          <div
            class="p-3 rounded-xl bg-white border border-slate-100 text-sm font-medium text-slate-900"
          >
            {{ formatDate(printingStatus.customize_delivery_date) || "—" }}
          </div>
        </div>
      </div>
      <div class="space-y-3" v-if="printingStatus.customize_follow_up">
        <label
          class="text-[10px] font-black uppercase tracking-widest text-slate-400"
          >Follow Up History</label
        >
        <div class="flex flex-wrap gap-2">
          <span
            v-for="day in days"
            :key="day"
            class="px-2 py-1 rounded-md text-[10px] font-bold border"
            :class="
              customizeFollowUpList.includes(day)
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-slate-50 text-slate-300 border-slate-100'
            "
          >
            {{ day }}
          </span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Printing Issues</label
        >
        <div
          class="p-4 rounded-xl bg-rose-50 border border-rose-100 text-sm text-rose-700 italic min-h-[60px]"
        >
          {{ printingStatus.printing_issues || "No issues reported." }}
        </div>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Delay Reason</label
        >
        <div
          class="p-4 rounded-xl bg-amber-50 border border-amber-100 text-sm text-amber-700 italic min-h-[60px]"
        >
          {{ printingStatus.delay_reason || "No delays." }}
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="printingStatus._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          printingStatus._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(printingStatus._audit.updated_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Calendar as CalendarIcon,
  Box as BoxIcon,
  ShoppingCart as ShoppingCartIcon,
  Package as PackageIcon,
  Printer as PrinterIcon,
  Brush as BrushIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const printingStatus = computed(
  () => props.order.tracking?.printing_status || {},
);

const readymadeFollowUpList = computed(() =>
  printingStatus.value.readymade_follow_up
    ? printingStatus.value.readymade_follow_up.split(",")
    : [],
);

const customizeFollowUpList = computed(() =>
  printingStatus.value.customize_follow_up
    ? printingStatus.value.customize_follow_up.split(",")
    : [],
);

const days = ["Day 1", "Day 2", "Day 3", "Day 4", "Day 5", "Day 6", "Day 7"];

const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const d = date.toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
  const t = date.toLocaleTimeString("en-GB", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  }).toUpperCase();
  return `${d} at ${t}`;
};
</script>

