<template>
  <div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-6">
      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700"
          >Confirmed Date <span class="text-red-500">*</span></label
        >
        <DatePicker
          v-model="printingStatus.confirmed_date"
          placeholder="Select Confirmed Date"
          custom-class="py-2.5 text-xs"
        />
      </div>
      <div class="space-y-2">
        <label class="text-xs font-medium text-slate-700"
          >Printer Company Names</label
        >
        <TagInput 
          v-model="printingStatus.company_names" 
          placeholder="Enter printer name" 
        />
      </div>
      <div v-if="printingStatus" class="flex items-center h-full pt-6 pl-2">
        <label class="flex items-center gap-2 cursor-pointer group">
          <input
            v-model="printingStatus.is_reprint"
            type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary/20 transition-all"
          />
          <span 
            class="text-xs font-bold transition-colors"
            :class="printingStatus.is_reprint ? 'text-primary' : 'text-slate-600 group-hover:text-primary'"
          >
            Reprint
          </span>
        </label>
      </div>
    </div>

    <!-- Printing Checklist -->
    <div class="flex flex-wrap gap-4">
      <label
        class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
        :class="
          printingStatus.readymade_ordered
            ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
            : 'border-slate-200 hover:border-slate-300'
        "
      >
        <div
          class="p-2 rounded-lg transition-colors"
          :class="
            printingStatus.readymade_ordered
              ? 'bg-primary text-white'
              : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
          "
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
            <path d="M3 6h18" />
            <path d="M16 10a4 4 0 0 1-8 0" />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-xs font-bold text-slate-900 leading-none">Ordered</p>
          <input
            type="checkbox"
            v-model="printingStatus.readymade_ordered"
            class="sr-only"
          />
        </div>
      </label>

      <label
        class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
        :class="
          printingStatus.readymade_sub_received
            ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
            : 'border-slate-200 hover:border-slate-300'
        "
      >
        <div
          class="p-2 rounded-lg transition-colors"
          :class="
            printingStatus.readymade_sub_received
              ? 'bg-primary text-white'
              : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
          "
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="m7.5 4.27 9 5.15" />
            <path
              d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"
            />
            <path d="m3.27 6.96 8.73 5.05 8.73-5.05" />
            <path d="M12 22.08V12" />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-xs font-bold text-slate-900 leading-none">
            Card Received
          </p>
          <input
            type="checkbox"
            v-model="printingStatus.readymade_sub_received"
            class="sr-only"
          />
        </div>
      </label>

      <label
        class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border bg-white transition-all cursor-pointer group"
        :class="
          printingStatus.readymade_sent_to_print
            ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
            : 'border-slate-200 hover:border-slate-300'
        "
      >
        <div
          class="p-2 rounded-lg transition-colors"
          :class="
            printingStatus.readymade_sent_to_print
              ? 'bg-primary text-white'
              : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'
          "
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <polyline points="6 9 6 2 18 2 18 9" />
            <path
              d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"
            />
            <rect x="6" y="14" width="12" height="8" />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-xs font-bold text-slate-900 leading-none">
            Sent to Print
          </p>
          <input
            type="checkbox"
            v-model="printingStatus.readymade_sent_to_print"
            class="sr-only"
          />
        </div>
      </label>
    </div>

    <!-- Dynamic Card Type Sections -->
    <div
      v-for="type in activeCardTypes"
      :key="type.id"
      class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 space-y-6"
    >
      <h4 class="font-bold text-slate-900 flex items-center gap-2">
        <component :is="type.icon" class="h-4 w-4 text-primary" />
        {{ type.label }}
      </h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-xs font-medium text-slate-700"
            >Sent to Print Date <span class="text-red-500">*</span></label
          >
          <DatePicker
            v-model="printingStatus[type.id + '_sent_to_print_date']"
            placeholder="Select Date"
            custom-class="py-2.5 text-xs"
          />
        </div>
        <div class="space-y-2">
          <label class="text-xs font-medium text-slate-700"
            >Received Date <span class="text-red-500">*</span></label
          >
          <DatePicker
            v-model="printingStatus[type.id + '_delivery_date']"
            placeholder="Select Date"
            custom-class="py-2.5 text-xs"
          />
        </div>
      </div>
      <div class="space-y-3">
        <label
          class="text-xs font-black uppercase tracking-wider text-slate-400"
          >Follow Up Checklist</label
        >
        <div class="space-y-4">
          <!-- Horizontal Checklist Grid -->
          <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
            <label
              v-for="day in days"
              :key="day"
              class="flex flex-col items-center justify-center p-2 rounded-xl border transition-all cursor-not-allowed group"
              :class="
                isDayActive(day, type.id)
                  ? 'bg-primary/5 border-primary shadow-sm ring-2 ring-primary/5'
                  : 'bg-slate-50 border-slate-100 opacity-50'
              "
            >
              <span
                class="text-[10px] font-black uppercase tracking-tighter mb-1.5 transition-colors"
                :class="
                  isDayActive(day, type.id)
                    ? 'text-primary'
                    : 'text-slate-400'
                "
              >
                {{ day }}
              </span>
              <input
                type="checkbox"
                :checked="isDayActive(day, type.id)"
                disabled
                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 transition-transform opacity-70"
              />
            </label>
          </div>

          <!-- Dynamic Individual Notes Sections -->
          <div class="space-y-3 mt-4">
            <div 
              v-for="day in days" 
              :key="'note_' + day"
              v-show="isDayActive(day, type.id)"
              class="flex flex-col gap-1.5 p-3 rounded-2xl bg-white border border-slate-100 shadow-sm animate-in fade-in slide-in-from-top-2 duration-300"
            >
              <div class="flex items-center gap-2">
                <span class="text-[9px] font-black bg-primary/10 text-primary px-2 py-0.5 rounded-full uppercase tracking-widest">Follow-up Status ({{ day }})</span>
              </div>
              <input
                v-model="printingStatus[type.id + '_' + day.toLowerCase().replace(' ', '_') + '_notes']"
                type="text"
                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-lg focus:border-primary focus:bg-white focus:outline-none text-xs text-slate-700 placeholder:text-slate-300 transition-all font-medium"
                :placeholder="'What happened on ' + day + '?'"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-xs font-medium text-slate-700"
        >Any Printing Issues</label
      >
      <textarea
        v-model="printingStatus.printing_issues"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Describe any issues..."
      ></textarea>
    </div>

    <div class="space-y-2">
      <label class="text-xs font-medium text-slate-700">Delay Reason</label>
      <textarea
        v-model="printingStatus.delay_reason"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Reason for delay..."
      ></textarea>
    </div>


  </div>
