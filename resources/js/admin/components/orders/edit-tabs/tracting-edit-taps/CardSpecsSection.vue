<template>
  <div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <label
        class="flex items-center gap-3 p-4 rounded-2xl border border-slate-100 bg-slate-50 cursor-pointer hover:border-primary/20 transition-all"
      >
        <input
          type="checkbox"
          class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
        />
        <span class="text-sm font-bold text-slate-700">Customize Card</span>
      </label>
      <label
        class="flex items-center gap-3 p-4 rounded-2xl border border-slate-100 bg-slate-50 cursor-pointer hover:border-primary/20 transition-all"
      >
        <input
          type="checkbox"
          class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
        />
        <span class="text-sm font-bold text-slate-700">Ready Made Card</span>
      </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Card Size</label>
        <input
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="e.g., 5x7 inches"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Quantity</label>
        <input
          type="number"
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="100"
        />
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700">Specifications</label>
      <textarea
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Enter specifications..."
      ></textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Inner GSM</label>
        <input
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="300"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Envelope GSM</label>
        <input
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          placeholder="120"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Card Lamination</label
        >
        <select
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        >
          <option value="">Select</option>
          <option value="matt">Matt</option>
          <option value="glossy">Glossy</option>
        </select>
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Envelope Lamination</label
        >
        <select
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
        >
          <option value="">Select</option>
          <option value="matt">Matt</option>
          <option value="glossy">Glossy</option>
        </select>
      </div>
    </div>

    <div class="space-y-4">
      <label class="text-sm font-medium text-slate-700">Options</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <label
          v-for="option in cardOptions"
          :key="option"
          class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <input
            type="checkbox"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span class="text-xs font-bold text-slate-700">{{ option }}</span>
        </label>
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

const cardOptions = [
  "Sticker",
  "Band",
  "Satin Ribbon",
  "Rope",
  "Corner Cutting",
  "Envelope",
  "Insert Leaf",
  "Buttersheet",
  "Tag",
  "Org. Ribbon",
  "Foiling",
  "Screen Printing",
  "UV",
  "SC Offset",
  "New Die",
  "Dry Flower / Fresh",
  "Ready Seal",
  "Special Paper",
  "Custom Seal",
];
</script>
