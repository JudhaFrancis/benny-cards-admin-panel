<template>
  <div class="space-y-12">
    <ViewPackagingLogisticsSection :order="order" :hide-audit="true" />
    <div class="border-t border-slate-100"></div>
    <ViewPackagingStatusSection :order="order" :hide-audit="true" />

    <!-- Unified Audit Footer -->
    <div v-if="order.packaging"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2">
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ order.packaging.modified_by?.name || order.added_by?.name || "System" }}</span
        >
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(order.packaging.updated_at || order.packaging.created_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import ViewPackagingLogisticsSection from "./ViewPackagingLogisticsSection.vue";
import ViewPackagingStatusSection from "./ViewPackagingStatusSection.vue";
import { Clock as ClockIcon } from "lucide-vue-next";

defineProps({
  order: { type: Object, required: true },
});

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
