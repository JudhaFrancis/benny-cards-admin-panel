<template>
  <div class="space-y-12">
    <ViewPackagingLogisticsSection :order="order" />
    <div class="border-t border-slate-100"></div>
    <ViewPackagingStatusSection :order="order" />
  </div>
</template>

<script setup>
import ViewPackagingLogisticsSection from "./ViewPackagingLogisticsSection.vue";
import ViewPackagingStatusSection from "./ViewPackagingStatusSection.vue";

defineProps({
  order: { type: Object, required: true },
});
</script>
