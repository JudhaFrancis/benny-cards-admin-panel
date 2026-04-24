<template>
  <span
    :class="
      cn(
        'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
        variants[props.variant || 'default'],
        props.class,
      )
    "
  >
    <slot />
  </span>
</template>

<script setup>
const props = defineProps({
  class: String,
  variant: String,
});

const variants = {
  default:
    "border-transparent bg-primary text-primary-foreground hover:bg-primary/80",
  secondary:
    "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",
  destructive:
    "border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80",
  outline: "text-foreground border-slate-200",
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
