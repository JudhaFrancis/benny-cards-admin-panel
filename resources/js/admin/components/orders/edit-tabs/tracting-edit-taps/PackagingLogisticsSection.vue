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
          >Crafting Done</span
        >
      </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Crafted By</label>
        <input
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="Enter name"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Names</label>
        <input
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="Names on cards"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Date</label>
        <input
          type="date"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Qty of Cards</label>
        <input
          type="number"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        />
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700"
        >Envelope / Ribbon / Tag / Sticker</label
      >
      <textarea
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Details..."
      ></textarea>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Issues in Card</label>
      <textarea
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Describe any issues..."
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
</script>
