<template>
  <div class="space-y-8">
    <!-- Dispatch Mode Selection -->
    <div class="space-y-4">
      <label
        class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2"
      >
        <TruckIcon class="h-4 w-4 text-primary" />
        Mode of Dispatch
      </label>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="mode in modeOptions"
          :key="mode.id"
          class="relative flex items-center gap-3 p-4 rounded-2xl border transition-all"
          :class="
            dispatchMode.modes === mode.id
              ? 'bg-primary/5 border-primary shadow-sm'
              : 'bg-slate-50 border-slate-100 opacity-60'
          "
        >
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
            :class="
              dispatchMode.modes === mode.id
                ? 'bg-primary text-white'
                : 'bg-slate-100 text-slate-400'
            "
          >
            <component :is="mode.icon" class="h-5 w-5" />
          </div>
          <span
            class="block text-[10px] font-black uppercase tracking-wider"
            :class="
              dispatchMode.modes === mode.id ? 'text-primary' : 'text-slate-700'
            "
            >{{ mode.label }}</span
          >
          <div
            v-if="dispatchMode.modes === mode.id"
            class="ml-auto w-5 h-5 rounded-full bg-primary flex items-center justify-center shadow-sm"
          >
            <CheckIcon class="h-3 w-3 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Details Grid -->
    <div
      class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-4 border-t border-slate-100"
    >
      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Dispatch Date</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <CalendarIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            formatDate(dispatchMode.date) || "N/A"
          }}</span>
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Dispatch Expense</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <BanknoteIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-bold text-slate-900"
            >₹{{ dispatchMode.expense || "0.00" }}</span
          >
        </div>
      </div>

      <div class="space-y-2">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >Signature & Name</label
        >
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100"
        >
          <UserIcon class="h-4 w-4 text-slate-400" />
          <span class="text-sm font-medium text-slate-900">{{
            dispatchMode.signature_name || "N/A"
          }}</span>
        </div>
      </div>
    </div>

    <!-- Audit Information -->
    <div
      v-if="dispatchMode._audit"
      class="pt-6 border-t border-slate-100 flex items-center justify-end text-xs text-slate-400 gap-2"
    >
      <ClockIcon class="h-3.5 w-3.5" />
      <span>
        Last updated by
        <strong class="text-slate-600">{{
          dispatchMode._audit.updated_by
        }}</strong>
        on
        <span class="font-medium bg-slate-100 px-2 py-0.5 rounded-full">{{
          formatAuditDate(dispatchMode._audit.updated_at)
        }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Truck as TruckIcon,
  Store as StoreIcon,
  Bus as BusIcon,
  Package as PackageIcon,
  Calendar as CalendarIcon,
  Banknote as BanknoteIcon,
  User as UserIcon,
  Check as CheckIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";

const props = defineProps({
  order: { type: Object, required: true },
});

const dispatchMode = computed(() => props.order.tracking?.dispatch_mode || {});

const modeOptions = [
  { id: "Shop Pickup", label: "Shop Pickup", icon: StoreIcon },
  { id: "Bus", label: "Bus", icon: BusIcon },
  { id: "Transport", label: "Transport", icon: TruckIcon },
  { id: "Courier", label: "Courier", icon: PackageIcon },
];

const formatDate = (dateString) => {
  if (!dateString) return "";
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};

const formatAuditDate = (dateString) => {
  return new Date(dateString).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};
</script>
