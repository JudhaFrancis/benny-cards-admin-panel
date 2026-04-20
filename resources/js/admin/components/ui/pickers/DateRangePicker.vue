<template>
  <Popover as="div" v-slot="{ open, close }" class="relative w-full">
    <PopoverButton
      :class="[
        'w-full bg-slate-50 border border-slate-200 rounded-xl text-[11px] font-semibold text-slate-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-left shadow-sm min-h-[31px] flex items-center justify-between group px-3 py-1.5',
        customClass,
        open || hasSelection ? 'ring-4 ring-primary/10 border-primary bg-white text-slate-900' : ''
      ]"
    >
      <div class="flex items-center gap-2 truncate text-[10px] sm:text-[11px]">
        <CalendarIcon 
          class="h-3.5 w-3.5 transition-colors shrink-0"
          :class="[open || hasSelection ? 'text-primary' : 'text-slate-400 group-hover:text-slate-500']"
        />
        <span class="truncate">
          {{ displayValue || placeholder }}
        </span>
      </div>
      <ChevronDownIcon 
        class="h-3 w-3 text-slate-400 group-hover:text-slate-500 transition-transform duration-200"
        :class="{ 'rotate-180': open }"
      />
    </PopoverButton>

    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-y-1 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="translate-y-1 opacity-0"
    >
      <PopoverPanel
        class="absolute z-[100] top-full mt-2 lg:right-0 transform rounded-[2rem] bg-white p-6 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-slate-100 w-auto min-w-[320px] lg:min-w-[640px]"
      >
        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Calendar 1 -->
          <div class="flex-1 min-w-[280px]">
            <CalendarMonth 
              :view-date="viewDate1"
              :start-date="startDate"
              :end-date="endDate"
              @change-month="changeMonth1"
              @select="handleSelect"
            />
          </div>

          <!-- Calendar 2 -->
          <div class="flex-1 min-w-[280px] hidden lg:block border-l border-slate-50 pl-8">
            <CalendarMonth 
              :view-date="viewDate2"
              :start-date="startDate"
              :end-date="endDate"
              @change-month="changeMonth2"
              @select="handleSelect"
            />
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 pt-4 border-t border-slate-50 flex items-center justify-between">
          <button
            @click="clearRange"
            class="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all"
          >
            Clear Selection
          </button>
          <div class="flex items-center gap-3">
             <button
              @click="close()"
              class="px-6 py-2 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95"
            >
              Apply Range
            </button>
          </div>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import {
  Calendar as CalendarIcon,
  ChevronDown as ChevronDownIcon,
} from "lucide-vue-next";
import CalendarMonth from "../parts/CalendarMonth.vue";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  placeholder: {
    type: String,
    default: "Select Range",
  },
  customClass: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

const startDate = ref(null);
const endDate = ref(null);

const viewDate1 = ref(new Date());
const viewDate2 = computed(() => {
  const d = new Date(viewDate1.value);
  d.setMonth(d.getMonth() + 1);
  return d;
});

const hasSelection = computed(() => !!startDate.value);

const displayValue = computed(() => {
  if (!startDate.value) return "";
  const format = (d) => {
    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
  };
  
  if (!endDate.value) return `${format(startDate.value)} to ...`;
  return `${format(startDate.value)} - ${format(endDate.value)}`;
});

const handleSelect = (date) => {
  if (!startDate.value || (startDate.value && endDate.value)) {
    startDate.value = date;
    endDate.value = null;
  } else if (date < startDate.value) {
    endDate.value = startDate.value;
    startDate.value = date;
  } else {
    endDate.value = date;
    updateModel();
  }
};

const updateModel = () => {
  if (startDate.value && endDate.value) {
    const format = (d) => {
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, "0");
      const day = String(d.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    };
    emit("update:modelValue", `${format(startDate.value)} to ${format(endDate.value)}`);
  }
};

const clearRange = () => {
  startDate.value = null;
  endDate.value = null;
  emit("update:modelValue", "");
};

const changeMonth1 = (delta) => {
  const d = new Date(viewDate1.value);
  d.setMonth(d.getMonth() + delta);
  viewDate1.value = d;
};

const changeMonth2 = (delta) => {
  changeMonth1(delta); // Keep them in sync (consecutive months)
};

onMounted(() => {
  if (props.modelValue && props.modelValue.includes(" to ")) {
    const [s, e] = props.modelValue.split(" to ");
    startDate.value = new Date(s);
    endDate.value = new Date(e);
    viewDate1.value = new Date(s);
  }
});
</script>
