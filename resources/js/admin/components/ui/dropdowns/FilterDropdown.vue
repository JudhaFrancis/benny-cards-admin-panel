<template>
  <Popover v-slot="{ open, close }" class="relative">
    <PopoverButton
      :class="
        cn(
          'group flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 active:scale-95 border border-slate-200 bg-white hover:bg-slate-50',
          open || isActive
            ? 'border-primary text-primary bg-primary/5 ring-4 ring-primary/5'
            : 'text-slate-600',
        )
      "
    >
      <div class="relative">
        <SlidersHorizontalIcon class="h-4 w-4" />
        <span
          v-if="isActive"
          class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-primary ring-2 ring-white animate-in zoom-in duration-300"
        ></span>
      </div>
      <span>Filters</span>
      <ChevronDownIcon
        :class="
          cn(
            'h-4 w-4 transition-transform duration-300 opacity-50',
            open && 'rotate-180',
          )
        "
      />
    </PopoverButton>

    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-4 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-4 scale-95"
    >
      <PopoverPanel
        class="absolute left-0 z-50 mt-3 w-[320px] transform bg-white rounded-[2rem] shadow-2xl border border-slate-100 flex flex-col overflow-hidden origin-top-left"
      >
        <!-- Header -->
        <div
          class="px-6 py-3 border-b border-slate-100 flex items-center justify-between bg-white z-10"
        >
          <span
            class="text-xs font-bold text-slate-400 uppercase tracking-wider"
            >Filters</span
          >
          <button
            @click="close()"
            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors -mr-2"
          >
            <XIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar">
          <slot />
        </div>

        <!-- Sticky Footer -->
        <div
          class="px-6 py-4 bg-slate-50/80 backdrop-blur-sm border-t border-slate-100 flex items-center justify-between gap-3"
        >
          <button
            type="button"
            @click="$emit('reset')"
            class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors px-2"
          >
            Clear All
          </button>
          <button
            type="button"
            @click="
              $emit('apply');
              close();
            "
            class="flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-[11px] font-bold uppercase tracking-wider transition-all shadow-lg shadow-slate-200 active:scale-95"
          >
            Apply Filters
          </button>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup>
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import {
  SlidersHorizontal as SlidersHorizontalIcon,
  ChevronDown as ChevronDownIcon,
  X as XIcon,
} from "lucide-vue-next";

const props = defineProps({
  isActive: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["apply", "reset"]);

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
