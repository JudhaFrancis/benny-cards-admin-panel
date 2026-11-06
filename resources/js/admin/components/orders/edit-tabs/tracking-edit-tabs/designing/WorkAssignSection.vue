<template>
  <form
    @submit.prevent="save"
    class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500"
  >
    <!-- Status Overview Card -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <CheckSquareIcon class="h-4 w-4" />
        Process Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <!-- Content Received -->
        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            workAssign.content_received
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              workAssign.content_received
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <FilesIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              Content Received
            </p>
            <input
              type="checkbox"
              v-model="workAssign.content_received"
              @change="workAssign.content_received ? (workAssign.content_not_received = false) : null"
              class="sr-only"
            />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              workAssign.content_received
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon
              v-if="workAssign.content_received"
              class="h-3 w-3 text-white"
            />
          </div>
        </label>

        <!-- Content Not Received -->
        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            workAssign.content_not_received
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              workAssign.content_not_received
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <FileXIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              Content Not Received
            </p>
            <input
              type="checkbox"
              v-model="workAssign.content_not_received"
              @change="workAssign.content_not_received ? (workAssign.content_received = false) : null"
              class="sr-only"
            />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              workAssign.content_not_received
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon
              v-if="workAssign.content_not_received"
              class="h-3 w-3 text-white"
            />
          </div>
        </label>

        <!-- Clear Content -->
        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            workAssign.clear_content
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              workAssign.clear_content
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <CheckSquareIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              Clear Content
            </p>
            <input
              type="checkbox"
              v-model="workAssign.clear_content"
              class="sr-only"
            />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              workAssign.clear_content
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon
              v-if="workAssign.clear_content"
              class="h-3 w-3 text-white"
            />
          </div>
        </label>

        <!-- Tag -->
        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            workAssign.tag
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              workAssign.tag
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <HashIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">Tag</p>
            <input type="checkbox" v-model="workAssign.tag" class="sr-only" />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              workAssign.tag
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon v-if="workAssign.tag" class="h-3 w-3 text-white" />
          </div>
        </label>

        <!-- Need Pdf -->
        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            workAssign.need_pdf
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              workAssign.need_pdf
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <FileTextIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">Need Pdf</p>
            <input type="checkbox" v-model="workAssign.need_pdf" class="sr-only" />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              workAssign.need_pdf
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon v-if="workAssign.need_pdf" class="h-3 w-3 text-white" />
          </div>
        </label>
      </div>
    </div>

    <!-- Assignment Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Assigned To <span class="text-red-500">*</span></label
        >
        <SearchableDropdown
          v-model="workAssign.assigned_to"
          :options="staffOptions"
          placeholder="Select designer"
          :icon="UserIcon"
        />
      </div>

      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Assigned Date <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <DatePicker
            v-model="workAssign.assigned_date"
            placeholder="Select Assigned Date"
            custom-class="pl-11 py-2.5 text-xs"
            required
          >
            <template #leading>
              <CalendarIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
            </template>
          </DatePicker>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Deadline <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <DatePicker
            v-model="workAssign.deadline"
            placeholder="Select Deadline"
            custom-class="pl-11 py-2.5 text-xs"
            required
          >
            <template #leading>
              <CalendarIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
            </template>
          </DatePicker>
        </div>
      </div>

      <div class="space-y-3">
        <label class="text-sm font-medium text-slate-700">Assign Timings</label>
        <div class="grid grid-cols-5 gap-1">
          <label v-for="timing in ['10am', '12pm', '2pm', '4pm', '6pm']" :key="timing" 
            class="flex items-center justify-center py-2 px-1 rounded-xl border text-[9px] font-black transition-all cursor-pointer whitespace-nowrap"
            :class="workAssign.assign_timings === timing ? 'bg-primary border-primary text-white shadow-md' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300'"
          >
            <input type="radio" v-model="workAssign.assign_timings" :value="timing" class="sr-only" />
            {{ timing }}
          </label>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Content By <span class="text-red-500">*</span></label
        >
        <SearchableDropdown
          v-model="workAssign.content_by"
          :options="staffOptions"
          placeholder="Select staff"
          :icon="FilesIcon"
        />
      </div>

      <div class="space-y-2 text-slate-900 font-medium">
        <label class="text-sm font-medium text-slate-700"
          >Completed By <span class="text-red-500">*</span></label
        >
        <SearchableDropdown
          v-model="workAssign.completed_by"
          :options="staffOptions"
          placeholder="Select staff"
          :icon="UserPlusIcon"
        />
      </div>

      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Completed Date <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <DatePicker
            v-model="workAssign.completed_date"
            placeholder="Select Completed Date"
            custom-class="pl-11 py-2.5 text-xs"
            required
          >
            <template #leading>
              <CalendarIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
            </template>
          </DatePicker>
        </div>
      </div>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="workAssign._audit && !hideAudit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(workAssign._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ workAssign._audit.updated_by }}</span
        >
      </span>
    </div>
  </form>
</template>

<script setup>
import { computed } from "vue";
import {
  User as UserIcon,
  Calendar as CalendarIcon,
  CheckSquare as CheckSquareIcon,
  Files as FilesIcon,
  FileX as FileXIcon,
  Hash as HashIcon,
  UserPlus as UserPlusIcon,
  Clock as ClockIcon,
  Check as CheckIcon,
  FileText as FileTextIcon,
} from "lucide-vue-next";
import SearchableDropdown from "../../../../ui/dropdowns/SearchableDropdown.vue";
import DatePicker from "../../../../ui/pickers/DatePicker.vue";

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

const workAssign = computed(() => {
  if (!props.order.designing) {
    props.order.designing = { work_assign: {} };
  }
  if (!props.order.designing.work_assign || Array.isArray(props.order.designing.work_assign)) {
    props.order.designing.work_assign = {};
  }
  
  // Auto-fill assigned_date with today's date if not already set
  if (!props.order.designing.work_assign.assigned_date) {
    const today = new Date().toISOString().split('T')[0];
    props.order.designing.work_assign.assigned_date = today;
  }
  
  return props.order.designing.work_assign;
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

const save = () => {
  // Save handled by parent
};
</script>
