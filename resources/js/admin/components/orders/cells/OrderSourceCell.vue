<template>
  <div class="flex flex-col gap-1 text-[11px] font-medium tracking-tight">
    <!-- Order Taken By -->
    <div class="flex items-center gap-1.5 text-slate-600">
      <span class="text-slate-400 font-bold shrink-0">By:</span>
      <span class="text-slate-800 truncate">{{ order.order_taken_by || "N/A" }}</span>
    </div>

    <!-- Order Placed In -->
    <div class="flex items-center gap-1.5 text-slate-600">
      <span class="text-slate-400 font-bold shrink-0">In:</span>
      <span class="text-slate-800 truncate">{{ order.order_placed_in || "N/A" }}</span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  order: {
    type: Object,
    required: true
  }
});
</script>
