<template>
  <Popover v-slot="{ open }" class="relative">
    <PopoverButton
      :class="[
        'w-full bg-slate-50 border border-slate-200 rounded-xl text-[11px] font-semibold focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all text-left shadow-sm min-h-[31px] flex items-center justify-between group px-3 py-1.5',
        customClass,
        open || modelValue ? 'ring-4 ring-primary/10 border-primary bg-white text-slate-900' : 'text-slate-400/50'
      ]"
    >
      <div class="flex items-center gap-2 truncate">
        <slot name="leading"></slot>
        <span class="truncate">
          {{ formattedDate || placeholder }}
        </span>
      </div>
      <CalendarIcon 
        v-if="!$slots.leading"
        class="h-3.5 w-3.5 transition-colors"
        :class="[open || modelValue ? 'text-primary' : 'text-slate-400 group-hover:text-slate-500']"
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
        class="absolute z-[var(--z-popover)] mt-3 w-72 -translate-x-1/2 left-1/2 lg:left-0 lg:translate-x-0 transform rounded-[2rem] bg-white p-4 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-slate-100"
      >
        <!-- Calendar Header -->
        <div class="flex items-center justify-between mb-4 px-1">
          <button
            @click="changeMonth(-1)"
            class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-all border border-transparent hover:border-slate-100 active:scale-95"
          >
            <ChevronLeftIcon class="h-4 w-4" />
          </button>
          
          <div class="flex flex-col items-center">
            <span class="text-sm font-black text-slate-900 tracking-tight">
              {{ currentMonthName }} {{ currentYear }}
            </span>
          </div>

          <button
            @click="changeMonth(1)"
            class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-all border border-transparent hover:border-slate-100 active:scale-95"
          >
            <ChevronRightIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- Days of Week Labels -->
        <div class="grid grid-cols-7 gap-1 mb-2">
          <div
            v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']"
            :key="day"
            class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center py-1"
          >
            {{ day }}
          </div>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-1">
          <button
            v-for="(date, index) in calendarDays"
            :key="index"
            @click="selectDate(date)"
            :disabled="!date.currentMonth"
            class="h-9 w-9 rounded-xl flex items-center justify-center text-xs font-bold transition-all relative group"
            :class="[
              !date.currentMonth ? 'text-slate-200 cursor-default' : 'hover:bg-primary/5 text-slate-700',
              isSelected(date.value) ? 'bg-primary text-white hover:bg-primary shadow-lg shadow-primary/20 scale-105' : '',
              isToday(date.value) && !isSelected(date.value) ? 'text-primary' : ''
            ]"
          >
            {{ date.day }}
            <!-- Today Dot -->
            <div 
              v-if="isToday(date.value) && !isSelected(date.value)" 
              class="absolute bottom-1 w-1 h-1 bg-primary rounded-full"
            ></div>
          </button>
        </div>

        <!-- Popover Footer -->
        <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between px-1">
          <button
            @click="clearDate"
            class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-colors"
          >
            Clear
          </button>
          <button
            @click="goToToday"
            class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary/80 transition-colors"
          >
            Today
          </button>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import {
  Calendar as CalendarIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ArrowLeftIcon,
  ChevronRight as ChevronRightIcon,
  X as XIcon,
} from "lucide-vue-next";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  placeholder: {
    type: String,
    default: "Select date",
  },
  customClass: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

const viewDate = ref(new Date());

const currentYear = computed(() => viewDate.value.getFullYear());
const currentMonth = computed(() => viewDate.value.getMonth());
const currentMonthName = computed(() => {
  return new Intl.DateTimeFormat("en-US", { month: "long" }).format(viewDate.value);
});

const formattedDate = computed(() => {
  if (!props.modelValue) return "";
  const d = new Date(props.modelValue);
  if (isNaN(d.getTime())) return props.modelValue;
  
  const day = String(d.getDate()).padStart(2, "0");
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const year = d.getFullYear();
  return `${day}-${month}-${year}`;
});

const calendarDays = computed(() => {
  const days = [];
  const year = currentYear.value;
  const month = currentMonth.value;
  
  const firstDayOfMonth = new Date(year, month, 1).getDay();
  const lastDateOfMonth = new Date(year, month + 1, 0).getDate();
  const lastDayOfPrevMonth = new Date(year, month, 0).getDate();
  
  // Previous month trailing days
  for (let i = firstDayOfMonth - 1; i >= 0; i--) {
    days.push({
      day: lastDayOfPrevMonth - i,
      currentMonth: false,
      value: new Date(year, month - 1, lastDayOfPrevMonth - i)
    });
  }
  
  // Current month days
  for (let i = 1; i <= lastDateOfMonth; i++) {
    days.push({
      day: i,
      currentMonth: true,
      value: new Date(year, month, i)
    });
  }
  
  // Next month leading days
  const remainingSlots = 42 - days.length; // 6 rows of 7 days
  for (let i = 1; i <= remainingSlots; i++) {
    days.push({
      day: i,
      currentMonth: false,
      value: new Date(year, month + 1, i)
    });
  }
  
  return days;
});

const changeMonth = (delta) => {
  viewDate.value = new Date(currentYear.value, currentMonth.value + delta, 1);
};

const selectDate = (date) => {
  if (!date.currentMonth) return;
  const year = date.value.getFullYear();
  const month = String(date.value.getMonth() + 1).padStart(2, "0");
  const day = String(date.value.getDate()).padStart(2, "0");
  emit("update:modelValue", `${year}-${month}-${day}`);
};

const clearDate = () => {
  emit("update:modelValue", "");
};

const goToToday = () => {
  const today = new Date();
  viewDate.value = today;
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  emit("update:modelValue", `${year}-${month}-${day}`);
};

const isToday = (date) => {
  const today = new Date();
  return date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear();
};

const isSelected = (date) => {
  if (!props.modelValue) return false;
  const selected = new Date(props.modelValue);
  return date.getDate() === selected.getDate() &&
    date.getMonth() === selected.getMonth() &&
    date.getFullYear() === selected.getFullYear();
};

onMounted(() => {
  if (props.modelValue) {
    viewDate.value = new Date(props.modelValue);
  }
});
</script>

<style scoped>
/* Any custom styles if needed */
</style>
