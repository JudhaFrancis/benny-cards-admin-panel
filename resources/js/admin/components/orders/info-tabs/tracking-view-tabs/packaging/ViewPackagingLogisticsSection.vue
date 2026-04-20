<template>
  <div class="space-y-8">
    <!-- Status Overview -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <PackageIcon class="h-4 w-4" />
        Logistics Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <div
          class="flex-1 min-w-[140px] flex flex-col gap-2 p-4 rounded-xl border transition-all"
          :class="
            logistics.card_received
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div class="flex items-center gap-3">
            <div
              class="p-2 rounded-lg"
              :class="
                logistics.card_received
                  ? 'bg-primary text-white'
                  : 'bg-slate-200 text-slate-400'
              "
            >
              <DownloadIcon class="h-4 w-4" />
            </div>
            <span
              class="text-xs font-bold"
              :class="
                logistics.card_received ? 'text-slate-900' : 'text-slate-400'
              "
              >Card Received</span
            >
          </div>
          <div v-if="logistics.card_received && logistics.card_received_date" class="mt-1 pl-11">
             <span class="text-[10px] font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md flex items-center gap-1.5 w-fit border border-emerald-100">
               <CalendarIcon class="h-3 w-3" />
               {{ formatDate(logistics.card_received_date) }}
             </span>
          </div>
        </div>

        <div
          class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            logistics.crafting_done
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              logistics.crafting_done
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <ScissorsIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              logistics.crafting_done ? 'text-slate-900' : 'text-slate-400'
            "
            >Crafting Done</span
          >
        </div>
      </div>



    </div>

    <!-- Workflow & Assignment Section -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100 space-y-6">
      <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
        <UserIcon class="h-4 w-4" /> Assignment & Completion
      </h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
            >Assigned By</label
          >
          <div
            class="flex flex-wrap gap-2 p-3 rounded-xl bg-white border border-slate-100 min-h-[46px]"
          >
            <template v-if="logistics.assigned_by_multiple?.length">
              <span 
                v-for="staff in logistics.assigned_by_multiple" 
                :key="staff"
                class="bg-primary/5 text-primary text-[10px] font-bold px-2.5 py-1 rounded-lg border border-primary/10"
              >
                {{ staff }}
              </span>
            </template>
            <span v-else class="text-sm font-medium text-slate-400">Not assigned</span>
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
            >Crafted By</label
          >
          <div
            class="flex flex-wrap gap-2 p-3 rounded-xl bg-white border border-slate-100 min-h-[46px]"
          >
            <template v-if="logistics.crafted_by_multiple?.length">
              <span 
                v-for="staff in logistics.crafted_by_multiple" 
                :key="staff"
                class="bg-primary/5 text-primary text-[10px] font-bold px-2.5 py-1 rounded-lg border border-primary/10"
              >
                {{ staff }}
              </span>
            </template>
            <span v-else class="text-sm font-medium text-slate-400">Not assigned</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Logistics Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Names on Cards</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <TypeIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            logistics.names || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Date</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <CalendarIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            formatDate(logistics.date) || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Qty of Cards</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <HashIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900 tracking-wider">{{
            logistics.qty_cards || "0"
          }}</span>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <!-- Selected Packaging Components -->
      <div v-if="logistics.selected_items?.length" class="space-y-3">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Selected Components</label
        >
        <div class="flex flex-wrap gap-2">
          <div v-if="logistics.selected_items.includes('envelope')" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/5 border border-primary/10 text-primary text-[10px] font-bold">
            <MailIcon class="h-3 w-3" /> ENVELOPE
          </div>
          <div v-if="logistics.selected_items.includes('sticker')" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/5 border border-primary/10 text-primary text-[10px] font-bold">
            <StickerIcon class="h-3 w-3" /> STICKER
          </div>
          <div v-if="logistics.selected_items.includes('crafting')" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/5 border border-primary/10 text-primary text-[10px] font-bold">
            <ScissorsIcon class="h-3 w-3" /> CRAFTING
          </div>
          <div v-if="logistics.selected_items.includes('tag')" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/5 border border-primary/10 text-primary text-[10px] font-bold">
            <TagIcon class="h-3 w-3" /> TAG
          </div>
          <div v-if="logistics.selected_items.includes('ribbon')" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/5 border border-primary/10 text-primary text-[10px] font-bold">
            <GiftIcon class="h-3 w-3" /> RIBBON
          </div>
          <div v-if="logistics.selected_items.includes('others')" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/5 border border-primary/10 text-primary text-[10px] font-bold uppercase transition-all">
            <PlusIcon class="h-3 w-3" /> {{ logistics.others_type || 'OTHERS' }}
          </div>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Issues in Card</label
        >
        <div
          class="p-4 rounded-xl bg-rose-50 border border-rose-100 text-sm text-rose-700 italic min-h-[80px]"
        >
          {{ logistics.card_issues || "No issues found." }}
        </div>
      </div>

    </div>

    <!-- Audit Information -->
    <div
      v-if="order.packaging && !hideAudit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          order.packaging.modified_by?.name || order.packaging.added_by?.name || "System"
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(order.packaging.updated_at || order.packaging.created_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  User as UserIcon,
  Package as PackageIcon,
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
  UserCheck as UserCheckIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
  hideAudit: { type: Boolean, default: false },
});

const logistics = computed(
  () => props.order.packaging?.packaging_logistics || {},
);

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

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("blob:") || path.startsWith("data:") || path.startsWith("http")) return path;
  return `/${path}`;
};
</script>