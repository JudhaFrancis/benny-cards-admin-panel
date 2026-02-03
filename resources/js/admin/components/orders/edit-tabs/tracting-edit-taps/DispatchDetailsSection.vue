<template>
  <div class="space-y-8">
    <div
      class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 space-y-6"
    >
      <h4 class="font-bold text-slate-900">Bus</h4>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700">Bus No</label>
          <input
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Reaching Time</label
          >
          <input
            type="time"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700">Contact No</label>
          <input
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
      </div>
    </div>

    <div
      class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 space-y-6"
    >
      <h4 class="font-bold text-slate-900">Courier</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700">Name</label>
          <input
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700">Tracking No</label>
          <input
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
      </div>
      <label class="flex items-center gap-2.5 cursor-pointer group">
        <input
          type="checkbox"
          class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
        />
        <span
          class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
          >Shared in WhatsApp Group</span
        >
      </label>
    </div>

    <div
      class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 space-y-6"
    >
      <h4 class="font-bold text-slate-900">Transport</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700">Name</label>
          <input
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700">LR Number</label>
          <input
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
      </div>
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
</script>
