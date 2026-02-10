<template>
  <div class="space-y-8">
    <p class="text-sm text-slate-400 italic">
      Please ✔ whichever given to print
    </p>

    <div class="space-y-4">
      <label class="text-sm font-medium text-slate-700"
        >Design Outputs <span class="text-red-500">*</span></label
      >
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <label
          v-for="output in designOutputs"
          :key="output"
          class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <input
            type="checkbox"
            :value="output"
            v-model="designOutputsList"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span class="text-xs font-bold text-slate-700">{{ output }}</span>
        </label>
      </div>
    </div>

    <div class="space-y-4">
      <label class="text-sm font-medium text-slate-700"
        >Print & Add-ons <span class="text-red-500">*</span></label
      >
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <label
          v-for="addon in printAddons"
          :key="addon"
          class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <input
            type="checkbox"
            :value="addon"
            v-model="printAddonsList"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span class="text-xs font-bold text-slate-700">{{ addon }}</span>
        </label>
      </div>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="designPrint._audit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span>
        Last updated by
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          designPrint._audit.updated_by
        }}</span>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          new Date(designPrint._audit.updated_at).toLocaleDateString("en-GB", {
            day: "numeric",
            month: "short",
            year: "numeric",
          })
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:order"]);

const designPrint = computed(() => {
  if (!props.order.tracking.design_print) {
    props.order.tracking.design_print = {};
  }
  return props.order.tracking.design_print;
});

const designOutputsList = computed({
  get: () =>
    designPrint.value.design_outputs
      ? designPrint.value.design_outputs.split(",")
      : [],
  set: (val) => {
    designPrint.value.design_outputs = val.join(",");
  },
});

const printAddonsList = computed({
  get: () =>
    designPrint.value.print_addons
      ? designPrint.value.print_addons.split(",")
      : [],
  set: (val) => {
    designPrint.value.print_addons = val.join(",");
  },
});

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
