<template>
  <div class="space-y-8">
    <div class="space-y-6">
      <div class="space-y-4">
        <label
          class="text-sm font-semibold text-slate-700 flex items-center gap-2"
        >
          <PrinterIcon class="h-4 w-4 text-primary" />
          Design Outputs
        </label>
        <div class="flex flex-wrap gap-2">
          <div
            v-for="output in designOutputs"
            :key="output"
            class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all"
            :class="
              designOutputsList.includes(output)
                ? 'bg-primary/10 text-primary border-primary/20'
                : 'bg-slate-50 text-slate-400 border-slate-100 opacity-50'
            "
          >
            <div class="flex items-center gap-2">
              <CheckCircleIcon
                v-if="designOutputsList.includes(output)"
                class="h-3 w-3"
              />
              <CircleIcon v-else class="h-3 w-3" />
              {{ output }}
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <label
          class="text-sm font-semibold text-slate-700 flex items-center gap-2"
        >
          <SparklesIcon class="h-4 w-4 text-primary" />
          Print & Add-ons
        </label>
        <div class="flex flex-wrap gap-2">
          <div
            v-for="addon in printAddons"
            :key="addon"
            class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all"
            :class="
              printAddonsList.includes(addon)
                ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                : 'bg-slate-50 text-slate-400 border-slate-100 opacity-50'
            "
          >
            <div class="flex items-center gap-2">
              <CheckCircleIcon
                v-if="printAddonsList.includes(addon)"
                class="h-3 w-3"
              />
              <CircleIcon v-else class="h-3 w-3" />
              {{ addon }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="designPrint._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          designPrint._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(designPrint._audit.updated_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Printer as PrinterIcon,
  Sparkles as SparklesIcon,
  CheckCircle as CheckCircleIcon,
  Circle as CircleIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const designPrint = computed(() => props.order.tracking?.design_print || {});

const designOutputsList = computed(() =>
  designPrint.value.design_outputs
    ? designPrint.value.design_outputs.split(",")
    : [],
);

const printAddonsList = computed(() =>
  designPrint.value.print_addons
    ? designPrint.value.print_addons.split(",")
    : [],
);

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

const formatAuditDate = (dateString) => {
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};
</script>
