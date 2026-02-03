<template>
  <div class="space-y-8">
    <p class="text-sm text-slate-400 italic">
      Please ✔ whichever given to print
    </p>

    <div class="space-y-4">
      <label class="text-sm font-medium text-slate-700">Design Outputs</label>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <label
          v-for="output in designOutputs"
          :key="output"
          class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <input
            type="checkbox"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span class="text-xs font-bold text-slate-700">{{ output }}</span>
        </label>
      </div>
    </div>

    <div class="space-y-4">
      <label class="text-sm font-medium text-slate-700">Print & Add-ons</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <label
          v-for="addon in printAddons"
          :key="addon"
          class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <input
            type="checkbox"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span class="text-xs font-bold text-slate-700">{{ addon }}</span>
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

const designOutputs = [
  "Invitation in Draft",
  "Buttersheet / Master",
  "Gift Frame",
  "PDF",
];

const printAddons = [
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
  "Old Die",
  "New Die",
  "Dry Flower",
  "Ready Seal",
  "Special Paper",
  "Custom Seal",
];
</script>
