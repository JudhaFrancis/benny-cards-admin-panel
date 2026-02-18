<template>
  <div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Name</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <UserIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            clientInfo.name || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Contact No</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <PhoneIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            clientInfo.phone || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2 md:col-span-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Place (Address)</label
        >
        <div
          class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <MapPinIcon class="h-4 w-4 text-slate-400 mt-0.5" />
          <span class="text-sm font-medium text-slate-900 leading-relaxed">{{
            clientInfo.address || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Occasion</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <PartyPopperIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900 capitalize">{{
            clientInfo.occasion || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Expected Delivery Date</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 border border-emerald-100"
        >
          <CalendarIcon class="h-4 w-4 text-emerald-500" />
          <span class="text-sm font-bold text-emerald-700">{{
            formatDate(clientInfo.expected_delivery_date) || "N/A"
          }}</span>
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="clientInfo._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          clientInfo._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(clientInfo._audit.updated_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  User as UserIcon,
  MapPin as MapPinIcon,
  Phone as PhoneIcon,
  PartyPopper as PartyPopperIcon,
  Calendar as CalendarIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const clientInfo = computed(() => props.order.tracking?.client_info || {});

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

