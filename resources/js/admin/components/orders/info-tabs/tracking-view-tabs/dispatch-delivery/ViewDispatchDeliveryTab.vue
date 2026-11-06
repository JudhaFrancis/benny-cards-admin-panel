<template>
  <div class="space-y-12">
    <ViewDeliveryLocationSection :order="order" :hide-audit="true" />
    <div class="border-t border-slate-100"></div>
    <ViewDispatchModeSection :order="order" :hide-audit="true" />
    <div class="border-t border-slate-100"></div>
    <ViewDispatchDetailsSection :order="order" :hide-audit="true" />

    <!-- Unified Audit Footer -->
    <!-- Unified Audit Footer -->
    <div v-if="order.dispatch_delivery"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2">
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          order.dispatch_delivery.modified_by?.name || "System"
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(order.dispatch_delivery.updated_at || order.dispatch_delivery.created_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import ViewDeliveryLocationSection from "./ViewDeliveryLocationSection.vue";
import ViewDispatchModeSection from "./ViewDispatchModeSection.vue";
import ViewDispatchDetailsSection from "./ViewDispatchDetailsSection.vue";
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
