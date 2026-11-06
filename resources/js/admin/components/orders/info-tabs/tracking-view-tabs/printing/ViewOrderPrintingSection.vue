<template>
  <div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Confirmed Date</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <CalendarIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            formatDate(printingStatus.confirmed_date) || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Printer Company Names</label
        >
        <div
          class="flex flex-wrap items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-100 min-h-[46px]"
        >
          <PrinterIcon class="h-4 w-4 text-slate-400" v-if="!printingStatus.company_names?.length" />
          <span v-if="!printingStatus.company_names?.length" class="text-sm font-medium text-slate-900">
            N/A
          </span>
          <span
            v-for="(company, index) in printingStatus.company_names"
            :key="index"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-700 shadow-sm"
          >
            <PrinterIcon class="h-3 w-3 text-slate-400" />
            {{ company }}
          </span>
        </div>
      </div>

      <div v-if="printingStatus.is_reprint" class="flex flex-col justify-center">
        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-black uppercase tracking-widest w-fit">
          <div class="w-1 h-1 rounded-full bg-rose-500"></div>
          Reprint
        </div>
      </div>
    </div>

    <!-- Printing Status Indicators -->
    <div class="flex flex-wrap gap-4">
      <div
        class="flex-1 min-w-[120px] flex items-center gap-3 p-3 rounded-xl border"
        :class="
          printingStatus.readymade_ordered
            ? 'bg-white border-primary border shadow-sm'
            : 'bg-slate-100/50 border-slate-100 opacity-50'
        "
      >
        <div
          class="p-1.5 rounded-lg"
          :class="
            printingStatus.readymade_ordered
              ? 'bg-primary text-white'
              : 'bg-slate-200 text-slate-400'
          "
        >
          <ShoppingCartIcon class="h-3.5 w-3.5" />
        </div>
        <span
          class="text-xs font-bold"
          :class="
            printingStatus.readymade_ordered ? 'text-slate-900' : 'text-slate-400'
          "
          >Ordered</span
        >
      </div>
      <div
        class="flex-1 min-w-[120px] flex items-center gap-3 p-3 rounded-xl border"
        :class="
          printingStatus.readymade_sub_received
            ? 'bg-white border-primary border shadow-sm'
            : 'bg-slate-100/50 border-slate-100 opacity-50'
        "
      >
        <div
          class="p-1.5 rounded-lg"
          :class="
            printingStatus.readymade_sub_received
              ? 'bg-primary text-white'
              : 'bg-slate-200 text-slate-400'
          "
        >
          <PackageIcon class="h-3.5 w-3.5" />
        </div>
        <span
          class="text-xs font-bold"
          :class="
            printingStatus.readymade_sub_received
              ? 'text-slate-900'
              : 'text-slate-400'
          "
          >Received</span
        >
      </div>
      <div
        class="flex-1 min-w-[120px] flex items-center gap-3 p-3 rounded-xl border"
        :class="
          printingStatus.readymade_sent_to_print
            ? 'bg-white border-primary border shadow-sm'
            : 'bg-slate-100/50 border-slate-100 opacity-50'
        "
      >
        <div
          class="p-1.5 rounded-lg"
          :class="
            printingStatus.readymade_sent_to_print
              ? 'bg-primary text-white'
              : 'bg-slate-200 text-slate-400'
          "
        >
          <PrinterIcon class="h-3.5 w-3.5" />
        </div>
        <span
          class="text-xs font-bold"
          :class="
            printingStatus.readymade_sent_to_print
              ? 'text-slate-900'
              : 'text-slate-400'
          "
          >To Print</span
        >
      </div>
    </div>

    <!-- Dynamic Card Type History -->
    <div
      v-for="type in activeCardTypes"
      :key="type.id"
      class="p-6 bg-slate-50/50 rounded-2xl border border-slate-100 space-y-6"
    >
      <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
        <component :is="type.icon" class="h-4 w-4 text-primary" />
        {{ type.label }} Status
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-slate-400"
            >Sent to Print Date</label
          >
          <div
            class="p-3 rounded-xl bg-white border border-slate-100 text-sm font-medium text-slate-900"
          >
            {{ formatDate(printingStatus[type.id + '_sent_to_print_date']) || "—" }}
          </div>
        </div>
        <div class="space-y-2">
          <label
            class="text-[10px] font-black uppercase tracking-widest text-slate-400"
            >Received Date</label
          >
          <div
            class="p-3 rounded-xl bg-white border border-slate-100 text-sm font-medium text-slate-900"
          >
            {{ formatDate(printingStatus[type.id + '_delivery_date']) || "—" }}
          </div>
        </div>
      </div>
      <div class="space-y-3" v-if="printingStatus.confirmed_date">
        <label
          class="text-[10px] font-black uppercase tracking-widest text-slate-400"
          >Follow Up Checklist</label
        >
        
        <!-- Horizontal Checklist Grid (Read-only) -->
        <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 pb-2">
          <div
            v-for="day in days"
            :key="'view_day_' + day"
            class="flex flex-col items-center justify-center p-2 rounded-xl border transition-all"
            :class="
              isDayActive(day)
                ? 'bg-emerald-50 border-emerald-200'
                : 'bg-slate-50 border-slate-100 opacity-50'
            "
          >
            <span
              class="text-[9px] font-bold uppercase tracking-tighter mb-1 transition-colors"
              :class="isDayActive(day) ? 'text-emerald-600' : 'text-slate-300'"
            >
              {{ day }}
            </span>
            <div 
              class="w-3.5 h-3.5 rounded-md border flex items-center justify-center"
              :class="isDayActive(day) ? 'bg-emerald-500 border-emerald-500' : 'bg-white border-slate-200'"
            >
              <svg v-if="isDayActive(day)" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div 
            v-for="day in days" 
            :key="day"
            v-show="isDayActive(day)"
            class="flex items-start gap-4 p-4 rounded-2xl bg-white border border-slate-100 shadow-sm"
          >
            <!-- Day Badge -->
            <div class="min-w-[70px]">
              <span class="px-2 py-1 rounded-lg text-[10px] font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wider block text-center whitespace-nowrap">
                Follow-up Status ({{ day }})
              </span>
            </div>

            <!-- Note Content -->
            <div class="flex-1">
              <p class="text-xs font-medium text-slate-700 leading-relaxed italic">
                {{ printingStatus[type.id + '_' + day.toLowerCase().replace(' ', '_') + '_notes'] || "No notes recorded for this day." }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Printing Issues</label
        >
        <div
          class="p-4 rounded-xl bg-rose-50 border border-rose-100 text-sm text-rose-700 italic min-h-[60px]"
        >
          {{ printingStatus.printing_issues || "No issues reported." }}
        </div>
      </div>
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Delay Reason</label
        >
        <div
          class="p-4 rounded-xl bg-amber-50 border border-amber-100 text-sm text-amber-700 italic min-h-[60px]"
        >
          {{ printingStatus.delay_reason || "No delays." }}
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="order.printing && !hideAudit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          order.printing.modified_by?.name || order.added_by?.name || "System"
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(order.printing.updated_at || order.printing.created_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Calendar as CalendarIcon,
  ShoppingCart as ShoppingCartIcon,
  Package as PackageIcon,
  Printer as PrinterIcon,
  Brush as BrushIcon,
  Clock as ClockIcon,
  Edit3 as Edit3Icon,
  Box as BoxIcon,
  Smartphone as SmartphoneIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
  hideAudit: { type: Boolean, default: false },
});

