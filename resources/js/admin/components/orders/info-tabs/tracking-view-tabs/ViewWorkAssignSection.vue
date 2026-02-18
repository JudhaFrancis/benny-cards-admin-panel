<template>
  <div class="space-y-8">
    <!-- Status Overview -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <CheckSquareIcon class="h-4 w-4" />
        Process Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <div
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            workAssign.content_received
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              workAssign.content_received
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <FilesIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              workAssign.content_received ? 'text-slate-900' : 'text-slate-400'
            "
            >Content Received</span
          >
        </div>

        <div
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            workAssign.clear_content
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              workAssign.clear_content
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <CheckSquareIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              workAssign.clear_content ? 'text-slate-900' : 'text-slate-400'
            "
            >Clear Content</span
          >
        </div>

        <div
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            workAssign.tag
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              workAssign.tag
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <HashIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="workAssign.tag ? 'text-slate-900' : 'text-slate-400'"
            >Tag</span
          >
        </div>
      </div>
    </div>

    <!-- Assignment Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Assigned To</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <UserIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            workAssign.assigned_to || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Deadline</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-rose-50 border border-rose-100"
        >
          <CalendarIcon class="h-4 w-4 text-rose-500" />
          <span class="text-sm font-bold text-rose-700">{{
            formatDate(workAssign.deadline) || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Content By</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <FilesIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            workAssign.content_by || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Completed By</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <UserPlusIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            workAssign.completed_by || "N/A"
          }}</span>
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="workAssign._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          workAssign._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(workAssign._audit.updated_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  User as UserIcon,
  Calendar as CalendarIcon,
  CheckSquare as CheckSquareIcon,
  Files as FilesIcon,
  Hash as HashIcon,
  UserPlus as UserPlusIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const workAssign = computed(() => props.order.tracking?.work_assign || {});

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

