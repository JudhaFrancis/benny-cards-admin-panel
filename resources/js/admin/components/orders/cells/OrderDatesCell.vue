<template>
  <div class="flex flex-col gap-1 text-[11px] font-medium tracking-tight">
    <!-- Order Date -->
    <div class="flex items-center gap-1.5 text-slate-600">
      <span class="w-5 text-slate-400 font-bold">OD</span>
      <span class="text-slate-800">{{ formatDate(order.order_date) }}</span>
    </div>

    <!-- Design Finalized Date -->
    <div class="flex items-center gap-1.5 text-slate-600">
      <span class="w-5 text-slate-400 font-bold">DF</span>
      <span class="text-slate-800">{{ formatDate(order.design_finalized_date) }}</span>
    </div>

    <!-- Delivery Date -->
    <div class="flex items-center gap-1.5 text-slate-600">
      <span class="w-5 text-slate-400 font-bold">DD</span>
      <span class="text-slate-800">{{ formatDate(order.delivery_date) }}</span>
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

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "N/A";
  
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};
</script>
