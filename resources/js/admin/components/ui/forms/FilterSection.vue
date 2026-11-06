<template>
  <div class="py-4 first:pt-0 last:pb-0">
    <div v-if="label" class="mb-3 px-1">
      <h5
        class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] mb-1"
      >
        {{ label }}
      </h5>
      <p v-if="description" class="text-[10px] text-slate-400 font-medium">
        {{ description }}
      </p>
    </div>
    <div :class="cn('space-y-4', contentClass)">
      <slot />
    </div>
    <div v-if="!last" class="mt-4 h-px bg-slate-50"></div>
  </div>
</template>

<script setup>
const props = defineProps({
  label: String,
  description: String,
  last: {
    type: Boolean,
    default: false,
  },
  contentClass: String,
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
