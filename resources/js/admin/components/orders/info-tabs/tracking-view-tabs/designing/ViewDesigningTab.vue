<template>
  <div class="space-y-12">
    <ViewWorkAssignSection :order="order" />
    <div class="border-t border-slate-100"></div>
    <ViewDesignPrintSection :order="order" />
  </div>
</template>

<script setup>
import ViewWorkAssignSection from "./ViewWorkAssignSection.vue";
import ViewDesignPrintSection from "./ViewDesignPrintSection.vue";

defineProps({
  order: { type: Object, required: true },
});
</script>
