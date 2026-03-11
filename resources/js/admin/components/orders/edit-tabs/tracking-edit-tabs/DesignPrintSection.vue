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
      <div class="flex items-center justify-between">
        <label class="text-sm font-medium text-slate-700"
          >Print & Add-ons <span class="text-red-500">*</span></label
        >
        <div class="flex items-center gap-2">
          <input
            v-model="newAddon"
            @keyup.enter="addAddon"
            type="text"
            placeholder="Add new addon..."
            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 w-40 transition-all font-medium"
          />
          <button
            @click="addAddon"
            type="button"
            class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 transition-all active:scale-95 shadow-sm shadow-primary/20"
          >
            Add
          </button>
        </div>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <label
          v-for="addon in allAddons"
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
      v-if="designPrint._audit && !hideAudit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(designPrint._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ designPrint._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { Clock as ClockIcon, PlusCircle as PlusCircleIcon } from "lucide-vue-next";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  hideAudit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order"]);

const designPrint = computed(() => {
  if (!props.order.tracking.design_print) {
    props.order.tracking.design_print = {};
  }
  return props.order.tracking.design_print;
});

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const d = date.toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
  const t = date.toLocaleTimeString("en-GB", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  }).toUpperCase();
  return `${d} at ${t}`;
};

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

const defaultAddons = [
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

const newAddon = ref("");
const customAddons = ref([]);

const allAddons = computed(() => {
  const selected = printAddonsList.value;
  // Get any selected options that are NOT in defaults and NOT in our tracked customAddons
  const legacyExtras = selected.filter(
    (s) => !defaultAddons.includes(s) && !customAddons.value.includes(s)
  );
  
  // Return unique set of everything
  return [...new Set([...defaultAddons, ...customAddons.value, ...legacyExtras])];
});

const addAddon = () => {
  const val = newAddon.value.trim();
  if (!val) return;

  // Prevent duplicates in the LIST of options
  if (
    allAddons.value.some((opt) => opt.toLowerCase() === val.toLowerCase()) 
  ) {
    if (!printAddonsList.value.includes(val)) {
       printAddonsList.value = [...printAddonsList.value, val];
    }
    newAddon.value = "";
    return;
  }

  // Add to custom options list so it persists
  customAddons.value.push(val);

  // Add to selected list
  printAddonsList.value = [...printAddonsList.value, val];
  newAddon.value = "";
};
</script>

