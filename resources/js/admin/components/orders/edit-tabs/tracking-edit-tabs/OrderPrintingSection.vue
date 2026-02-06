<template>
  <div class="space-y-8">
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Assigned Date</label>
      <input
        type="date"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
      />
    </div>

    <div
      class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 space-y-6"
    >
      <h4 class="font-bold text-slate-900">Readymade Card</h4>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <label class="flex items-center gap-2.5 cursor-pointer group">
          <input
            type="checkbox"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span
            class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
            >Ordered</span
          >
        </label>
        <label class="flex items-center gap-2.5 cursor-pointer group">
          <input
            type="checkbox"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span
            class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
            >Card Received</span
          >
        </label>
        <label class="flex items-center gap-2.5 cursor-pointer group">
          <input
            type="checkbox"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span
            class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
            >Sent to Print</span
          >
        </label>
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Follow Up</label>
        <div class="flex flex-wrap gap-2">
          <label
            v-for="day in days"
            :key="day"
            class="flex items-center gap-1.5 cursor-pointer"
          >
            <input
              type="checkbox"
              class="w-3.5 h-3.5 rounded-md border-slate-300 text-primary focus:ring-primary/20"
            />
            <span class="text-xs font-bold text-slate-600">{{ day }}</span>
          </label>
        </div>
      </div>
    </div>

    <div
      class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 space-y-6"
    >
      <h4 class="font-bold text-slate-900">Customize Card</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Sent to Print Date</label
          >
          <input
            type="date"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Delivery Date</label
          >
          <input
            type="date"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Follow Up</label>
        <div class="flex flex-wrap gap-2">
          <label
            v-for="day in days"
            :key="day"
            class="flex items-center gap-1.5 cursor-pointer"
          >
            <input
              type="checkbox"
              class="w-3.5 h-3.5 rounded-md border-slate-300 text-primary focus:ring-primary/20"
            />
            <span class="text-xs font-bold text-slate-600">{{ day }}</span>
          </label>
        </div>
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700"
        >Any Printing Issues</label
      >
      <textarea
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Describe any issues..."
      ></textarea>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Delay Reason</label>
      <textarea
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Reason for delay..."
      ></textarea>
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

const days = ["Day 1", "Day 2", "Day 3", "Day 4", "Day 5", "Day 6", "Day 7"];
</script>
