<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Assignment -->
    <div class="space-y-4">
      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700">Packed By <span class="text-red-500">*</span></label>
        <ContextDropdown :model-value="packagingStatus.packed_by"
          @update:model-value="(val) => updateSection('packed_by', val)" :options="staffOptions"
          placeholder="Select staff" :icon="UserIcon" />
      </div>
    </div>

    <!-- Audit Footer -->
    <div v-if="packagingStatus._audit && !hideAudit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400">
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600">{{
          formatAuditDate(packagingStatus._audit.updated_at) }}</span>
        by
        <span class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2">{{
          packagingStatus._audit.updated_by }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { User as UserIcon, Clock as ClockIcon } from "lucide-vue-next";
import ContextDropdown from "../../../../ui/dropdowns/ContextDropdown.vue";


const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  staffOptions: {
    type: Array,
    default: () => [],
  },
  hideAudit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order"]);

const packagingStatus = computed(() => {
  return props.order.packaging?.packaging_status || {};
});


const updateSection = (key, value) => {
  const newOrder = JSON.parse(JSON.stringify(props.order));

  // Ensure path exists
  if (!newOrder.packaging) newOrder.packaging = {};
  if (!newOrder.packaging.packaging_status)
    newOrder.packaging.packaging_status = {};

  newOrder.packaging.packaging_status[key] = value;
  emit("update:order", newOrder);
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
