<template>
  <div
    :class="
      cn(
        'rounded-3xl border border-slate-100 bg-white shadow-sm transition-all duration-300',
        props.class,
      )
    "
  >
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
