<template>
  <div :class="[
    !noWrapper
      ? 'bg-white rounded-[2rem] border border-slate-200 shadow-soft-xl overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-700 flex flex-col min-h-[400px]'
      : 'flex flex-col min-h-[400px]',
  ]">
    <!-- Header with Slots -->
    <div v-if="$slots['header-left'] || $slots['header-right']"
      class="px-8 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between shrink-0">
      <div class="flex items-center gap-2">
        <slot name="header-left"></slot>
      </div>
      <div class="flex items-center gap-3">
        <slot name="header-right"></slot>
      </div>
    </div>

    <div class="overflow-x-auto custom-scrollbar flex-1">
      <table class="min-w-full border-separate border-spacing-0 text-left">
        <thead>
          <tr class="border-b border-slate-200">
            <th v-for="column in columns" :key="column.key" :class="[
              'px-4 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 select-none transition-colors duration-200 whitespace-nowrap bg-slate-50/50 sticky top-0 z-20',
              column.align === 'center' ? 'text-center' : column.align === 'right' ? 'text-right' : 'text-left',
              column.class,
            ]" :style="column.width ? { width: column.width } : {}">
              {{ column.label }}
            </th>
          </tr>
          <!-- Filter Row -->
          <tr class="bg-slate-50/50 border-b border-slate-100 sticky top-[49px] z-10">
            <th v-for="column in columns" :key="'filter-' + column.key" class="px-3 py-2 border-slate-100">
              <div v-if="shouldShowFilter(column)" class="relative group min-w-[120px]">
                <DatePicker v-if="column.type === 'date'" v-model="filters[column.filterKey || column.key]" placeholder="Select Date" />
                <div v-else-if="column.type === 'select'" class="relative">
                  <Listbox v-model="filters[column.filterKey || column.key]" :multiple="column.multiple">
                    <div class="relative">
                      <ListboxButton :class="[
                        'w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-[11px] font-semibold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all pr-8 text-left shadow-sm min-h-[31px] flex items-center',
                        isFilterEmpty(column) ? 'text-slate-400/50' : 'text-slate-700'
                      ]">
                        <span class="block truncate">
                          {{ getSelectedLabel(column) }}
                        </span>
                        <span v-if="isFilterEmpty(column)" class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                          <ChevronDownIcon class="h-3.5 w-3.5 text-slate-400" aria-hidden="true" />
                        </span>
                      </ListboxButton>

                      <transition leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100"
                        leave-to-class="opacity-0">
                        <ListboxOptions
                          class="absolute z-50 mt-1 max-h-60 min-w-full w-max left-0 overflow-auto rounded-xl bg-white py-1 text-[11px] shadow-2xl ring-1 ring-black/5 focus:outline-none scrollbar-hide">
                          <ListboxOption v-if="!column.hideAllOption" v-slot="{ active, selected }" :value="column.multiple ? '__all__' : ''" as="template">
                            <li :class="[
                              active || selected ? 'bg-primary/5 text-primary' : 'text-slate-600',
                              'relative cursor-pointer select-none py-2 pl-4 pr-4 transition-colors font-semibold',
                            ]">
                              <span :class="[selected ? 'font-bold' : '', 'block truncate']">
                                {{ column.placeholder || (column.multiple ? "All Status" : "All Status") }}
                              </span>
                            </li>
                          </ListboxOption>

                          <ListboxOption v-slot="{ active, selected }" v-for="opt in column.options" :key="opt.value"
                            :value="opt.value" as="template">
                            <li :class="[
                              active || selected ? 'bg-primary/5 text-primary' : 'text-slate-600',
                              'relative cursor-pointer select-none py-2 pl-4 pr-4 transition-colors font-semibold',
                            ]">
                              <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-1 text-primary">
                                <CheckIcon class="h-3 w-3" aria-hidden="true" />
                              </span>
                              <span :class="[selected ? 'font-bold pl-3' : '', 'block truncate']">{{ opt.label }}</span>
                            </li>
                          </ListboxOption>
                        </ListboxOptions>
                      </transition>
                    </div>
                  </Listbox>
                </div>
                <input v-else type="text" v-model="filters[column.filterKey || column.key]" :placeholder="`Filter ${column.label}...`"
                  class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-[11px] font-semibold text-slate-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-slate-400/50 pr-8 shadow-sm" />
                <button v-if="!isFilterEmpty(column)" @click="resetColumnFilter(column)"
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-0.5 rounded-md hover:bg-slate-100">
                  <XIcon class="h-3 w-3" />
                </button>
              </div>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
          <template v-if="loading">
            <tr v-for="i in skeletonRows" :key="i" class="animate-pulse">
              <td :colspan="columns.length" class="px-4 py-6">
                <div class="h-3 bg-slate-100 rounded w-full"></div>
              </td>
            </tr>
          </template>

          <template v-else-if="filteredItems.length === 0">
            <tr>
              <td :colspan="columns.length" class="px-4 py-20 text-center bg-white">
                <div class="flex flex-col items-center justify-center space-y-3">
                  <div class="p-4 rounded-full bg-slate-50 border border-slate-100">
                    <SearchIcon class="h-8 w-8 text-slate-300" />
                  </div>
                  <p class="text-sm font-bold text-slate-900 uppercase tracking-tight">{{ emptyText }}</p>
                  <button v-if="hasActiveFilters" @click="resetFilters"
                    class="text-xs font-bold text-primary hover:text-primary/80 flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary/5 border border-primary/10 transition-all hover:scale-105 active:scale-95">
                    Clear all filters
                  </button>
                </div>
              </td>
            </tr>
          </template>

          <template v-else>
            <tr v-for="(item, index) in filteredItems" :key="item.id || index"
              @click="$emit('row-click', item)"
              class="group hover:bg-slate-50/50 transition-colors duration-200 cursor-pointer">
              <slot name="row" :item="item" :index="index">
                <td v-for="column in columns" :key="column.key" :class="[
                  'px-4 py-4 text-[13px] whitespace-nowrap text-slate-600 font-medium',
                  column.align === 'center' ? 'text-center' : column.align === 'right' ? 'text-right' : 'text-left',
                  column.cellClass,
                ]" :style="column.width ? { width: column.width } : {}">
                  <slot :name="`cell-${column.key}`" :item="item" :column="column" :index="index">
                    <template v-if="column.key.toLowerCase() === 'sn'">
                      {{ from + index }}
                    </template>
                    <template v-else>
                      {{ resolveValue(item, column.key) }}
                    </template>
                  </slot>
                </td>
              </slot>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination Slot -->
    <div class="shrink-0">
      <slot name="pagination"></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import {
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from "@headlessui/vue";
import DatePicker from "../pickers/DatePicker.vue";
import { X as XIcon, Search as SearchIcon, ChevronDown as ChevronDownIcon, Check as CheckIcon } from "lucide-vue-next";

const props = defineProps({
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
  from: {
    type: Number,
    default: 1,
  },
  manualFilters: {
    type: Boolean,
    default: false,
  },
  emptyText: {
    type: String,
    default: "No matching results found.",
  },
  noWrapper: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["filter-change", "row-click"]);

const filters = ref({});

const isFilterEmpty = (column) => {
  const value = filters.value[column.filterKey || column.key];
  if (Array.isArray(value)) return value.length === 0;
  return value === "" || value === undefined || value === null;
};

const getSelectedLabel = (column) => {
  const value = filters.value[column.filterKey || column.key];
  if (isFilterEmpty(column)) return column.placeholder || "All Status";

  if (Array.isArray(value)) {
    // Check if special "All" value is selected
    if (value.includes("__all__")) return column.placeholder || "All Status";

    // If it matches default filter, show placeholder
    if (column.defaultFilter && Array.isArray(column.defaultFilter)) {
      const sortedVal = [...value].sort();
      const sortedDef = [...column.defaultFilter].sort();
      if (JSON.stringify(sortedVal) === JSON.stringify(sortedDef)) {
        return column.placeholder || "All Status";
      }
    }

    if (value.length > 0) {
      const labels = value.map(v => {
        const option = column.options?.find(opt => opt.value === v);
        return option ? option.label : v;
      });
      return labels.join(", ");
    }
    return `${value.length} Selected`;
  }

  const option = column.options?.find(opt => opt.value === value);
  return option ? option.label : value;
};

// Initialize filters immediately
props.columns.forEach(col => {
  const key = col.filterKey || col.key;
  if (col.defaultFilter !== undefined) {
    filters.value[key] = JSON.parse(JSON.stringify(col.defaultFilter));
  } else if (col.multiple) {
    filters.value[key] = [];
  } else {
    filters.value[key] = "";
  }
});

let debounceTimeout = null;

const hasActiveFilters = computed(() => {
  return props.columns.some(col => !isFilterEmpty(col));
});

const resetFilters = () => {
  const newFilters = {};
  props.columns.forEach(col => {
    if (col.multiple) newFilters[col.filterKey || col.key] = [];
    else newFilters[col.filterKey || col.key] = "";
  });
  filters.value = newFilters;
};

// Watch filters and emit for server-side search
watch(filters, (newFilters) => {
  if (debounceTimeout) clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    const emittedFilters = JSON.parse(JSON.stringify(newFilters));

    // Handle the special "__all__" value for multiple select
    Object.keys(filters.value).forEach(key => {
      const col = props.columns.find(c => (c.filterKey || c.key) === key);
      if (col?.multiple && Array.isArray(filters.value[key])) {
        if (filters.value[key].includes('__all__')) {
          filters.value[key] = []; // Clear local state too
          emittedFilters[key] = []; // Clear for backend
        }
      }
    });

    emit("filter-change", emittedFilters);
  }, 500);
}, { deep: true, immediate: true });

