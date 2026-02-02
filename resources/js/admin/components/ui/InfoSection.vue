<template>
  <div class="space-y-6">
    <div v-if="title || $slots.header" class="flex items-center gap-4">
      <h4
        v-if="title"
        class="text-sm font-bold text-slate-400 uppercase tracking-widest"
      >
        {{ title }}
      </h4>
      <div class="flex-1 h-px bg-slate-100"></div>
      <slot name="header" />
    </div>

    <div :class="cn('grid gap-6', gridClass)">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  title: String,
  columns: {
    type: [Number, String],
    default: 1,
  },
});

const gridClass = computed(() => {
  const cols = parseInt(props.columns);
  switch (cols) {
    case 2:
      return "grid-cols-1 md:grid-cols-2";
    case 3:
      return "grid-cols-1 md:grid-cols-3";
    case 4:
      return "grid-cols-1 md:grid-cols-2 lg:grid-cols-4";
    default:
      return "grid-cols-1";
  }
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
