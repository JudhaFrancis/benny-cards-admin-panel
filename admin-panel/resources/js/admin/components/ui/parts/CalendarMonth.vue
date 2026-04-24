<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-4 px-1">
      <button
        @click="$emit('change-month', -1)"
        class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-all border border-transparent hover:border-slate-100 active:scale-95"
      >
        <ChevronLeftIcon class="h-4 w-4" />
      </button>
      
      <span class="text-sm font-black text-slate-900 tracking-tight uppercase">
        {{ currentMonthName }} {{ currentYear }}
      </span>

      <button
        @click="$emit('change-month', 1)"
        class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition-all border border-transparent hover:border-slate-100 active:scale-95"
      >
        <ChevronRightIcon class="h-4 w-4" />
      </button>
    </div>

    <!-- Days Labels -->
    <div class="grid grid-cols-7 gap-1 mb-2">
      <div
        v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']"
        :key="day"
        class="text-[10px] font-black text-slate-300 uppercase tracking-widest text-center py-2"
      >
        {{ day }}
      </div>
    </div>

    <!-- Days Grid -->
    <div class="grid grid-cols-7 gap-y-1">
      <div
        v-for="(date, index) in days"
        :key="index"
        @click="selectDate(date)"
        class="h-10 flex items-center justify-center relative select-none"
        :class="[
          date.currentMonth ? 'cursor-pointer' : 'opacity-0 pointer-events-none',
        ]"
      >
        <!-- Range Background Highlight -->
        <div 
          v-if="isInRange(date.value)"
          class="absolute inset-0 bg-primary/10 transition-colors"
          :class="[
            isRangeStart(date.value) ? 'rounded-l-xl ml-1' : '',
            isRangeEnd(date.value) ? 'rounded-r-xl mr-1' : '',
            !isRangeStart(date.value) && !isRangeEnd(date.value) ? '' : ''
          ]"
        ></div>

        <!-- Date Circle -->
        <div
          class="z-10 w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold transition-all relative"
          :class="[
            isRangeStart(date.value) || isRangeEnd(date.value) 
              ? 'bg-primary text-white shadow-lg shadow-primary/20 scale-105' 
              : 'text-slate-700 hover:bg-primary/5',
            isToday(date.value) && !(isRangeStart(date.value) || isRangeEnd(date.value)) ? 'text-primary' : ''
          ]"
        >
          {{ date.day }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { ChevronLeft as ChevronLeftIcon, ChevronRight as ChevronRightIcon } from "lucide-vue-next";

const props = defineProps({
  viewDate: {
    type: Date,
    required: true,
  },
  startDate: Date,
  endDate: Date,
});

const emit = defineEmits(["change-month", "select"]);

const currentYear = computed(() => props.viewDate.getFullYear());
const currentMonth = computed(() => props.viewDate.getMonth());
const currentMonthName = computed(() => {
  return new Intl.DateTimeFormat("en-US", { month: "long" }).format(props.viewDate);
});

const days = computed(() => {
  const result = [];
  const year = currentYear.value;
  const month = currentMonth.value;
  
  const firstDayOfMonth = new Date(year, month, 1).getDay();
  const lastDateOfMonth = new Date(year, month + 1, 0).getDate();
  
  // Empty slots for start
  for (let i = 0; i < firstDayOfMonth; i++) {
    result.push({ currentMonth: false });
  }
  
  // Actual days
  for (let i = 1; i <= lastDateOfMonth; i++) {
    result.push({
      day: i,
      currentMonth: true,
      value: new Date(year, month, i)
    });
  }
  
  return result;
});

const selectDate = (date) => {
  if (date.currentMonth) {
    emit("select", date.value);
  }
};

const isToday = (date) => {
  const today = new Date();
  return date && date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear();
};

const isRangeStart = (date) => {
  return props.startDate && date && 
    date.getTime() === props.startDate.getTime();
};

const isRangeEnd = (date) => {
  return props.endDate && date && 
    date.getTime() === props.endDate.getTime();
};

const isInRange = (date) => {
  if (!props.startDate || !props.endDate || !date) return false;
  return date >= props.startDate && date <= props.endDate;
};
</script>
