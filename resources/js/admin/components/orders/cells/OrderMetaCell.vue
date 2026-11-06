<template>
  <div class="flex flex-col gap-0.5">
    <span class="text-[10px] text-slate-600 font-extrabold uppercase tracking-tight">
      {{ formatDate(date) }}
    </span>
    <span class="text-[9px] text-slate-400 font-medium">
      <span v-if="by">by {{ by }}</span>
      <span v-else class="italic">Unknown</span>
    </span>
  </div>
</template>

<script setup>
const props = defineProps({
  date: {
    type: String,
    required: false
  },
  by: {
    type: String,
    required: false
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
