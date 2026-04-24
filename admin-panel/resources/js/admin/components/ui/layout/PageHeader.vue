<template>
  <div
    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500"
    v-bind="$attrs"
  >
    <div class="space-y-1">
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
        {{ title }}
      </h1>
      <p v-if="subtitle || $slots.subtitle" class="text-sm text-slate-500">
        <slot name="subtitle">{{ subtitle }}</slot>
      </p>
    </div>
    <div v-if="$slots.actions" class="flex items-center gap-3">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: "",
  },
});
</script>
