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
          <span class="text-sm font-medium text-slate-900">{{
            isOccasionOther ? 'Other' : (clientInfo.occasion ? capitalize(clientInfo.occasion) : "N/A")
          }}</span>
        </div>
        <div v-if="isOccasionOther && clientInfo.occasion && clientInfo.occasion !== 'other'" class="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-100/50 text-sm font-medium text-slate-600 italic">
          "{{ clientInfo.occasion }}"
        </div>
      </div>

    </div>

    <!-- Audit Information -->
    <div
      v-if="order.client_information && !hideAudit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
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
  User as UserIcon,
  MapPin as MapPinIcon,
  Phone as PhoneIcon,
  PartyPopper as PartyPopperIcon,
  Calendar as CalendarIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
  hideAudit: { type: Boolean, default: false },
});

const clientInfo = computed(() => props.order.client_information?.client_info || {});

const standardOccasions = ["wedding", "birthday", "engagement", "anniversary", "house_warming"];
const isOccasionOther = computed(() => {
  const val = clientInfo.value.occasion;
  return val && val !== "none" && !standardOccasions.includes(val);
});

const capitalize = (str) => {
  if (!str) return "";
  return str.charAt(0).toUpperCase() + str.slice(1).replace("_", " ");
};

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

