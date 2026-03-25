<template>
  <div class="space-y-12">
    <ViewDeliveryLocationSection :order="order" />
    <div class="border-t border-slate-100"></div>
    <ViewDispatchModeSection :order="order" />
    <div class="border-t border-slate-100"></div>
    <ViewDispatchDetailsSection :order="order" />
  </div>
</template>

<script setup>
import ViewDeliveryLocationSection from "./ViewDeliveryLocationSection.vue";
import ViewDispatchModeSection from "./ViewDispatchModeSection.vue";
import ViewDispatchDetailsSection from "./ViewDispatchDetailsSection.vue";

defineProps({
  order: { type: Object, required: true },
});
</script>
