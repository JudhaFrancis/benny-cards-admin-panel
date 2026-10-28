<template>
  <div class="flex flex-col gap-1 items-start">
    <!-- Status Badge -->
    <AppTooltip v-if="isCancelled">
      <template #trigger>
        <span :class="[
          'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-colors duration-200 cursor-help',
          (order.resolved_status && orderStatusStyles[order.resolved_status.toLowerCase()]) || 'bg-rose-500/10 text-rose-500 border-rose-500/20'
        ]">
          {{ order.resolved_status || "Cancelled" }}
          <Info class="w-3 h-3 text-rose-500 opacity-80" />
        </span>
      </template>
      <div class="p-1 space-y-1 text-left max-w-xs normal-case tracking-normal">
        <div v-if="order.cancelled_module" class="text-[11px] font-semibold text-rose-300">
          <span class="text-slate-400 font-normal">Cancelled In:</span> {{ order.cancelled_module }}
        </div>
        <div v-if="order.cancelled_reason" class="text-[11px] text-slate-100">
          <span class="text-slate-400 font-normal">Reason:</span> {{ order.cancelled_reason }}
        </div>
        <div v-if="!order.cancelled_module && !order.cancelled_reason" class="text-[11px] text-slate-300 italic">
          Order cancelled
        </div>
      </div>
    </AppTooltip>
    <span v-else :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-colors duration-200',
      (order.resolved_status && orderStatusStyles[order.resolved_status.toLowerCase()]) || 'bg-slate-100 text-slate-800 border-slate-200'
    ]">
      {{ order.resolved_status || "New Order" }}
    </span>

    <span v-if="isCancelled && order.cancelled_reason" 
      class="text-[9px] text-rose-500 font-medium truncate max-w-[120px]"
      :title="order.cancelled_reason">
      {{ order.cancelled_reason }}
    </span>

    <!-- Assigned Personnel (Conditional) -->
    <span v-if="shouldShowAssigned" class="text-[10px] text-slate-500 font-medium">
      <span class="text-slate-400">By:</span> {{ order.assigned_name || "Unassigned" }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Info } from 'lucide-vue-next';
import AppTooltip from '../../ui/display/AppTooltip.vue';

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  orderStatusStyles: {
    type: Object,
    required: true
  }
});

const isCancelled = computed(() => {
  return props.order.resolved_status?.toLowerCase() === 'cancelled' || props.order.status?.toLowerCase() === 'cancelled';
});

const shouldShowAssigned = computed(() => {
  const status = props.order.resolved_status;
  if (!status) return false;
  
  const activeStatuses = [
    'Designing in Progress',
    'Printing in Progress',
    'Packing in Progress',
    'Out for Delivery'
  ];
  
  return activeStatuses.includes(status);
});
</script>
