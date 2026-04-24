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
          {{ displayTime || placeholder }}
        </span>
      </div>
      <ClockIcon 
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
        class="absolute z-[60] mt-3 w-56 -translate-x-1/2 left-1/2 lg:left-0 lg:translate-x-0 transform rounded-[2rem] bg-white p-4 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-slate-100"
      >
        <div class="flex flex-col gap-4">
          <div class="flex items-center justify-between px-2">
            <span class="text-xs font-black text-slate-900 uppercase tracking-widest">Select Time</span>
            <div class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">
              {{ selectedHour }}:{{ selectedMinute }}
            </div>
          </div>

          <div class="grid grid-cols-3 gap-2 h-48">
            <!-- Hours Column -->
            <div class="flex flex-col gap-1 overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-slate-200">
              <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter sticky top-0 bg-white py-1">Hour</span>
              <button
                v-for="h in hours12"
                :key="h"
                @click="setHour(h)"
                class="h-8 flex-shrink-0 rounded-lg text-xs font-bold transition-all"
                :class="[
                  selectedHour12 === h 
                    ? 'bg-primary text-white shadow-lg shadow-primary/20 scale-105' 
                    : 'text-slate-600 hover:bg-slate-50'
                ]"
              >
                {{ h }}
              </button>
            </div>

            <!-- Minutes Column -->
            <div class="flex flex-col gap-1 overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-slate-200">
              <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter sticky top-0 bg-white py-1">Min</span>
              <button
                v-for="m in minutes"
                :key="m"
                @click="setMinute(m)"
                class="h-8 flex-shrink-0 rounded-lg text-xs font-bold transition-all"
                :class="[
                  selectedMinute === m 
                    ? 'bg-primary text-white shadow-lg shadow-primary/20 scale-105' 
                    : 'text-slate-600 hover:bg-slate-50'
                ]"
              >
                {{ m }}
              </button>
            </div>

            <!-- AM/PM Column -->
            <div class="flex flex-col gap-1 pr-1">
              <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter py-1">Period</span>
              <button
                v-for="p in ['AM', 'PM']"
                :key="p"
                @click="setPeriod(p)"
                class="h-8 flex-shrink-0 rounded-lg text-xs font-bold transition-all mb-1"
                :class="[
                  selectedPeriod === p 
                    ? 'bg-primary text-white shadow-lg shadow-primary/20 scale-105' 
                    : 'text-slate-600 hover:bg-slate-50'
                ]"
              >
                {{ p }}
              </button>
            </div>
          </div>

          <!-- Popover Footer -->
          <div class="pt-4 border-t border-slate-50 flex items-center justify-between px-1">
            <button
              @click="clearTime"
              class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-colors"
            >
              Clear
            </button>
            <button
              @click="setNow"
              class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary/80 transition-colors"
            >
              Now
            </button>
          </div>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup>
import { computed } from "vue";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import { Clock as ClockIcon } from "lucide-vue-next";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  placeholder: {
    type: String,
    default: "Select time",
  },
  customClass: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

const hours12 = Array.from({ length: 12 }, (_, i) => String(i + 1).padStart(2, "0"));
const minutes = Array.from({ length: 60 }, (_, i) => String(i).padStart(2, "0"));

const selectedHour24 = computed(() => {
  if (!props.modelValue) return 0;
  return parseInt(props.modelValue.split(":")[0]) || 0;
});

const selectedHour12 = computed(() => {
  const h24 = selectedHour24.value;
  const h12 = h24 % 12 || 12;
  return String(h12).padStart(2, "0");
});

const selectedPeriod = computed(() => {
  return selectedHour24.value >= 12 ? "PM" : "AM";
});

const selectedMinute = computed(() => {
  if (!props.modelValue) return "00";
  return props.modelValue.split(":")[1] || "00";
});

const displayTime = computed(() => {
  if (!props.modelValue) return "";
  const [h, m] = props.modelValue.split(":");
  if (!h || !m) return props.modelValue;
  
  const hour = parseInt(h);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const hour12 = hour % 12 || 12;
  return `${String(hour12).padStart(2, '0')}:${m} ${ampm}`;
});

const setHour = (h12) => {
  let h24 = parseInt(h12);
  const period = selectedPeriod.value;
  
  if (period === "PM" && h24 < 12) h24 += 12;
  if (period === "AM" && h24 === 12) h24 = 0;
  
  const m = selectedMinute.value;
  emit("update:modelValue", `${String(h24).padStart(2, "0")}:${m}`);
};

const setMinute = (m) => {
  const h24 = String(selectedHour24.value).padStart(2, "0");
  emit("update:modelValue", `${h24}:${m}`);
};

const setPeriod = (p) => {
  let h24 = selectedHour24.value;
  if (p === "PM" && h24 < 12) h24 += 12;
  if (p === "AM" && h24 >= 12) h24 -= 12;
  
  const m = selectedMinute.value;
  emit("update:modelValue", `${String(h24).padStart(2, "0")}:${m}`);
};

const setNow = () => {
  const now = new Date();
  const h = String(now.getHours()).padStart(2, "0");
  const m = String(now.getMinutes()).padStart(2, "0");
  emit("update:modelValue", `${h}:${m}`);
};

const clearTime = () => {
  emit("update:modelValue", "");
};
</script>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-thumb-slate-200::-webkit-scrollbar-thumb {
  background-color: #e2e8f0;
  border-radius: 2px;
}
</style>
