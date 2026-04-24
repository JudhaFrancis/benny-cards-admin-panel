<template>
  <div
    :class="
      cn(
        'flex flex-col gap-1.5 p-4 rounded-2xl transition-all duration-300',
        bordered
          ? 'border border-slate-100 bg-white shadow-sm'
          : 'bg-slate-50/50',
      )
    "
  >
    <div class="flex items-center gap-2 text-slate-400 group">
      <component :is="icon" v-if="icon" class="h-4 w-4" />
      <span class="text-[11px] font-bold uppercase tracking-wider">{{
        label
      }}</span>
    </div>
    <div class="flex items-center gap-3">
      <slot>
        <span class="text-sm font-semibold text-slate-900 break-all">{{
          value || "—"
        }}</span>
      </slot>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  label: String,
  value: [String, Number],
  icon: [Object, Function],
  bordered: {
    type: Boolean,
    default: false,
  },
});

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