const printingStatus = computed(
  () => props.order.printing?.printing_status || {},
);

const typeConfig = [
  { id: 'customize', label: 'Customize Card', icon: BrushIcon },
  { id: 'semi_customize', label: 'Semi – Customize Card', icon: Edit3Icon },
  { id: 'ready_made', label: 'Ready Made Card', icon: BoxIcon },
  { id: 'digital_local', icon: SmartphoneIcon, label: 'Digital Local' },
];

const activeCardTypes = computed(() => {
  const selectedTypes = props.order.client_information?.card_specs?.type?.split(',') || [];
  return typeConfig.filter(t => selectedTypes.includes(t.id));
});

const getFollowUpList = (typeId) => {
  return printingStatus.value[typeId + '_follow_up'] 
    ? printingStatus.value[typeId + '_follow_up'].split(',') 
    : [];
};

const readymadeFollowUpList = computed(() => []);
const customizeFollowUpList = computed(() => getFollowUpList('customize'));

const days = ["Day 1", "Day 2", "Day 3", "Day 4", "Day 5", "Day 6", "Day 7"];

const isDayActive = (day) => {
  if (!printingStatus.value.confirmed_date) return false;
  
  const dayNum = parseInt(day.replace('Day ', ''));
  const confirmedDate = new Date(printingStatus.value.confirmed_date);
  confirmedDate.setHours(0, 0, 0, 0);
  
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const diffTime = today.getTime() - confirmedDate.getTime();
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  
  // Same day = Day 1 logic (+1 added)
  return (diffDays + 1) >= dayNum;
};

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};

const formatAuditDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  const d = `${day}-${month}-${year}`;
  const t = date.toLocaleTimeString("en-GB", {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  }).toUpperCase();
  return `${d} at ${t}`;
};
</script>

