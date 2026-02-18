<template>
  <div class="space-y-8">
    <!-- Status Overview -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <PackageIcon class="h-4 w-4" />
        Logistics Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <div
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            logistics.card_received
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              logistics.card_received
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <DownloadIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              logistics.card_received ? 'text-slate-900' : 'text-slate-400'
            "
            >Card Received</span
          >
        </div>

        <div
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            logistics.crafting_done
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              logistics.crafting_done
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <ScissorsIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              logistics.crafting_done ? 'text-slate-900' : 'text-slate-400'
            "
            >Crafting Done</span
          >
        </div>
      </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Crafted By</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <UserIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            logistics.crafted_by || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Names on Cards</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <TypeIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            logistics.names || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Date</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <CalendarIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            formatDate(logistics.date) || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Qty of Cards</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <HashIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900 tracking-wider">{{
            logistics.qty_cards || "0"
          }}</span>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Accessory Details</label
        >
        <div
          class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm text-slate-700 leading-relaxed whitespace-pre-wrap min-h-[80px]"
        >
          {{ logistics.logistics_details || "No accessory details." }}
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Issues in Card</label
        >
        <div
          class="p-4 rounded-xl bg-rose-50 border border-rose-100 text-sm text-rose-700 italic min-h-[80px]"
        >
          {{ logistics.card_issues || "No issues found." }}
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="logistics._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          logistics._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(logistics._audit.updated_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  User as UserIcon,
  Package as PackageIcon,
  Download as DownloadIcon,
  Scissors as ScissorsIcon,
  Type as TypeIcon,
  Calendar as CalendarIcon,
  Hash as HashIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const logistics = computed(
  () => props.order.tracking?.packaging_logistics || {},
);

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

