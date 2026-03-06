<template>
  <div class="space-y-8">
    <!-- Card Type Display -->
    <div class="space-y-4">
      <label
        class="text-sm font-semibold text-slate-700 flex items-center gap-2"
      >
        <CreditCardIcon class="h-4 w-4 text-primary" />
        Product Type
      </label>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          class="flex items-center gap-4 p-5 rounded-2xl border-2 transition-all"
          :class="
            cardSpecs.type === 'customize'
              ? 'bg-primary/5 border-primary shadow-sm'
              : 'bg-slate-50 border-slate-100 opacity-60'
          "
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center bg-primary text-white"
            v-if="cardSpecs.type === 'customize'"
          >
            <BrushIcon class="h-5 w-5" />
          </div>
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center bg-slate-100 text-slate-400"
            v-else
          >
            <BrushIcon class="h-5 w-5" />
          </div>
          <div>
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
        </div>

        <div
          class="flex items-center gap-4 p-5 rounded-2xl border-2 transition-all"
          :class="
            cardSpecs.type === 'ready_made'
              ? 'bg-emerald-50 border-emerald-500 shadow-sm'
              : 'bg-slate-50 border-slate-100 opacity-60'
          "
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-500 text-white"
            v-if="cardSpecs.type === 'ready_made'"
          >
            <BoxIcon class="h-5 w-5" />
          </div>
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center bg-slate-100 text-slate-400"
            v-else
          >
            <BoxIcon class="h-5 w-5" />
          </div>
          <div>
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
        </div>
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
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wider"
            >Card Size</label
          >
          <div
            class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900 flex items-center gap-2"
          >
            <MaximizeIcon class="h-4 w-4 text-slate-400" />
            {{ cardSpecs.card_size || "N/A" }}
          </div>
        </div>
        <div class="space-y-2">
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wider"
            >Quantity</label
          >
          <div
            class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900 flex items-center gap-2"
          >
            <LayersIcon class="h-4 w-4 text-slate-400" />
            {{ cardSpecs.quantity || "N/A" }}
          </div>
        </div>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Detailed Specifications</label
        >
        <div
          class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm text-slate-700 leading-relaxed whitespace-pre-wrap min-h-[100px]"
        >
          {{ cardSpecs.specifications || "No specifications provided." }}
        </div>
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
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wider"
            >Inner GSM</label
          >
          <div
            class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900"
          >
            {{ cardSpecs.inner_gsm || "N/A" }}
          </div>
        </div>
        <div class="space-y-2">
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wider"
            >Envelope GSM</label
          >
          <div
            class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900"
          >
            {{ cardSpecs.envelope_gsm || "N/A" }}
          </div>
        </div>
        <div class="space-y-2">
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wider"
            >Card Lamination</label
          >
          <div
            class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900 capitalize"
          >
            {{ cardSpecs.card_lamination || "None" }}
          </div>
        </div>
        <div class="space-y-2">
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wider"
            >Envelope Lamination</label
          >
          <div
            class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-sm font-medium text-slate-900 capitalize"
          >
            {{ cardSpecs.envelope_lamination || "None" }}
          </div>
        </div>
      </div>
    </div>

    <!-- Additional Options -->
    <div class="space-y-4" v-if="cardSpecs.card_options">
      <label
        class="text-sm font-semibold text-slate-700 flex items-center gap-2"
      >
        <PlusCircleIcon class="h-4 w-4 text-primary" />
        Additional Options
      </label>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="option in cardSpecs.card_options.split(',')"
          :key="option"
          class="px-3 py-1.5 rounded-lg bg-primary/10 text-primary border border-primary/20 text-xs font-bold"
        >
          {{ option }}
        </span>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="cardSpecs._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          cardSpecs._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(cardSpecs._audit.updated_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
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

const props = defineProps({
  order: { type: Object, required: true },
});

const cardSpecs = computed(() => props.order.tracking?.card_specs || {});

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
</script>

