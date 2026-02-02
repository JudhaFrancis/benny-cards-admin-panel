<template>
  <router-link
    v-if="item.url"
    :to="item.url"
    :class="
      cn(
        'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-300 group relative mx-2',
        isActive
          ? 'bg-white text-primary shadow-lg shadow-black/10'
          : 'text-white/70 hover:bg-white/15 hover:text-white',
        isSubItem ? 'py-1.5 px-3 text-[13px] last:mb-2' : 'mb-1 text-[14px]',
        isCollapsed ? 'justify-center px-0 mx-auto w-12 h-12' : '',
      )
    "
    :title="isCollapsed ? item.title : ''"
  >
    <component
      v-if="item.icon"
      :is="item.icon"
      :class="
        cn(
          'h-5 w-5 shrink-0 transition-all duration-300',
          isActive
            ? 'text-primary scale-110 mt-[-1px]'
            : 'text-white/60 group-hover:text-white group-hover:scale-110',
        )
      "
    />
    <span
      v-if="!isCollapsed"
      :class="
        cn(
          'font-medium tracking-tight truncate transition-all',
          isActive
            ? 'opacity-100 font-bold'
            : 'opacity-80 group-hover:opacity-100',
        )
      "
    >
      {{ item.title }}
    </span>

    <!-- Active Indicator (Expanded) -->
    <div
      v-if="isActive && !isSubItem && !isCollapsed"
      class="absolute left-[-12px] top-1/2 -translate-y-1/2 w-1.5 h-6 rounded-r-full bg-white shadow-[0_0_8px_rgba(255,255,255,0.4)]"
    />
  </router-link>

  <div
    v-else
    :class="
      cn(
        'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group cursor-default text-slate-400',
        isSubItem ? 'py-2 px-3 text-xs' : 'text-sm',
        isCollapsed ? 'justify-center px-0 mx-auto w-11 h-11' : '',
      )
    "
  >
    <component v-if="item.icon" :is="item.icon" class="h-5 w-5 shrink-0" />
    <span
      v-if="!isCollapsed"
      class="font-medium tracking-wide truncate opacity-60"
      >{{ item.title }}</span
    >
  </div>
</template>

<script setup>
function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

defineProps({
  item: {
    type: Object,
    required: true,
  },
  isCollapsed: {
    type: Boolean,
    default: false,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
  isSubItem: {
    type: Boolean,
    default: false,
  },
});
</script>
