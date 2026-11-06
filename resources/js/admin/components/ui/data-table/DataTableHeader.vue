<template>
  <thead>
    <tr class="border-b border-slate-200">
      <th
        v-for="column in columns"
        :key="column.key"
        :class="[
          'px-3 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 select-none transition-colors duration-200 whitespace-nowrap',
          column.align === 'center'
            ? 'text-center'
            : column.align === 'right'
              ? 'text-right'
              : 'text-left',
          column.class,
        ]"
        :style="column.width ? { width: column.width } : {}"
      >
        <div
          :class="[
            'flex items-center gap-1.5',
            column.align === 'center'
              ? 'justify-center'
              : column.align === 'right'
                ? 'justify-end'
                : 'justify-start',
          ]"
        >
          {{ column.label }}
          <slot :name="`header-${column.key}`" :column="column"></slot>
        </div>
      </th>
    </tr>
  </thead>
</template>

<script setup>
defineProps({
  columns: {
    type: Array,
    required: true,
    // Column structure: { key: string, label: string, align?: 'left'|'center'|'right', class?: string }
  },
});
</script>

<style scoped>
th {
  font-family:
    "Inter",
    system-ui,
    -apple-system,
    sans-serif;
}
</style>
