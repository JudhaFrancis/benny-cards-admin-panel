<template>
  <h3
    :class="cn('text-lg font-bold text-slate-900 tracking-tight', props.class)"
  >
    <slot />
  </h3>
</template>

<script setup>
const props = defineProps({
  class: String,
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
