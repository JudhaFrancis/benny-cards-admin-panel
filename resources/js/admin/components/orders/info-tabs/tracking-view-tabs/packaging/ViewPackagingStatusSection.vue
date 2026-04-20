<template>
  <div class="space-y-8">
    <!-- Status Overview -->
    <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
      <h3
        class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2"
      >
        <PackageIcon class="h-4 w-4" />
        Packaging Status
      </h3>
      <div class="flex flex-wrap gap-4">
        <div
          class="flex-1 min-w-[160px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            packagingStatus.packed_with_gift
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              packagingStatus.packed_with_gift
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <GiftIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              packagingStatus.packed_with_gift
                ? 'text-slate-900'
                : 'text-slate-400'
            "
            >Packed with Gift</span
          >
        </div>

        <div
          class="flex-1 min-w-[160px] flex items-center gap-3 p-4 rounded-xl border transition-all"
          :class="
            packagingStatus.packed_without_gift
              ? 'border-primary bg-white shadow-sm'
              : 'border-slate-100 bg-slate-50 opacity-50'
          "
        >
          <div
            class="p-2 rounded-lg"
            :class="
              packagingStatus.packed_without_gift
                ? 'bg-primary text-white'
                : 'bg-slate-200 text-slate-400'
            "
          >
            <PackageIcon class="h-4 w-4" />
          </div>
          <span
            class="text-xs font-bold"
            :class="
              packagingStatus.packed_without_gift
                ? 'text-slate-900'
                : 'text-slate-400'
            "
            >Packed Without Gift</span
          >
        </div>
      </div>
    </div>

    <!-- Assignment -->
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Packed By</label>
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
          <UserIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{ packagingStatus.packed_by || "N/A" }}</span>
        </div>
      </div>

      <!-- Gift Option Section (Moved here from Logistics) -->
      <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-100 mt-6 relative z-[15] mb-8">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
          <GiftIcon class="h-4 w-4" /> Gift Option
        </h3>
        <div class="flex flex-wrap gap-4">
          <!-- With Gift Option -->
          <div class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border transition-all"
            :class="packagingLogistics.gift_type === 'with_gift' ? 'border-primary bg-white shadow-sm' : 'border-slate-100 bg-slate-50 opacity-50'">
            <div class="p-2 rounded-lg"
              :class="packagingLogistics.gift_type === 'with_gift' ? 'bg-primary text-white' : 'bg-slate-200 text-slate-400'">
              <GiftIcon class="h-4 w-4" />
            </div>
            <span class="text-xs font-bold"
              :class="packagingLogistics.gift_type === 'with_gift' ? 'text-slate-900' : 'text-slate-400'">With Gift</span>
          </div>

          <!-- Without Gift Option -->
          <div class="flex-1 min-w-[140px] flex items-center gap-3 p-4 rounded-xl border transition-all"
            :class="packagingLogistics.gift_type === 'without_gift' ? 'border-primary bg-white shadow-sm' : 'border-slate-100 bg-slate-50 opacity-50'">
            <div class="p-2 rounded-lg"
              :class="packagingLogistics.gift_type === 'without_gift' ? 'bg-primary text-white' : 'bg-slate-200 text-slate-400'">
              <PackageIcon class="h-4 w-4" />
            </div>
            <span class="text-xs font-bold"
              :class="packagingLogistics.gift_type === 'without_gift' ? 'text-slate-900' : 'text-slate-400'">Without Gift</span>
          </div>
        </div>
      </div>

    <!-- Sticker Reference Image -->
    <div v-if="order.designing?.sticker_image" class="space-y-3 pt-6 border-t border-slate-100">
      <label class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
        <ImageIcon class="h-3.5 w-3.5" /> Sticker Design Reference
      </label>
      <div class="relative w-40 h-40 rounded-2xl border border-slate-200 overflow-hidden bg-white shadow-sm group">
        <img :src="getImageSource(order.designing.sticker_image)" class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
           <a :href="getImageSource(order.designing.sticker_image)" target="_blank" class="p-2 bg-white/90 text-slate-900 rounded-xl shadow-lg hover:scale-110 transition-transform active:scale-95">
              <ImageIcon class="h-5 w-5" />
           </a>
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
  Gift as GiftIcon,
  Clock as ClockIcon,
  Image as ImageIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
  hideAudit: { type: Boolean, default: false },
});

const packagingStatus = computed(
  () => props.order.packaging?.packaging_status || {},
);

const packagingLogistics = computed(
  () => props.order.packaging?.packaging_logistics || {},
);

const getImageSource = (path) => {
  if (!path) return "/images/placeholder.webp";
  if (path.startsWith("blob:") || path.startsWith("data:") || path.startsWith("http")) return path;
  return `/${path}`;
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

