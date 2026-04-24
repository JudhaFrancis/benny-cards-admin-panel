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

    <!-- Sticker Design Image View -->
    <div v-if="order.designing?.sticker_image" class="space-y-4 pt-6 border-t border-slate-100">
      <label
        class="text-sm font-semibold text-slate-700 flex items-center gap-2"
      >
        <ImageIcon class="h-4 w-4 text-primary" />
        Sticker Design Image
      </label>
      <div class="max-w-[280px] bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm group transition-all hover:shadow-md">
        <div class="aspect-square relative flex items-center justify-center bg-slate-50">
          <img :src="getImageSource(order.designing.sticker_image)" class="w-full h-full object-contain" />
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
             <button @click="isPreviewOpen = true" class="p-2 bg-white text-slate-900 rounded-xl hover:bg-slate-50 transition-all active:scale-95 shadow-lg" title="Preview">
                <EyeIcon class="h-4 w-4" />
             </button>
             <a :href="getImageSource(order.designing.sticker_image)" :download="`sticker-${order.order_number}`" class="p-2 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all active:scale-95 shadow-lg" title="Download">
                <DownloadIcon class="h-4 w-4" />
             </a>
          </div>
        </div>
        <div class="px-4 py-2 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sticker Design</span>
            <div class="flex items-center gap-3">
               <button @click="isPreviewOpen = true" class="text-[10px] font-bold text-primary hover:underline">Preview</button>
               <a :href="getImageSource(order.designing.sticker_image)" :download="`sticker-${order.order_number}`" class="text-[10px] font-bold text-primary hover:underline">Download</a>
            </div>
        </div>
      </div>

      <!-- Preview Modal -->
      <teleport to="body">
        <div v-if="isPreviewOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-sm" @click="isPreviewOpen = false"></div>
          <div class="relative max-w-5xl max-h-[90vh] w-full flex flex-col items-center gap-4">
            <button @click="isPreviewOpen = false" class="absolute -top-12 right-0 p-2 text-white hover:text-slate-300 transition-colors">
              <XIcon class="h-6 w-6" />
            </button>
            <img :src="getImageSource(order.designing.sticker_image)" class="max-w-full max-h-full rounded-2xl shadow-2xl object-contain bg-white" />
          </div>
        </div>
      </teleport>
    </div>

    <!-- Audit Information -->
    <div
      v-if="order.designing && !hideAudit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          order.designing.modified_by?.name || order.designing.added_by?.name || "System"
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(order.designing.updated_at || order.designing.created_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import {
  Printer as PrinterIcon,
  Sparkles as SparklesIcon,
  CheckCircle as CheckCircleIcon,
  Circle as CircleIcon,
  Clock as ClockIcon,
  Image as ImageIcon,
  Eye as EyeIcon,
  Download as DownloadIcon,
  X as XIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
  hideAudit: { type: Boolean, default: false },
});

const isPreviewOpen = ref(false);

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("data:") || path.startsWith("http")) return path;
  return `/${path}`;
};

const designPrint = computed(() => props.order.designing?.design_print || {});

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

