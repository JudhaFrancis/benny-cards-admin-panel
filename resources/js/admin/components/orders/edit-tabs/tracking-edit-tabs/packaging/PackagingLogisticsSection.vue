<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Status Overview Card -->
    <div class="relative z-[40] bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <PackageIcon class="h-4 w-4" />
        Logistics Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <div
          class="flex-1 min-w-[140px] flex flex-col gap-2 p-4 rounded-xl border bg-white transition-all group"
          :class="
            packagingLogistics.card_received
              ? 'border-primary bg-primary/5 ring-4 ring-primary/5'
              : 'border-slate-200 hover:border-slate-300'
          "
        >
          <label class="flex items-center gap-3 cursor-pointer">
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
              <input type="checkbox" :checked="packagingLogistics.card_received"
                @change="(e) => updateSection('card_received', e.target.checked)" class="sr-only" />
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
          
          <div v-if="packagingLogistics.card_received" class="mt-2 pt-2 border-t border-primary/10 animate-in fade-in slide-in-from-top-2 duration-300">
             <DatePicker 
                :model-value="packagingLogistics.card_received_date" 
                @update:model-value="(val) => updateSection('card_received_date', val)"
                placeholder="Received Date" 
                custom-class="pl-9 py-1.5 text-[10px] h-8"
              >
                <template #leading>
                  <CalendarIcon
                    class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-primary"
                  />
                </template>
              </DatePicker>
          </div>
        </div>

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
            <input type="checkbox" :checked="packagingLogistics.crafting_done"
              @change="(e) => updateSection('crafting_done', e.target.checked)" class="sr-only" />
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


    <!-- Status Overview Card / Logistics Status (End) -->
    
    <!-- Workflow & Assignment Section -->
    <div class="relative z-[30] bg-slate-50/50 rounded-2xl p-6 border border-slate-100 space-y-6">
      <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
        <UserIcon class="h-4 w-4" /> Assignment & Completion
      </h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-xs font-medium text-slate-700"
            >Assigned By <span class="text-red-500">*</span></label
          >
          <SearchableDropdown 
            :model-value="packagingLogistics.assigned_by_multiple || []"
            @update:model-value="(val) => updateSection('assigned_by_multiple', val)" 
            :options="staffOptions"
            placeholder="Select Staff" 
            :icon="UserIcon"
            multiple
          />
        </div>

        <div class="space-y-2">
          <label class="text-xs font-medium text-slate-700"
            >Crafted By <span class="text-red-500">*</span></label
          >
          <SearchableDropdown 
            :model-value="packagingLogistics.crafted_by_multiple || []"
            @update:model-value="(val) => updateSection('crafted_by_multiple', val)" 
            :options="staffOptions"
            placeholder="Select Staff" 
            :icon="UserCheckIcon" 
            multiple
          />
        </div>
      </div>
    </div>

    <!-- Logistics Details Rows -->
    <div class="relative z-[20] space-y-6">
      <!-- Row 1: Names, Date, Qty -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-2">
          <label class="text-xs font-medium text-slate-700"
            >Names <span class="text-red-500">*</span></label
          >
          <div class="relative group">
            <TypeIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input :value="packagingLogistics.names" @input="(e) => updateSection('names', e.target.value)"
              class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all"
              placeholder="Names on cards" />
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-xs font-medium text-slate-700"
            >Date <span class="text-red-500">*</span></label
          >
          <div class="relative group">
            <DatePicker :model-value="packagingLogistics.date" @update:model-value="(val) => updateSection('date', val)"
              placeholder="Select Date" custom-class="pl-11 py-2.5 text-xs h-[42px]">
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
            <input type="number" :value="packagingLogistics.qty_cards"
              @input="(e) => updateSection('qty_cards', e.target.value)"
              class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all h-[42px]" />
          </div>
        </div>
      </div>

      <!-- Row 2: Times -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-xs font-medium text-slate-700">Start Time</label>
          <div class="relative group">
            <TimePicker :model-value="packagingLogistics.start_time"
              @update:model-value="(val) => updateSection('start_time', val)" 
              placeholder="Select Start Time"
              custom-class="pl-11 py-2.5 text-xs h-[42px]">
              <template #leading>
                <ClockIcon
                  class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
                />
              </template>
            </TimePicker>
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-xs font-medium text-slate-700">End Time</label>
          <div class="relative group">
            <TimePicker :model-value="packagingLogistics.end_time"
              @update:model-value="(val) => updateSection('end_time', val)" 
              placeholder="Select End Time"
              custom-class="pl-11 py-2.5 text-xs h-[42px]">
              <template #leading>
                <ClockIcon
                  class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
                />
              </template>
            </TimePicker>
          </div>
        </div>
      </div>
    </div>

    <!-- Packaging Items Selection -->
    <div class="relative z-[10] bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <PackageIcon class="h-4 w-4" />
        Packaging Components
      </h3>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
        <!-- Envelope -->
        <button
          type="button"
          @click="toggleItem('envelope')"
          class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all group"
          :class="isItemSelected('envelope') ? 'border-primary bg-primary/5 ring-2 ring-primary/5' : 'border-slate-200 bg-white hover:border-slate-300'"
        >
          <div class="p-2 rounded-lg transition-colors" :class="isItemSelected('envelope') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'">
            <MailIcon class="h-4 w-4" />
          </div>
          <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Envelope</span>
        </button>

        <!-- Sticker -->
        <button
          type="button"
          @click="toggleItem('sticker')"
          class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all group"
          :class="isItemSelected('sticker') ? 'border-primary bg-primary/5 ring-2 ring-primary/5' : 'border-slate-200 bg-white hover:border-slate-300'"
        >
          <div class="p-2 rounded-lg transition-colors" :class="isItemSelected('sticker') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'">
            <StickerIcon class="h-4 w-4" />
          </div>
          <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Sticker</span>
        </button>

        <!-- Crafting -->
        <button
          type="button"
          @click="toggleItem('crafting')"
          class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all group"
          :class="isItemSelected('crafting') ? 'border-primary bg-primary/5 ring-2 ring-primary/5' : 'border-slate-200 bg-white hover:border-slate-300'"
        >
          <div class="p-2 rounded-lg transition-colors" :class="isItemSelected('crafting') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'">
            <ScissorsIcon class="h-4 w-4" />
          </div>
          <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Crafting</span>
        </button>

        <!-- Tag -->
        <button
          type="button"
          @click="toggleItem('tag')"
          class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all group"
          :class="isItemSelected('tag') ? 'border-primary bg-primary/5 ring-2 ring-primary/5' : 'border-slate-200 bg-white hover:border-slate-300'"
        >
          <div class="p-2 rounded-lg transition-colors" :class="isItemSelected('tag') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'">
            <TagIcon class="h-4 w-4" />
          </div>
          <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Tag</span>
        </button>

        <!-- Ribbon -->
        <button
          type="button"
          @click="toggleItem('ribbon')"
          class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all group"
          :class="isItemSelected('ribbon') ? 'border-primary bg-primary/5 ring-2 ring-primary/5' : 'border-slate-200 bg-white hover:border-slate-300'"
        >
          <div class="p-2 rounded-lg transition-colors" :class="isItemSelected('ribbon') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'">
            <GiftIcon class="h-4 w-4" />
          </div>
          <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Ribbon</span>
        </button>

        <!-- Others -->
        <button
          type="button"
          @click="toggleItem('others')"
          class="flex flex-col items-center gap-2 p-3 rounded-xl border transition-all group"
          :class="isItemSelected('others') ? 'border-primary bg-primary/5 ring-2 ring-primary/5' : 'border-slate-200 bg-white hover:border-slate-300'"
        >
          <div class="p-2 rounded-lg transition-colors" :class="isItemSelected('others') ? 'bg-primary text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'">
            <PlusIcon class="h-4 w-4" />
          </div>
          <span class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">Others</span>
        </button>
      </div>

      <!-- Others Input Field -->
      <div v-if="isItemSelected('others')" class="mt-6 animate-in fade-in zoom-in-95 duration-300">
        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2 block">Specify Other Items (e.g. Seal, Nuts)</label>
        <div class="relative group">
          <TypeIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
          <input 
            :value="packagingLogistics.others_type" 
            @input="(e) => updateSection('others_type', e.target.value)"
            class="w-full px-11 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            placeholder="Type items here..." 
          />
        </div>
      </div>
    </div>

    <div class="space-y-2">
      <label class="text-xs font-medium text-slate-700"
        >Issues in Card</label
      >
      <textarea :value="packagingLogistics.card_issues" @input="(e) => updateSection('card_issues', e.target.value)"
        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all min-h-[100px]"
        placeholder="Describe any issues found..."></textarea>
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

    <!-- Fix Vue 3 Headless Fragment Tail Node Patch Bug -->
    <div key="tail-node-fix" class="hidden"></div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
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
  Gift as GiftIcon,
  Mail as MailIcon,
  Tag as TagIcon,
  Smile as StickerIcon,
  Plus as PlusIcon,
  Image as ImageIcon,
  Upload as UploadIcon,
  Trash2 as Trash2Icon,
  UserCheck as UserCheckIcon,
  ChevronDown as ChevronDownIcon,
} from "lucide-vue-next";
import SearchableDropdown from "../../../../ui/dropdowns/SearchableDropdown.vue";
import DatePicker from "../../../../ui/pickers/DatePicker.vue";
import TimePicker from "../../../../ui/pickers/TimePicker.vue";

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
  return props.order.packaging?.packaging_logistics || {};
});

const updateSection = (key, value) => {
  const newOrder = { 
    ...props.order,
    packaging: {
      ...(props.order.packaging || {}),
      packaging_logistics: {
        ...(props.order.packaging?.packaging_logistics || {})
      }
    }
  };

  newOrder.packaging.packaging_logistics[key] = value;
  emit("update:order", newOrder);
};

const isItemSelected = (item) => {
  const selectedItems = packagingLogistics.value.selected_items || [];
  return selectedItems.includes(item);
};

const toggleItem = (item) => {
  const currentItems = [...(packagingLogistics.value.selected_items || [])];
  const index = currentItems.indexOf(item);
  
  if (index === -1) {
    currentItems.push(item);
  } else {
    currentItems.splice(index, 1);
  }
  
  updateSection('selected_items', currentItems);
  
  // Clear others_type if others is deselected
  if (item === 'others' && index !== -1) {
    updateSection('others_type', '');
  }
};

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