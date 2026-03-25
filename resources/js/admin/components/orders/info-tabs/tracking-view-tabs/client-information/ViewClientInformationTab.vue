<template>
  <div class="space-y-12">
    <ViewOrderDetailsSection :order="order" />
    <div class="border-t border-slate-100"></div>
    <ViewClientInfoSection :order="order" />
    <div class="border-t border-slate-100"></div>
    <ViewCardSpecsSection :order="order" />
  </div>
</template>

<script setup>
import ViewOrderDetailsSection from "./ViewOrderDetailsSection.vue";
import ViewClientInfoSection from "./ViewClientInfoSection.vue";
import ViewCardSpecsSection from "./ViewCardSpecsSection.vue";

defineProps({
  order: { type: Object, required: true },
});
</script>
