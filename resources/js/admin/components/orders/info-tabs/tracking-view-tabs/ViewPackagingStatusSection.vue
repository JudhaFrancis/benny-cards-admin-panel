<template>
  <div class="space-y-8">
    <!-- Status Overview -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <PackageIcon class="h-4 w-4" />
        Packaging Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <div
          class="flex-1 min-w-[160px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            packagingStatus.packed_with_gift
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              packagingStatus.packed_with_gift
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <GiftIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              packagingStatus.packed_with_gift
                ? 'text-slate-900'
                : 'text-slate-400'
            "
            >Packed with Gift</span
          >
        </div>

        <div
          class="flex-1 min-w-[160px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            packagingStatus.packed_without_gift
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              packagingStatus.packed_without_gift
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <PackageIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              packagingStatus.packed_without_gift
                ? 'text-slate-900'
                : 'text-slate-400'
            "
            >Packed Without Gift</span
          >
        </div>
      </div>
    </div>

    <!-- Assignment -->
    <div class="space-y-4">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Packed By</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <UserIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            packagingStatus.packed_by || "N/A"
          }}</span>
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="packagingStatus._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          packagingStatus._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(packagingStatus._audit.updated_at)
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
  Gift as GiftIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const packagingStatus = computed(
  () => props.order.tracking?.packaging_status || {},
);

const formatAuditDate = (dateString) => {
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};
</script>
