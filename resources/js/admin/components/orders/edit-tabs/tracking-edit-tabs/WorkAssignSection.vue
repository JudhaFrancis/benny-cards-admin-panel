<template>
  <div class="space-y-6">
    <div class="flex flex-wrap gap-4">
      <label class="flex items-center gap-2.5 cursor-pointer group">
        <input
          type="checkbox"
          class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
        />
        <span
          class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
          >Content Received</span
        >
      </label>
      <label class="flex items-center gap-2.5 cursor-pointer group">
        <input
          type="checkbox"
          class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
        />
        <span
          class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
          >Clear Content</span
        >
      </label>
      <label class="flex items-center gap-2.5 cursor-pointer group">
        <input
          type="checkbox"
          class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
        />
        <span
          class="text-sm font-bold text-slate-700 group-hover:text-primary transition-colors"
          >Tag</span
        >
      </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Assigned To</label>
        <input
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="Designer name"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Deadline</label>
        <input
          type="date"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Content By</label>
        <input
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="Enter name"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Completed By</label>
        <input
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="Enter name"
        />
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
