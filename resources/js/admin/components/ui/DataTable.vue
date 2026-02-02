<template>
  <div
    :class="[
      !noWrapper
        ? 'bg-white rounded-[2rem] border border-slate-200 shadow-soft-xl overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-700'
        : '',
    ]"
  >
    <div class="overflow-x-auto custom-scrollbar">
      <table class="w-full border-separate border-spacing-0 text-left">
        <DataTableHeader :columns="columns">
          <template v-for="(_, name) in $slots" #[name]="slotData">
            <slot :name="name" v-bind="slotData"></slot>
          </template>
        </DataTableHeader>

        <tbody class="divide-y divide-slate-100">
          <template v-if="loading">
            <tr v-for="i in skeletonRows" :key="i" class="animate-pulse">
              <td :colspan="columns.length" class="px-6 py-8">
                <div class="h-4 bg-slate-100 rounded w-full"></div>
              </td>
            </tr>
          </template>

          <template v-else-if="items.length === 0">
            <tr>
              <td
                :colspan="columns.length"
                class="px-6 py-12 text-center text-slate-400 italic text-sm"
              >
                {{ emptyText }}
              </td>
            </tr>
          </template>

          <tr
            v-for="(item, index) in items"
            :key="item.id || index"
            class="group hover:bg-slate-50/50 transition-colors duration-200"
          >
            <slot name="row" :item="item" :index="index">
              <td
                v-for="column in columns"
                :key="column.key"
                :class="[
                  'px-6 py-4 text-sm',
                  column.align === 'center'
                    ? 'text-center'
                    : column.align === 'right'
                      ? 'text-right'
                      : 'text-left',
                  column.cellClass,
                ]"
              >
                <slot
                  :name="`cell-${column.key}`"
                  :item="item"
                  :column="column"
                >
                  {{ item[column.key] }}
                </slot>
              </td>
            </slot>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Slot -->
    <slot name="pagination"></slot>
  </div>
</template>

<script setup>
import DataTableHeader from "./DataTableHeader.vue";

defineProps({
  columns: {
    type: Array,
    required: true,
  },
  items: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  skeletonRows: {
    type: Number,
    default: 5,
  },
  emptyText: {
    type: String,
    default: "No data found matching your criteria.",
  },
  noWrapper: {
    type: Boolean,
    default: false,
  },
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
