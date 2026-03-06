<template>
  <div class="space-y-8">
    <!-- Shop Selection -->
    <div class="space-y-4">
      <label
        class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2"
      >
        <StoreIcon class="h-4 w-4 text-primary" />
        Selected Shop Location
      </label>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="shop in shopOptions"
          :key="shop.id"
          class="relative flex items-center gap-3 p-4 rounded-2xl border transition-all"
          :class="
            deliveryLocation.shops === shop.id
              ? 'bg-primary/5 border-primary shadow-sm'
              : 'bg-slate-50 border-slate-100 opacity-60'
          "
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
            :class="
              deliveryLocation.shops === shop.id
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400'
            "
          >
            <component :is="shop.icon" class="h-5 w-5" />
          </div>
          <span
            class="block text-[10px] font-black uppercase tracking-wider"
            :class="
              deliveryLocation.shops === shop.id
                ? 'text-primary'
                : 'text-slate-700'
            "
            >{{ shop.label }}</span
          >
          <div
            v-if="deliveryLocation.shops === shop.id"
            class="ml-auto w-5 h-5 rounded-full bg-primary flex items-center justify-center shadow-sm"
          >
            <CheckIcon class="h-3 w-3 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Place Name -->
    <div class="space-y-4 pt-4 border-t border-slate-100">
      <label
        class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2"
      >
        <NavigationIcon class="h-4 w-4 text-primary" />
        Place Name
      </label>
      <div
        class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100"
      >
        <MapPinIcon class="h-5 w-5 text-slate-400 mt-0.5" />
        <span class="text-sm font-medium text-slate-900 leading-relaxed">{{
          deliveryLocation.place_name || "N/A"
        }}</span>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="deliveryLocation._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(deliveryLocation._audit.updated_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Store as StoreIcon,
  MapPin as MapPinIcon,
  ShoppingBag as ShoppingBagIcon,
  Building2 as Building2Icon,
  Navigation as NavigationIcon,
  Check as CheckIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const deliveryLocation = computed(
  () => props.order.tracking?.delivery_location || {},
);

const shopOptions = [
  { id: "NGL Shop", label: "NGL Shop", icon: StoreIcon },
  { id: "Marthandam Shop", label: "Marthandam Shop", icon: MapPinIcon },
  { id: "TVL Shop", label: "TVL Shop", icon: ShoppingBagIcon },
  { id: "Chennai Shop", label: "Chennai Shop", icon: Building2Icon },
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

