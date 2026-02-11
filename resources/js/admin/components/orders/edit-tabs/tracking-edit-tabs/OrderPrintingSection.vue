<template>
  <div class="space-y-8">
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700"
        >Assigned Date <span class="text-red-500">*</span></label
      >
      <input
        type="date"
        v-model="printingStatus.assigned_date"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>

    <!-- Readymade Card -->
    <div
      class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 space-y-6"
    >
      <h4 class="font-bold text-slate-900">Readymade Card</h4>
      <div class="flex flex-wrap gap-4">
        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            printingStatus.readymade_ordered
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              printingStatus.readymade_ordered
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
              <path d="M3 6h18" />
              <path d="M16 10a4 4 0 0 1-8 0" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">Ordered</p>
            <input
              type="checkbox"
              v-model="printingStatus.readymade_ordered"
              class="sr-only"
            />
          </div>
        </label>

        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            printingStatus.readymade_sub_received
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              printingStatus.readymade_sub_received
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="m7.5 4.27 9 5.15" />
              <path
                d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"
              />
              <path d="m3.27 6.96 8.73 5.05 8.73-5.05" />
              <path d="M12 22.08V12" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              Card Received
            </p>
            <input
              type="checkbox"
              v-model="printingStatus.readymade_sub_received"
              class="sr-only"
            />
          </div>
        </label>

        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            printingStatus.readymade_sent_to_print
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              printingStatus.readymade_sent_to_print
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <polyline points="6 9 6 2 18 2 18 9" />
              <path
                d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"
              />
              <rect x="6" y="14" width="12" height="8" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              Sent to Print
            </p>
            <input
              type="checkbox"
              v-model="printingStatus.readymade_sent_to_print"
              class="sr-only"
            />
          </div>
        </label>
      </div>
      <div class="space-y-3">
        <label
          class="text-xs font-black uppercase tracking-wider text-slate-400"
          >Follow Up Status</label
        >
        <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
          <label
            v-for="day in days"
            :key="day"
            class="flex flex-col items-center justify-center p-2 rounded-xl border transition-all cursor-pointer group"
            :class="
              readymadeFollowUpList.includes(day)
                ? 'bg-primary/5 border-primary shadow-sm ring-2 ring-primary/5'
                : 'bg-white border-slate-200 hover:border-slate-300'
            "
          >
            <span
              class="text-[10px] font-black uppercase tracking-tighter mb-1.5 transition-colors"
              :class="
                readymadeFollowUpList.includes(day)
                  ? 'text-primary'
                  : 'text-slate-400 group-hover:text-slate-500'
              "
              >{{ day }}</span
            >
            <input
              type="checkbox"
              :value="day"
              v-model="readymadeFollowUpList"
              class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 transition-transform group-active:scale-90"
            />
          </label>
        </div>
      </div>
    </div>

    <!-- Customize Card -->
    <div
      class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 space-y-6"
    >
      <h4 class="font-bold text-slate-900">Customize Card</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Sent to Print Date <span class="text-red-500">*</span></label
          >
          <input
            type="date"
            v-model="printingStatus.customize_sent_to_print_date"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Delivery Date <span class="text-red-500">*</span></label
          >
          <input
            type="date"
            v-model="printingStatus.customize_delivery_date"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
      </div>
      <div class="space-y-3">
        <label
          class="text-xs font-black uppercase tracking-wider text-slate-400"
          >Follow Up Status</label
        >
        <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
          <label
            v-for="day in days"
            :key="day"
            class="flex flex-col items-center justify-center p-2 rounded-xl border transition-all cursor-pointer group"
            :class="
              customizeFollowUpList.includes(day)
                ? 'bg-primary/5 border-primary shadow-sm ring-2 ring-primary/5'
                : 'bg-white border-slate-200 hover:border-slate-300'
            "
          >
            <span
              class="text-[10px] font-black uppercase tracking-tighter mb-1.5 transition-colors"
              :class="
                customizeFollowUpList.includes(day)
                  ? 'text-primary'
                  : 'text-slate-400 group-hover:text-slate-500'
              "
              >{{ day }}</span
            >
            <input
              type="checkbox"
              :value="day"
              v-model="customizeFollowUpList"
              class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 transition-transform group-active:scale-90"
            />
          </label>
        </div>
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700"
        >Any Printing Issues</label
      >
      <textarea
        v-model="printingStatus.printing_issues"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Describe any issues..."
      ></textarea>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Delay Reason</label>
      <textarea
        v-model="printingStatus.delay_reason"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Reason for delay..."
      ></textarea>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="printingStatus._audit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(printingStatus._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ printingStatus._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { Clock as ClockIcon } from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const printingStatus = computed(() => {
  if (!props.order.tracking.printing_status) {
    props.order.tracking.printing_status = {};
  }
  return props.order.tracking.printing_status;
});

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};

const readymadeFollowUpList = computed({
  get: () =>
    printingStatus.value.readymade_follow_up
      ? printingStatus.value.readymade_follow_up.split(",")
      : [],
  set: (val) => {
    printingStatus.value.readymade_follow_up = val.join(",");
  },
});

const customizeFollowUpList = computed({
  get: () =>
    printingStatus.value.customize_follow_up
      ? printingStatus.value.customize_follow_up.split(",")
      : [],
  set: (val) => {
    printingStatus.value.customize_follow_up = val.join(",");
  },
});

const days = ["Day 1", "Day 2", "Day 3", "Day 4", "Day 5", "Day 6", "Day 7"];
</script>
