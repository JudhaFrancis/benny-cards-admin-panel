<template>
  <div class="space-y-8">
    <!-- Card Type Toggle -->
    <div class="space-y-4">
      <label
        class="text-sm font-semibold text-slate-700 flex items-center gap-2"
      >
        <CreditCardIcon class="h-4 w-4 text-primary" />
        Product Type <span class="text-red-500">*</span>
      </label>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label
          class="relative flex items-center gap-4 p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden"
          :class="[
            cardSpecs.type === 'customize'
              ? 'bg-primary/5 border-primary shadow-md'
              : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
          ]"
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
            :class="
              cardSpecs.type === 'customize'
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <BrushIcon class="h-5 w-5" />
          </div>
          <div class="flex-1">
            <span
              class="block text-sm font-bold"
              :class="
                cardSpecs.type === 'customize'
                  ? 'text-primary'
                  : 'text-slate-700'
              "
              >Customize Card</span
            >
            <span class="text-xs text-slate-400"
              >Custom design and printing</span
            >
          </div>
          <div
            v-if="cardSpecs.type === 'customize'"
            class="absolute top-2 right-2"
          >
            <div class="w-2 h-2 rounded-full bg-primary animate-ping"></div>
          </div>
          <input
            type="radio"
            name="card_type"
            value="customize"
            v-model="cardSpecs.type"
            class="absolute inset-0 opacity-0 cursor-pointer"
          />
        </label>

        <label
          class="relative flex items-center gap-4 p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden"
          :class="[
            cardSpecs.type === 'ready_made'
              ? 'bg-emerald-50 border-emerald-500 shadow-md'
              : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
          ]"
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
            :class="
              cardSpecs.type === 'ready_made'
                ? 'bg-emerald-500 text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <BoxIcon class="h-5 w-5" />
          </div>
          <div class="flex-1">
            <span
              class="block text-sm font-bold"
              :class="
                cardSpecs.type === 'ready_made'
                  ? 'text-emerald-700'
                  : 'text-slate-700'
              "
              >Ready Made Card</span
            >
            <span class="text-xs text-slate-400">Pre-designed stock items</span>
          </div>
          <div
            v-if="cardSpecs.type === 'ready_made'"
            class="absolute top-2 right-2"
          >
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></div>
          </div>
          <input
            type="radio"
            name="card_type"
            value="ready_made"
            v-model="cardSpecs.type"
            class="absolute inset-0 opacity-0 cursor-pointer"
          />
        </label>
      </div>
    </div>

    <div class="border-t border-slate-100"></div>

    <!-- Basic Specs -->
    <div class="space-y-6">
      <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
        <SettingsIcon class="h-5 w-5 text-primary" />
        Basic Specifications
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Card Size <span class="text-red-500">*</span></label
          >
          <div class="relative group">
            <MaximizeIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="cardSpecs.card_size"
              class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              placeholder="e.g., 5x7 inches"
            />
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Quantity <span class="text-red-500">*</span></label
          >
          <div class="relative group">
            <LayersIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              type="number"
              v-model="cardSpecs.quantity"
              class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              placeholder="100"
            />
          </div>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700"
          >Detailed Specifications <span class="text-red-500">*</span></label
        >
        <textarea
          v-model="cardSpecs.specifications"
          class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[120px]"
          placeholder="Describe your requirements..."
        ></textarea>
      </div>
    </div>

    <!-- Paper & Finish -->
    <div class="space-y-6">
      <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
        <SparklesIcon class="h-5 w-5 text-primary" />
        Paper & Finish
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Inner GSM</label
          >
          <input
            v-model="cardSpecs.inner_gsm"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            placeholder="e.g., 300"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Envelope GSM</label
          >
          <input
            v-model="cardSpecs.envelope_gsm"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            placeholder="e.g., 120"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Card Lamination</label
          >
          <ContextDropdown
            v-model="cardSpecs.card_lamination"
            :options="[
              { label: 'None', value: 'none' },
              { label: 'Matt', value: 'matt' },
              { label: 'Glossy', value: 'glossy' },
              { label: 'Velvet', value: 'velvet' },
            ]"
            placeholder="Select Lamination"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Envelope Lamination</label
          >
          <ContextDropdown
            v-model="cardSpecs.envelope_lamination"
            :options="[
              { label: 'None', value: 'none' },
              { label: 'Matt', value: 'matt' },
              { label: 'Glossy', value: 'glossy' },
              { label: 'Velvet', value: 'velvet' },
            ]"
            placeholder="Select Lamination"
          />
        </div>
      </div>
    </div>

    <!-- Options Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <label
          class="text-sm font-semibold text-slate-700 flex items-center gap-2"
        >
          <PlusCircleIcon class="h-4 w-4 text-primary" />
          Additional Options
        </label>
        <div class="flex items-center gap-2">
          <input
            v-model="newOption"
            @keyup.enter="addOption"
            type="text"
            placeholder="Add new option..."
            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 w-40 transition-all font-medium"
          />
          <button
            @click="addOption"
            type="button"
            class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 transition-all active:scale-95 shadow-sm shadow-primary/20"
          >
            Add
          </button>
        </div>
      </div>
      <div
        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 bg-slate-50/50 p-4 rounded-2xl border border-slate-100"
      >
        <label
          v-for="option in allOptions"
          :key="option"
          class="flex items-center gap-2 cursor-pointer p-2.5 rounded-xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-slate-100"
          :class="{
            'bg-white shadow-sm border-slate-200':
              cardOptionsList.includes(option),
          }"
        >
          <input
            type="checkbox"
            :value="option"
            v-model="cardOptionsList"
            class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/20"
          />
          <span class="text-xs font-bold text-slate-700 tracking-tight">{{
            option
          }}</span>
        </label>
      </div>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="cardSpecs._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(cardSpecs._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ cardSpecs._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref } from "vue";
