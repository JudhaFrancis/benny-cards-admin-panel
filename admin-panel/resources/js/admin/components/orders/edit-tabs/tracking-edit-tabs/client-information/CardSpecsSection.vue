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
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Customize Card -->
        <label
          class="relative flex flex-col items-center text-center gap-3 p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden h-full"
          :class="[
            cardTypes.includes('customize')
              ? 'bg-primary/5 border-primary shadow-md'
              : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
          ]"
        >
          <div
            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors mb-2"
            :class="
              cardTypes.includes('customize')
                ? 'bg-primary text-white scale-110'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <BrushIcon class="h-6 w-6" />
          </div>
          <div>
            <span
              class="block text-xs font-bold whitespace-nowrap"
              :class="
                cardTypes.includes('customize')
                  ? 'text-primary'
                  : 'text-slate-700'
              "
              >Customize Card</span
            >
            <span class="text-[10px] text-slate-400 leading-tight block mt-1"
              >Custom design and printing</span
            >
          </div>
          <div
            v-if="cardTypes.includes('customize')"
            class="absolute top-2 right-2"
          >
            <div class="w-2 h-2 rounded-full bg-primary animate-ping"></div>
          </div>
          <input
            type="checkbox"
            value="customize"
            v-model="cardTypes"
            class="absolute inset-0 opacity-0 cursor-pointer"
          />
        </label>

        <!-- Semi-Customize Card -->
        <label
          class="relative flex flex-col items-center text-center gap-3 p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden h-full"
          :class="[
            cardTypes.includes('semi_customize')
              ? 'bg-indigo-50 border-indigo-500 shadow-md'
              : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
          ]"
        >
          <div
            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors mb-2"
            :class="
              cardTypes.includes('semi_customize')
                ? 'bg-indigo-500 text-white scale-110'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <Edit3Icon class="h-6 w-6" />
          </div>
          <div>
            <span
              class="block text-xs font-bold whitespace-nowrap"
              :class="
                cardTypes.includes('semi_customize')
                  ? 'text-indigo-700'
                  : 'text-slate-700'
              "
              >Semi – Customize Card</span
            >
            <span class="text-[10px] text-slate-400 leading-tight block mt-1"
              >Semi custom design and printing</span
            >
          </div>
          <div
            v-if="cardTypes.includes('semi_customize')"
            class="absolute top-2 right-2"
          >
            <div class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></div>
          </div>
          <input
            type="checkbox"
            value="semi_customize"
            v-model="cardTypes"
            class="absolute inset-0 opacity-0 cursor-pointer"
          />
        </label>

        <!-- Ready Made Card -->
        <label
          class="relative flex flex-col items-center text-center gap-3 p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden h-full"
          :class="[
            cardTypes.includes('ready_made')
              ? 'bg-emerald-50 border-emerald-500 shadow-md'
              : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
          ]"
        >
          <div
            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors mb-2"
            :class="
              cardTypes.includes('ready_made')
                ? 'bg-emerald-500 text-white scale-110'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <BoxIcon class="h-6 w-6" />
          </div>
          <div>
            <span
              class="block text-xs font-bold whitespace-nowrap"
              :class="
                cardTypes.includes('ready_made')
                  ? 'text-emerald-700'
                  : 'text-slate-700'
              "
              >Ready Made Card</span
            >
            <span class="text-[10px] text-slate-400 leading-tight block mt-1"
              >Pre-designed stock items</span
            >
          </div>
          <div
            v-if="cardTypes.includes('ready_made')"
            class="absolute top-2 right-2"
          >
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></div>
          </div>
          <input
            type="checkbox"
            value="ready_made"
            v-model="cardTypes"
            class="absolute inset-0 opacity-0 cursor-pointer"
          />
        </label>

        <!-- Digital Local -->
        <label
          class="relative flex flex-col items-center text-center gap-3 p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300 group overflow-hidden h-full"
          :class="[
            cardTypes.includes('digital_local')
              ? 'bg-violet-50 border-violet-500 shadow-md'
              : 'bg-white border-slate-100 hover:border-slate-300 hover:bg-slate-50/50',
          ]"
        >
          <div
            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors mb-2"
            :class="
              cardTypes.includes('digital_local')
                ? 'bg-violet-500 text-white scale-110'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <SmartphoneIcon class="h-6 w-6" />
          </div>
          <div>
            <span
              class="block text-xs font-bold whitespace-nowrap"
              :class="
                cardTypes.includes('digital_local')
                  ? 'text-violet-700'
                  : 'text-slate-700'
              "
              >Digital Local</span
            >
            <span class="text-[10px] text-slate-400 leading-tight block mt-1"
              >Digital invitations and sharing</span
            >
          </div>
          <div
            v-if="cardTypes.includes('digital_local')"
            class="absolute top-2 right-2"
          >
            <div class="w-2 h-2 rounded-full bg-violet-500 animate-ping"></div>
          </div>
          <input
            type="checkbox"
            value="digital_local"
            v-model="cardTypes"
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
              class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
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
              class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
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
          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[120px]"
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
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            placeholder="e.g., 300"
          />
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Envelope GSM</label
          >
          <input
            v-model="cardSpecs.envelope_gsm"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
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
            :model-value="isCardLaminationOther ? 'others' : cardSpecs.card_lamination"
            @update:model-value="handleCardLaminationChange"
            :options="[
              { label: 'None', value: 'none' },
              { label: 'Matt', value: 'matt' },
              { label: 'Glossy', value: 'glossy' },
              { label: 'Velvet', value: 'velvet' },
              { label: 'Others', value: 'others' },
            ]"
            placeholder="Select Lamination"
          />
          <div v-if="isCardLaminationOther" class="mt-2 animate-in slide-in-from-top-1 duration-200">
            <input
              v-model="cardLaminationOtherValue"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all placeholder:text-slate-400 shadow-sm"
              placeholder="e.g., Texture Paper, Board Paper, etc."
            />
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-sm font-medium text-slate-700"
            >Envelope Lamination</label
          >
          <ContextDropdown
            :model-value="isEnvelopeLaminationOther ? 'others' : cardSpecs.envelope_lamination"
            @update:model-value="handleEnvelopeLaminationChange"
            :options="[
              { label: 'None', value: 'none' },
              { label: 'Matt', value: 'matt' },
              { label: 'Glossy', value: 'glossy' },
              { label: 'Velvet', value: 'velvet' },
              { label: 'Others', value: 'others' },
            ]"
            placeholder="Select Lamination"
          />
          <div v-if="isEnvelopeLaminationOther" class="mt-2 animate-in slide-in-from-top-1 duration-200">
            <input
              v-model="envelopeLaminationOtherValue"
              class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all placeholder:text-slate-400 shadow-sm"
              placeholder="e.g., Texture Paper, Board Paper, etc."
            />
          </div>
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
      v-if="cardSpecs._audit && !hideAudit"
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
  Edit3 as Edit3Icon,
  Box as BoxIcon,
  Smartphone as SmartphoneIcon,
  Settings as SettingsIcon,
  Maximize as MaximizeIcon,
  Layers as LayersIcon,
  Sparkles as SparklesIcon,
  PlusCircle as PlusCircleIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";
