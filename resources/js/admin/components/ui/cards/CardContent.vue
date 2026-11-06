<template>
  <div :class="cn('p-6', props.class)">
    <slot />
  </div>
</template>

<script setup>
const props = defineProps({
  class: String,
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
