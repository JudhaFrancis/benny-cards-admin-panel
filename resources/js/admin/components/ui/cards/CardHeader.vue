<template>
  <div :class="cn('px-6 py-5', props.class)">
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