</template>

<script setup>
import { computed, watch, onMounted, ref } from "vue";
import { Clock as ClockIcon, User as UserIcon, Brush as BrushIcon, Edit3 as Edit3Icon, Box as BoxIcon, Smartphone as SmartphoneIcon } from "lucide-vue-next";
import ContextDropdown from "../../../../ui/dropdowns/ContextDropdown.vue";
import DatePicker from "../../../../ui/pickers/DatePicker.vue";
import TagInput from "../../../../ui/forms/TagInput.vue";

const isDayActive = (day, typeId) => {
  const dateStr = printingStatus.value[typeId + '_sent_to_print_date'];
  if (!dateStr) return false;
  
  const dayNum = parseInt(day.replace('Day ', ''));
  const startDate = new Date(dateStr);
  startDate.setHours(0, 0, 0, 0);
  
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const diffTime = today.getTime() - startDate.getTime();
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  
  // Same day = Day 1 logic (+1 added)
  return (diffDays + 1) >= dayNum;
};

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  staffOptions: {
    type: Array,
    default: () => [],
  },
});

const printingStatus = computed(() => {
  if (!props.order.printing) {
    props.order.printing = { printing_status: {} };
  }
  if (!props.order.printing.printing_status || Array.isArray(props.order.printing.printing_status)) {
    props.order.printing.printing_status = {};
  }
  return props.order.printing.printing_status;
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

const typeConfig = [
  { id: 'customize', label: 'Customize Card', icon: BrushIcon },
  { id: 'semi_customize', label: 'Semi – Customize Card', icon: Edit3Icon },
  { id: 'ready_made', label: 'Ready Made Card', icon: BoxIcon },
  { id: 'digital_local', label: 'Digital Local', icon: SmartphoneIcon },
];

const activeCardTypes = computed(() => {
  const selectedTypes = props.order.client_information?.card_specs?.type?.split(',') || [];
  return typeConfig.filter(t => selectedTypes.includes(t.id));
});

const getFollowUpList = (typeId) => {
  return computed({
    get: () => printingStatus.value[typeId + '_follow_up'] ? printingStatus.value[typeId + '_follow_up'].split(',') : [],
    set: (val) => {
      printingStatus.value[typeId + '_follow_up'] = val.join(',');
    }
  });
};

const readymadeFollowUpList = ref([]);
const customizeFollowUpList = getFollowUpList('customize');

const autoSelectDays = () => {
  activeCardTypes.value.forEach(type => {
    const baseDateStr = printingStatus.value[type.id + '_sent_to_print_date'];
    if (!baseDateStr) return;

    const baseDate = new Date(baseDateStr);
    baseDate.setHours(0, 0, 0, 0);
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const diffTime = today - baseDate;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays >= 0) {
      const maxDay = Math.min(diffDays + 1, 7);
      const followUp = getFollowUpList(type.id);
      const current = [...followUp.value];
      let changed = false;
      for (let i = 1; i <= maxDay; i++) {
          const dayStr = `Day ${i}`;
          if (!current.includes(dayStr)) {
              current.push(dayStr);
              changed = true;
          }
      }
      if (changed) followUp.value = current;
    }
  });
};

watch(() => printingStatus.value, () => {
  autoSelectDays();
}, { deep: true });

onMounted(() => {
  autoSelectDays();
});

const days = ["Day 1", "Day 2", "Day 3", "Day 4", "Day 5", "Day 6", "Day 7"];
</script>