const resetColumnFilter = (column) => {
  const key = column.filterKey || column.key;
  if (column.multiple) filters.value[key] = [];
  else filters.value[key] = "";
};

const shouldShowFilter = (column) => {
  const skip = ['sn', 'actions', 'action'];
  if (skip.includes(column.key.toLowerCase())) return false;
  if (column.filter === false) return false;
  return true;
};

const resolveValue = (obj, path) => {
  if (!path) return '';
  return path.split('.').reduce((acc, part) => acc && acc[part], obj);
};



const filteredItems = computed(() => {
  // Always return props.items if manualFilters is enabled (server-side filtering)
  if (props.manualFilters) return props.items;

  if (!hasActiveFilters.value) return props.items;

  return props.items.filter((item) => {
    return props.columns.every((column) => {
      const key = column.filterKey || column.key;
      const filterValue = filters.value[key];
      if (filterValue === undefined || filterValue === "" || filterValue === null) return true;

      const val = resolveValue(item, column.key);
      if (val === undefined || val === null) return false;

      return String(val).toLowerCase().includes(String(filterValue).toLowerCase());
    });
  });
});

// Expose filter state to parent if needed
defineExpose({ filters, resetFilters, filteredItems });
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
  appearance: textfield;
}

input[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  padding: 2px;
  filter: invert(0.5);
}
</style>
