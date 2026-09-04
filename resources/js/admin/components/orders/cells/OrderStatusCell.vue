<template>
  <div class="flex flex-col gap-1 items-start">
    <!-- Status Badge -->
    <span :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-colors duration-200',
      (order.resolved_status && orderStatusStyles[order.resolved_status.toLowerCase()]) || 'bg-slate-100 text-slate-800 border-slate-200'
    ]">
      {{ order.resolved_status || "New Order" }}
    </span>

    <!-- Assigned Personnel (Conditional) -->
    <span v-if="shouldShowAssigned" class="text-[10px] text-slate-500 font-medium">
      <span class="text-slate-400">By:</span> {{ order.assigned_name || "Unassigned" }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

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
