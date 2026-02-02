<template>
  <div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <label
        v-for="mode in dispatchModes"
        :key="mode"
        class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
      >
        <input
          type="checkbox"
          class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
        />
        <span class="text-xs font-bold text-slate-700">{{ mode }}</span>
      </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label
          class="text-xs font-bold text-slate-400 uppercase tracking-widest"
          >Dispatch Details with Date</label
        >
        <input
          type="date"
          class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        />
      </div>
      <div class="space-y-2">
        <label
          class="text-xs font-bold text-slate-400 uppercase tracking-widest"
          >Dispatch Expense</label
        >
        <input
          type="number"
          class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
          placeholder="0.00"
        />
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-xs font-bold text-slate-400 uppercase tracking-widest"
        >Signature & Name</label
      >
      <input
        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-900 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
        placeholder="Enter name"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order"]);

const dispatchModes = ["Shop Pickup", "Bus", "Transport", "Courier"];
</script>
