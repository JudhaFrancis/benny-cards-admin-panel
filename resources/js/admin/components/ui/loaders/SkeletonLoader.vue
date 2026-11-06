<template>
  <div :class="cn('animate-pulse bg-slate-200 rounded-lg', className)" :style="style"></div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  width: { type: String, default: '100%' },
  height: { type: String, default: '20px' },
  className: { type: String, default: '' },
  variant: { type: String, default: 'rect' } // 'rect', 'circle', 'text'
});

const style = computed(() => ({
  width: props.width,
  height: props.height,
  borderRadius: props.variant === 'circle' ? '50%' : (props.variant === 'text' ? '4px' : '8px')
}));

function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}
</script>
