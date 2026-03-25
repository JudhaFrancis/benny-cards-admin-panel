<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Status Overview Card -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <PackageIcon class="h-4 w-4" />
        Logistics Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            packagingLogistics.card_received
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              packagingLogistics.card_received
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <DownloadIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              Card Received
            </p>
            <input
              type="checkbox"
              v-model="packagingLogistics.card_received"
              class="sr-only"
            />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              packagingLogistics.card_received
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon
              v-if="packagingLogistics.card_received"
              class="h-3 w-3 text-white"
            />
          </div>
        </label>

        <label
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
          :class="
            packagingLogistics.crafting_done
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <div
            class="p-2 rounded-lg transition-colors"
            :class="
              packagingLogistics.crafting_done
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
            "
          >
            <ScissorsIcon class="h-4 w-4" />
          </div>
          <div class="flex-1">
            <p class="text-xs font-bold text-slate-900 leading-none">
              Crafting Done
            </p>
            <input
              type="checkbox"
              v-model="packagingLogistics.crafting_done"
              class="sr-only"
            />
          </div>
          <div
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
            :class="
              packagingLogistics.crafting_done
                ? 'border-primary bg-primary scale-110'
                : 'border-slate-200'
            "
          >
            <CheckIcon
              v-if="packagingLogistics.crafting_done"
              class="h-3 w-3 text-white"
            />
          </div>
        </label>
      </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700"
          >Crafted By <span class="text-red-500">*</span></label
        >
        <ContextDropdown
          v-model="packagingLogistics.crafted_by"
          :options="staffOptions"
          placeholder="Select staff"
          :icon="UserIcon"
        />
      </div>

      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700"
          >Names <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <TypeIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            v-model="packagingLogistics.names"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            placeholder="Names on cards"
          />
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700"
          >Date <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <DatePicker
            v-model="packagingLogistics.date"
            placeholder="Select Date"
            custom-class="pl-11 py-2.5 text-xs"
          >
            <template #leading>
              <CalendarIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
              />
            </template>
          </DatePicker>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700"
          >Qty of Cards <span class="text-red-500">*</span></label
        >
        <div class="relative group">
          <HashIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            type="number"
            v-model="packagingLogistics.qty_cards"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700">Start Time</label>
        <div class="relative group">
          <ClockIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            type="time"
            v-model="packagingLogistics.start_time"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700">End Time</label>
        <div class="relative group">
          <ClockIcon
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
          />
          <input
            type="time"
            v-model="packagingLogistics.end_time"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
          />
        </div>
      </div>
    </div>

    <!-- Textareas -->
    <div class="space-y-2">
      <label class="text-sm font-medium text-slate-700"
        >Envelope / Ribbon / Tag / Sticker
        <span class="text-red-500">*</span></label
      >
      <textarea
        v-model="packagingLogistics.logistics_details"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Details about accessories..."
      ></textarea>
    </div>

    <div class="space-y-2">
      <label class="text-xs font-medium text-slate-700"
        >Issues in Card</label
      >
      <textarea
        v-model="packagingLogistics.card_issues"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Describe any issues found..."
      ></textarea>
    </div>

    <!-- Audit Footer -->
    <div
      v-if="packagingLogistics._audit && !hideAudit"
      class="pt-4 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400"
    >
      <span class="flex items-center gap-2">
        <ClockIcon class="h-3.5 w-3.5" />
        Last updated
        <span
          class="font-medium bg-slate-100 px-2 py-0.5 rounded-full text-slate-600"
          >{{ formatAuditDate(packagingLogistics._audit.updated_at) }}</span
        >
        by
        <span
          class="font-medium text-slate-600 underline decoration-slate-200 underline-offset-2"
          >{{ packagingLogistics._audit.updated_by }}</span
        >
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  User as UserIcon,
  Package as PackageIcon,
  Check as CheckIcon,
  Download as DownloadIcon,
  Scissors as ScissorsIcon,
  Type as TypeIcon,
  Calendar as CalendarIcon,
  Hash as HashIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";
import ContextDropdown from "../../../../ui/dropdowns/ContextDropdown.vue";
import DatePicker from "../../../../ui/pickers/DatePicker.vue";

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  staffOptions: {
    type: Array,
    default: () => [],
  },
  hideAudit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:order"]);

const packagingLogistics = computed(() => {
  if (!props.order.packaging) {
    props.order.packaging = { packaging_logistics: {} };
  }
  if (!props.order.packaging.packaging_logistics) {
    props.order.packaging.packaging_logistics = {};
  }
  return props.order.packaging.packaging_logistics;
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
</script>