import ContextDropdown from "../../../../ui/dropdowns/ContextDropdown.vue";

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
  if (!props.order.client_information) {
    props.order.client_information = { card_specs: {} };
  }
  if (!props.order.client_information.card_specs || Array.isArray(props.order.client_information.card_specs)) {
    props.order.client_information.card_specs = {
      type: "customize",
      quantity: props.order.total_quantity,
    };
  }
  return props.order.client_information.card_specs;
});
const cardTypes = computed({
  get: () => cardSpecs.value.type ? cardSpecs.value.type.split(",") : [],
  set: (val) => {
    cardSpecs.value.type = val.join(",");
  },
});

watch(
  () => cardSpecs.value.type,
  (newTypeStr) => {
    const types = newTypeStr ? newTypeStr.split(",") : [];
    cardSpecs.value.card_type_customize = types.includes("customize");
    cardSpecs.value.card_type_ready_made = types.includes("ready_made");
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

const standardLaminations = ["none", "matt", "glossy", "velvet"];

const isCardLaminationOther = computed(() => {
  const val = cardSpecs.value.card_lamination;
  if (!val) return false;
  return val === "others" || !standardLaminations.includes(val);
});

const cardLaminationOtherValue = computed({
  get: () =>
    isCardLaminationOther.value && cardSpecs.value.card_lamination !== "others"
      ? cardSpecs.value.card_lamination
      : "",
  set: (val) => {
    cardSpecs.value.card_lamination = val || "others";
  },
});

const isEnvelopeLaminationOther = computed(() => {
  const val = cardSpecs.value.envelope_lamination;
  if (!val) return false;
  return val === "others" || !standardLaminations.includes(val);
});

const envelopeLaminationOtherValue = computed({
  get: () =>
    isEnvelopeLaminationOther.value &&
    cardSpecs.value.envelope_lamination !== "others"
      ? cardSpecs.value.envelope_lamination
      : "",
  set: (val) => {
    cardSpecs.value.envelope_lamination = val || "others";
  },
});

const handleCardLaminationChange = (val) => {
  if (val === "others") {
    if (
      !cardSpecs.value.card_lamination ||
      standardLaminations.includes(cardSpecs.value.card_lamination)
    ) {
      cardSpecs.value.card_lamination = "others";
    }
  } else {
    cardSpecs.value.card_lamination = val;
  }
};

const handleEnvelopeLaminationChange = (val) => {
  if (val === "others") {
    if (
      !cardSpecs.value.envelope_lamination ||
      standardLaminations.includes(cardSpecs.value.envelope_lamination)
    ) {
      cardSpecs.value.envelope_lamination = "others";
    }
  } else {
    cardSpecs.value.envelope_lamination = val;
  }
};
</script>

