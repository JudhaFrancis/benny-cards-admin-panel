<template>
  <div class="space-y-8">
    <!-- Timeline -->
    <div
      v-if="order?.timeline?.length"
      class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    >
      <h4
        class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2 pb-4 border-b border-slate-100"
      >
        <ClockIcon class="h-4 w-4 text-slate-500" /> Order Timeline
      </h4>
      <div
        class="relative pl-4 space-y-6 before:absolute before:left-[5px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100"
      >
        <div
          v-for="(step, index) in order.timeline"
          :key="index"
          class="relative pl-6"
        >
          <div
            class="absolute left-0 top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white shadow-sm z-10"
            :class="step.completed ? 'bg-emerald-500' : 'bg-slate-300'"
          />
          <p class="text-xs font-bold text-slate-900">
            {{ step.status }}
          </p>
          <p
            v-if="step.date"
            class="text-[10px] font-bold text-slate-400 uppercase mt-0.5"
          >
            {{ step.date }} • {{ step.time }}
          </p>
        </div>
      </div>
    </div>
    <div v-else class="text-center py-12 text-slate-400 text-sm">
      No tracking timeline available.
    </div>
  </div>
</template>

<script setup>
import { Clock as ClockIcon } from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});
</script>