import {
  CreditCard as CreditCardIcon,
  Brush as BrushIcon,
  Box as BoxIcon,
  Settings as SettingsIcon,
  Maximize as MaximizeIcon,
  Layers as LayersIcon,
  Sparkles as SparklesIcon,
  PlusCircle as PlusCircleIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";
import ContextDropdown from "../../../ui/ContextDropdown.vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
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

const cardSpecs = computed(() => {
  if (!props.order.tracking) {
    props.order.tracking = {};
  }
  if (!props.order.tracking.card_specs) {
    props.order.tracking.card_specs = {
      type: "customize", // Default
      quantity: props.order.total_quantity,
    };
  }
  return props.order.tracking.card_specs;
});

// Sync type with legacy checkboxes if needed by backend,
// though we should ideally just use 'type'
watch(
  () => cardSpecs.value.type,
  (newType) => {
    if (newType === "customize") {
      cardSpecs.value.card_type_customize = true;
      cardSpecs.value.card_type_ready_made = false;
    } else {
      cardSpecs.value.card_type_customize = false;
      cardSpecs.value.card_type_ready_made = true;
    }
  },
  { immediate: true },
);

const defaultOptions = [
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
  "Pasting",
  "Others",
];

const newOption = ref("");

const customOptions = ref([]);

const allOptions = computed(() => {
  const selected = cardOptionsList.value;
  // Get any selected options that are NOT in defaults and NOT in our tracked customOptions
  // (This handles cases where data was saved previously with custom options)
  const legacyExtras = selected.filter(
    (s) => !defaultOptions.includes(s) && !customOptions.value.includes(s)
  );
  
  // Return unique set of everything
  return [...new Set([...defaultOptions, ...customOptions.value, ...legacyExtras])];
});

const addOption = () => {
  const val = newOption.value.trim();
  if (!val) return;

  // Prevent duplicates in the LIST of options
  if (
    allOptions.value.some((opt) => opt.toLowerCase() === val.toLowerCase())
  ) {
    // If it exists but isn't selected, select it
    if (!cardOptionsList.value.includes(val)) {
       cardOptionsList.value = [...cardOptionsList.value, val];
    }
    newOption.value = "";
    return;
  }

  // Add to custom options list so it persists
  customOptions.value.push(val);
  
  // Add to selected list
  cardOptionsList.value = [...cardOptionsList.value, val];
  newOption.value = "";
};

const cardOptionsList = computed({
  get: () =>
    cardSpecs.value.card_options ? cardSpecs.value.card_options.split(",") : [],
  set: (val) => {
    cardSpecs.value.card_options = val.join(",");
  },
});
</script>

